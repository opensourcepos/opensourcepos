<?php

declare(strict_types=1);

namespace App\Libraries\Nano;

use InvalidArgumentException;

/**
 * The Nano (XNO) payment adapter for the register.
 *
 * It is off until the operator turns it on, and nothing in the rest of OSPOS
 * changes while it is off. When a cashier takes a Nano payment, the register
 * first asks this adapter for a quote: the exact raw amount owed (derived from
 * the sale total and the operator's XNO price, never a restated constant) and
 * the representative account the payer must set on their send block so the
 * block belongs to this one sale (binding option C of the xno-binding v1
 * standard). The cashier then enters the resulting block hash as the payment's
 * reference code, and this adapter reads that block from a Nano node the
 * operator names - no key, no seed, read only - and credits the sale only if
 * the block is a confirmed send of the exact amount, to the configured account,
 * carrying this sale's commitment. A durable, fsynced claims ledger makes one
 * block credit one sale once, even across a restart.
 *
 * The node read and the clock are injectable, so every decision here is unit
 * tested without a database, a container or the network.
 */
final class PaymentGateway
{
    private readonly string $account;
    private readonly string $nodeUrl;
    private readonly string $xnoPrice;
    private readonly int $replayWindowSeconds;
    private readonly ClaimsFile $claims;

    /** @var callable(string): array<string, mixed> */
    private $fetchBlock;

    /** @var callable(): int */
    private $clock;

    /**
     * @param array<string, mixed>                      $settings   the OSPOS settings array
     * @param (callable(string): array<string, mixed>)? $fetchBlock given a 64-hex block hash, returns the node's block_info reply
     * @param (callable(): int)?                         $clock      current unix time (injected in tests)
     */
    public function __construct(array $settings, ?callable $fetchBlock = null, ?callable $clock = null)
    {
        $account = trim((string) ($settings['nano_account'] ?? ''));
        if (!Address::isValid($account)) {
            throw new InvalidArgumentException('The configured Nano account is not a valid nano_ address.');
        }

        $xnoPrice = trim((string) ($settings['nano_xno_price'] ?? ''));
        if (preg_match('/^\d+(?:\.\d+)?$/', $xnoPrice) !== 1 || (float) $xnoPrice <= 0) {
            throw new InvalidArgumentException('The Nano price (XNO per unit of sale currency) must be a positive number.');
        }

        $nodeUrl = trim((string) ($settings['nano_node_url'] ?? ''));
        if ($fetchBlock === null && !preg_match('#^https?://#', $nodeUrl)) {
            throw new InvalidArgumentException('A Nano node URL (http or https) must be configured.');
        }

        $window = (int) ($settings['nano_replay_window_seconds'] ?? 86400);

        $this->account = $account;
        $this->nodeUrl = $nodeUrl;
        $this->xnoPrice = $xnoPrice;
        $this->replayWindowSeconds = $window > 0 ? $window : 86400;
        $claimsPath = trim((string) ($settings['nano_claims_path'] ?? ''));
        if ($claimsPath === '') {
            $base = defined('WRITEPATH') ? WRITEPATH : sys_get_temp_dir() . '/';
            $claimsPath = $base . 'nano/claims.log';
        }
        $this->claims = new ClaimsFile($claimsPath);
        $this->fetchBlock = $fetchBlock ?? fn (string $hash): array => $this->fetchFromNode($hash);
        $this->clock = $clock ?? static fn (): int => time();
    }

    /**
     * True when the operator has turned Nano on and set the three required
     * fields. The register only offers Nano, and only runs this adapter, when
     * this returns true; everything else is unchanged.
     *
     * @param array<string, mixed> $settings
     */
    public static function isEnabled(array $settings): bool
    {
        return in_array((string) ($settings['nano_enabled'] ?? '0'), ['1', 'true'], true)
            && Address::isValid(trim((string) ($settings['nano_account'] ?? '')))
            && trim((string) ($settings['nano_node_url'] ?? '')) !== ''
            && trim((string) ($settings['nano_xno_price'] ?? '')) !== '';
    }

