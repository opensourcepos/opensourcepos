<?php

declare(strict_types=1);

namespace App\Libraries\Nano;

/**
 * Binds a Nano send block to one sale with option C of the xno-binding v1
 * standard (docs/BINDING-STANDARD.md): the order commitment goes in the send
 * block's representative field.
 *
 *   commitment = SHA256("xno-binding/v1/rep|" + orderId + "|" + payTo + "|" + priceRaw)
 *
 * encoded as a `nano_` account. A Nano send has no memo, so a bare block hash
 * proves only that an amount reached an account; the commitment makes the block
 * belong to exactly one issued sale at exactly one price, set by the payer.
 * Pure: no dependency and no network, so it unit-tests on its own.
 */
final class Binding
{
    private const DOMAIN = 'xno-binding/v1/rep|';

    public static function commitmentHex(string $orderId, string $payTo, string $priceRaw): string
    {
        $priceRaw = Amount::normalizeRaw($priceRaw);

        return hash('sha256', self::DOMAIN . $orderId . '|' . $payTo . '|' . $priceRaw);
    }

    /**
     * The representative account a payer must set on the send block for this
     * sale. The adapter compares it against the block's representative.
     */
    public static function representativeAccount(string $orderId, string $payTo, string $priceRaw): string
    {
        return Address::fromPublicKey(self::commitmentHex($orderId, $payTo, $priceRaw));
    }
}
