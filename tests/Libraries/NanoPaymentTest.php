<?php

declare(strict_types=1);

namespace Tests\Libraries;

use App\Libraries\Nano\Address;
use App\Libraries\Nano\Amount;
use App\Libraries\Nano\Binding;
use App\Libraries\Nano\ClaimsFile;
use App\Libraries\Nano\NanoException;
use App\Libraries\Nano\PaymentGateway;
use App\Libraries\Nano\Verifier;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the dependency-free Nano (XNO) payment pieces: the address
 * codec (BLAKE2b checksum), raw-amount derivation, the xno-binding v1 option C
 * commitment, the block verifier, the restart-proof claims ledger, and the
 * register adapter with its node read injected. None of these touch the
 * database, a container or the network, so they run on their own.
 */
final class NanoPaymentTest extends TestCase
{
    // The official xno-binding v1 option C vector (docs/BINDING-STANDARD.md).
    private const BURN = 'nano_1111111111111111111111111111111111111111111111111111hifc8npp';
    private const PRICE_RAW = '50000000000000000000000000000'; // 0.05 XNO
    private const ORDER = 'order-0001';
    private const REP = 'nano_33q9dassq66es6mcay83n5kypqgpzjikr9bkc9ope9xch1borfphgehj4ozj';

    /** @var list<string> */
    private array $tempFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $path) {
            @unlink($path);
        }
        $this->tempFiles = [];
    }

    private function tempPath(): string
    {
        $path = sys_get_temp_dir() . '/nano-test-' . bin2hex(random_bytes(8)) . '.log';
        $this->tempFiles[] = $path;

        return $path;
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
    private function confirmedSend(array $overrides = []): array
    {
        $base = [
            'block_account'   => 'nano_3t6k35gi95xu6tergt6p69ck76ogmitsa8mnijtpxm9fkcm736xtoncuohr3',
            'amount'          => self::PRICE_RAW,
            'confirmed'       => 'true',
            'subtype'         => 'send',
            'local_timestamp' => '1763200000',
            'contents'        => [
                'link_as_account' => self::BURN,
                'representative'   => self::REP,
            ],
        ];
        $contents = array_merge($base['contents'], $overrides['contents'] ?? []);
        unset($overrides['contents']);

        return array_merge($base, $overrides, ['contents' => $contents]);
    }

    /**
     * @param array<string, string> $overrides
     *
     * @return array<string, mixed>
     */
    private function settings(array $overrides = []): array
    {
        return array_merge([
            'nano_enabled'               => '1',
            'nano_account'               => self::BURN,
            'nano_node_url'              => 'http://127.0.0.1:7076',
            'nano_xno_price'             => '0.05',
            'nano_replay_window_seconds' => '86400',
            'nano_claims_path'           => $this->tempPath(),
        ], $overrides);
    }

    public function testValidAddressRoundTripsThroughItsPublicKey(): void
    {
        $this->assertTrue(Address::isValid(self::BURN));
        $this->assertSame(str_repeat('0', 64), Address::publicKey(self::BURN));
        $this->assertSame(self::BURN, Address::fromPublicKey(str_repeat('0', 64)));
    }

    public function testOneCharacterChecksumErrorIsRejected(): void
    {
        $this->assertFalse(Address::isValid('nano_1111111111111111111111111111111111111111111111111111hifc8npq'));
    }

    public function testXrbAndNanoFormsOfOneAccountMatch(): void
    {
        $this->assertTrue(Address::matches(self::BURN, 'xrb_1111111111111111111111111111111111111111111111111111hifc8npp'));
    }

    public function testRawIsDerivedFromXnoNeverFinerThanOneRaw(): void
    {
        $this->assertSame(self::PRICE_RAW, Amount::xnoToRaw('0.05'));
        $this->assertSame('1000000000000000000000000000000', Amount::xnoToRaw('1'));

        $this->expectException(InvalidArgumentException::class);
        Amount::xnoToRaw('0.' . str_repeat('0', 30) . '1');
    }

    public function testPriceIsDerivedFromTheSaleTotalWithNoFloatRounding(): void
    {
        // 12.50 at 3.2 XNO per unit = 40 XNO, exact.
        $this->assertSame('40', Amount::multiplyDecimals('12.50', '3.2'));
        $this->assertSame('40000000000000000000000000000000', Amount::xnoToRaw('40'));
    }

    public function testRawAmountsCompareAsIntegers(): void
    {
        $this->assertTrue(Amount::equals('050', '50'));
        $this->assertTrue(Amount::atLeast(self::PRICE_RAW, self::PRICE_RAW));
        $this->assertFalse(Amount::atLeast('49999999999999999999999999999', self::PRICE_RAW));
        $this->assertFalse(Amount::isRaw('1.5'));
    }

    public function testOptionCCommitmentReproducesThePublishedVector(): void
    {
        $this->assertSame(
            '86e75a339b908cc926a478c1a0e5eb5dd6fc612c1d3251eb661faa78135c36cf',
            Binding::commitmentHex(self::ORDER, self::BURN, self::PRICE_RAW)
        );
        $this->assertSame(self::REP, Binding::representativeAccount(self::ORDER, self::BURN, self::PRICE_RAW));
    }

    public function testADifferentOrderGivesADifferentRepresentative(): void
    {
        $this->assertNotSame(
            Binding::representativeAccount(self::ORDER, self::BURN, self::PRICE_RAW),
            Binding::representativeAccount('order-0002', self::BURN, self::PRICE_RAW)
        );
    }

    public function testVerifierAcceptsAConfirmedBoundExactSend(): void
    {
        $result = Verifier::verify($this->confirmedSend(), self::BURN, self::PRICE_RAW, self::REP, 3600, 1763200100);
        $this->assertTrue($result['ok']);
        $this->assertSame(self::PRICE_RAW, $result['amount']);
        $this->assertSame('nano_3t6k35gi95xu6tergt6p69ck76ogmitsa8mnijtpxm9fkcm736xtoncuohr3', $result['payer']);
    }

    public function testVerifierRefusesAnUnconfirmedSend(): void
    {
        $result = Verifier::verify($this->confirmedSend(['confirmed' => 'false']), self::BURN, self::PRICE_RAW, self::REP, 3600, 1763200100);
        $this->assertFalse($result['ok']);
    }

    public function testVerifierRefusesASendToAnotherAccount(): void
    {
        $other = ['contents' => ['link_as_account' => 'nano_3t6k35gi95xu6tergt6p69ck76ogmitsa8mnijtpxm9fkcm736xtoncuohr3']];
        $result = Verifier::verify($this->confirmedSend($other), self::BURN, self::PRICE_RAW, self::REP, 3600, 1763200100);
        $this->assertFalse($result['ok']);
    }

    public function testVerifierRefusesTheWrongAmountShortOrOver(): void
    {
        $short = Verifier::verify($this->confirmedSend(['amount' => '49999999999999999999999999999']), self::BURN, self::PRICE_RAW, self::REP, 3600, 1763200100);
        $over  = Verifier::verify($this->confirmedSend(['amount' => '50000000000000000000000000001']), self::BURN, self::PRICE_RAW, self::REP, 3600, 1763200100);
        $this->assertFalse($short['ok']);
        $this->assertFalse($over['ok']);
    }

    public function testVerifierRefusesASendNotBoundToThisSale(): void
    {
        $wrongRep = ['contents' => ['representative' => self::BURN]];
        $result = Verifier::verify($this->confirmedSend($wrongRep), self::BURN, self::PRICE_RAW, self::REP, 3600, 1763200100);
        $this->assertFalse($result['ok']);
    }

    public function testVerifierRefusesASendOlderThanTheReplayWindow(): void
    {
        $result = Verifier::verify($this->confirmedSend(['local_timestamp' => '1763200000']), self::BURN, self::PRICE_RAW, self::REP, 3600, 1763299999);
        $this->assertFalse($result['ok']);
    }

    public function testAClaimCreditsOneBlockToOneSaleOnceIdempotently(): void
    {
        $ledger = new ClaimsFile($this->tempPath());
        $hash = str_repeat('A', 64);
        $this->assertTrue($ledger->claim($hash, 'sale-1'));
        $this->assertFalse($ledger->claim($hash, 'sale-1'));
    }

    public function testAClaimedBlockCannotBeReplayedForAnotherSale(): void
    {
        $ledger = new ClaimsFile($this->tempPath());
        $hash = str_repeat('B', 64);
        $ledger->claim($hash, 'sale-1');
        $this->expectException(NanoException::class);
        $ledger->claim($hash, 'sale-2');
    }

    public function testASaleCannotBePaidTwiceByTwoDifferentBlocks(): void
    {
        $ledger = new ClaimsFile($this->tempPath());
        $ledger->claim(str_repeat('C', 64), 'sale-1');
        $this->expectException(NanoException::class);
        $ledger->claim(str_repeat('D', 64), 'sale-1');
    }

    public function testAClaimSurvivesARestart(): void
    {
        $path = $this->tempPath();
        $hash = str_repeat('E', 64);
        (new ClaimsFile($path))->claim($hash, 'sale-1');

        // A new process constructs a new ledger; it reloads from disk.
        $reloaded = new ClaimsFile($path);
        $this->assertTrue($reloaded->isClaimed($hash));
        $this->assertFalse($reloaded->claim($hash, 'sale-1'));
    }

    public function testGatewayIsOffUntilConfigured(): void
    {
        $this->assertFalse(PaymentGateway::isEnabled([]));
        $this->assertFalse(PaymentGateway::isEnabled(['nano_enabled' => '0'] + $this->settings()));
        $this->assertFalse(PaymentGateway::isEnabled(['nano_enabled' => '1', 'nano_account' => self::BURN, 'nano_node_url' => '', 'nano_xno_price' => '0.05']));
        $this->assertTrue(PaymentGateway::isEnabled($this->settings()));
    }

    public function testGatewayRejectsABadAccountOrPrice(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PaymentGateway($this->settings(['nano_account' => 'nano_notanaddress']));
    }

    public function testGatewayRejectsANonPositivePrice(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PaymentGateway($this->settings(['nano_xno_price' => '0']));
    }

    public function testGatewayQuoteDerivesTheExactRawAndBoundRepresentative(): void
    {
        $gateway = new PaymentGateway($this->settings(), fn (string $h): array => $this->confirmedSend());
        // A sale total of 1.00 at 0.05 XNO per unit -> 0.05 XNO.
        $quote = $gateway->quote(self::ORDER, '1');
        $this->assertSame(self::PRICE_RAW, $quote['amount_raw']);
        $this->assertSame('0.05', $quote['amount_xno']);
        $this->assertSame(self::REP, $quote['representative']);
        $this->assertSame(self::BURN, $quote['account']);
    }

    public function testGatewayVerifyAcceptsThenRefusesASecondBlockForASettledSale(): void
    {
        $gateway = new PaymentGateway(
            $this->settings(),
            fn (string $h): array => $this->confirmedSend(),
            static fn (): int => 1763200100
        );

        $first = $gateway->verify(str_repeat('1', 64), self::ORDER, self::PRICE_RAW);
        $this->assertTrue($first['ok']);
        $this->assertSame(self::PRICE_RAW, $first['amount']);

        // A re-submission of the very same block for the same sale is an
        // idempotent retry: still ok, and the durable ledger records it once.
        $retry = $gateway->verify(str_repeat('1', 64), self::ORDER, self::PRICE_RAW);
        $this->assertTrue($retry['ok']);

        // A different block presented for the already-settled sale is refused by
        // the durable, restart-proof claims ledger - one sale, one block, ever.
        $second = $gateway->verify(str_repeat('3', 64), self::ORDER, self::PRICE_RAW);
        $this->assertFalse($second['ok']);
    }

    public function testGatewayVerifyRefusesABlockAlreadySpentOnAnotherSale(): void
    {
        $claimsPath = $this->tempPath();
        $hash = str_repeat('4', 64);

        // One block settles sale A. A second sale that (improbably) presents the
        // same block with a matching commitment is refused across restart: a
        // fresh gateway reading the same ledger file still knows the block.
        $gatewayA = new PaymentGateway(
            $this->settings(['nano_claims_path' => $claimsPath]),
            fn (string $h): array => $this->confirmedSend(),
            static fn (): int => 1763200100
        );
        $this->assertTrue($gatewayA->verify($hash, self::ORDER, self::PRICE_RAW)['ok']);

        $gatewayB = new PaymentGateway(
            $this->settings(['nano_claims_path' => $claimsPath]),
            fn (string $h): array => $this->confirmedSend(['contents' => ['representative' => Binding::representativeAccount('order-9999', self::BURN, self::PRICE_RAW)]]),
            static fn (): int => 1763200100
        );
        $this->assertFalse($gatewayB->verify($hash, 'order-9999', self::PRICE_RAW)['ok']);
    }

    public function testGatewayVerifyRejectsAMalformedBlockHash(): void
    {
        $gateway = new PaymentGateway($this->settings(), fn (string $h): array => $this->confirmedSend());
        $result = $gateway->verify('not-a-hash', self::ORDER, self::PRICE_RAW);
        $this->assertFalse($result['ok']);
    }

    public function testGatewayVerifyRejectsAnAmountDrift(): void
    {
        // The stored quote (price_raw) no longer matches the on-chain amount:
        // the representative derived from the new price_raw will not match.
        $hash = str_repeat('2', 64);
        $gateway = new PaymentGateway(
            $this->settings(),
            fn (string $h): array => $this->confirmedSend(),
            static fn (): int => 1763200100
        );
        $result = $gateway->verify($hash, self::ORDER, '60000000000000000000000000000');
        $this->assertFalse($result['ok']);
    }
}