    /**
     * What the payer must send for a sale: the exact raw amount and the
     * representative account that binds the block to this sale. The register
     * stores $orderRef and the returned price_raw with the in-progress sale and
     * hands them back to verify(), so an amount change before payment produces
     * a different quote rather than crediting the wrong block.
     *
     * @return array{order_ref: string, account: string, amount_xno: string, amount_raw: string, representative: string}
     */
    public function quote(string $orderRef, string $amountDue): array
    {
        $amountXno = Amount::multiplyDecimals($this->normalizeAmount($amountDue), $this->xnoPrice);
        $priceRaw = Amount::xnoToRaw($amountXno);

        if ($priceRaw === '0') {
            throw new InvalidArgumentException('A Nano payment must be for more than zero.');
        }

        return [
            'order_ref'      => $orderRef,
            'account'        => $this->account,
            'amount_xno'     => $amountXno,
            'amount_raw'     => $priceRaw,
            'representative' => Binding::representativeAccount($orderRef, $this->account, $priceRaw),
        ];
    }

    /**
     * Read the block the cashier entered and decide whether it settles this
     * sale. On success the block is recorded in the durable claims ledger so it
     * can never be replayed. $priceRaw is the exact raw from the quote stored
     * with the sale, so the amount the payer was quoted is the amount checked.
     *
     * @return array{ok: bool, error: string, amount: ?string, payer: ?string}
     */
    public function verify(string $blockHash, string $orderRef, string $priceRaw): array
    {
        $hash = strtoupper(trim($blockHash));
        if (preg_match('/^[0-9A-F]{64}$/', $hash) !== 1) {
            return ['ok' => false, 'error' => 'A Nano block hash is 64 hex characters.', 'amount' => null, 'payer' => null];
        }

        $representative = Binding::representativeAccount($orderRef, $this->account, Amount::normalizeRaw($priceRaw));

        $blockInfo = ($this->fetchBlock)($hash);
        $result = Verifier::verify($blockInfo, $this->account, $priceRaw, $representative, $this->replayWindowSeconds, ($this->clock)());

        if (!$result['ok']) {
            return $result;
        }

        // Durable, restart-proof replay refusal: one block pays one sale once.
        // claim() throws for a block already spent on another sale, or a sale
        // already paid by another block; false means the same block was already
        // recorded for this same sale (an idempotent retry), which still counts
        // as settled. Any other value is a fresh claim.
        try {
            $this->claims->claim($hash, $orderRef);
        } catch (NanoException $e) {
            return ['ok' => false, 'error' => $e->getMessage(), 'amount' => null, 'payer' => null];
        }

        return $result;
    }

    private function normalizeAmount(string $amountDue): string
    {
        $amount = trim($amountDue);
        if (preg_match('/^\d+(?:\.\d+)?$/', $amount) !== 1) {
            throw new InvalidArgumentException('The sale amount due is not a non-negative decimal.');
        }

        return $amount;
    }

    /**
     * Read one block from the operator's node with a plain block_info RPC. No
     * key and no seed are sent; the node only returns public ledger data.
     *
     * @return array<string, mixed>
     */
    private function fetchFromNode(string $hash): array
    {
        $payload = json_encode(['action' => 'block_info', 'json_block' => 'true', 'hash' => $hash], JSON_THROW_ON_ERROR);

        $handle = curl_init($this->nodeUrl);
        if ($handle === false) {
            throw new NanoException('Could not reach the Nano node.');
        }

        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $body = curl_exec($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $error = curl_error($handle);
        curl_close($handle);

        if (!is_string($body) || $status < 200 || $status >= 300) {
            throw new NanoException('The Nano node did not answer the block query.' . ($error !== '' ? ' ' . $error : ''));
        }

        $decoded = json_decode($body, true);
        if (!is_array($decoded) || isset($decoded['error'])) {
            throw new NanoException('The Nano node could not find this block.');
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }
}
