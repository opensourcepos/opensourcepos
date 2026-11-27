<?php

namespace App\Libraries;

use InvalidArgumentException;

/**
 * Allocates a monetary discount in minor currency units.
 */
final class BillDiscountAllocation
{
    /**
     * @param array<int|string, int> $lineAmounts Non-negative amounts in minor units.
     *
     * @return array<int|string, int> Allocated amounts in minor units.
     */
    public static function allocatePercentage(array $lineAmounts, int $rate): array
    {
        if ($rate < 0 || $rate > 1000000) {
            throw new InvalidArgumentException('Invalid percentage.');
        }
        $total = 0;

        foreach ($lineAmounts as $amount) {
            if ($amount < 0 || $total > PHP_INT_MAX - $amount) {
                throw new InvalidArgumentException('Invalid line amount.');
            }
            $total += $amount;
        }

        // Rate is in ten-thousandths of one percent, rounded half up to a minor unit.
        $discount = intdiv($total, 1000000) * $rate
            + intdiv(($total % 1000000) * $rate + 500000, 1000000);

        return self::allocate($lineAmounts, $discount);
    }

    public static function allocate(array $lineAmounts, int $discount): array
    {
        $total = 0;

        foreach ($lineAmounts as $amount) {
            if ($amount < 0 || $total > PHP_INT_MAX - $amount) {
                throw new InvalidArgumentException('Invalid line amount.');
            }
            $total += $amount;
        }
        if ($discount < 0 || $discount > $total) {
            throw new InvalidArgumentException('Invalid discount.');
        }

        $allocated = array_fill_keys(array_keys($lineAmounts), 0);
        if ($discount === 0) {
            return $allocated;
        }

        $remainders = [];
        $assigned   = 0;

        foreach ($lineAmounts as $key => $amount) {
            if ($amount !== 0 && $discount > intdiv(PHP_INT_MAX, $amount)) {
                throw new InvalidArgumentException('Amount exceeds allocation precision.');
            }
            $product         = $amount * $discount;
            $allocated[$key] = intdiv($product, $total);
            $assigned += $allocated[$key];
            $remainders[] = ['key' => $key, 'remainder' => $product % $total, 'order' => count($remainders)];
        }
        usort($remainders, static fn (array $a, array $b): int => ($b['remainder'] <=> $a['remainder']) ?: ($a['order'] <=> $b['order']));

        for ($i = 0; $i < $discount - $assigned; $i++) {
            $allocated[$remainders[$i]['key']]++;
        }

        return $allocated;
    }
}
