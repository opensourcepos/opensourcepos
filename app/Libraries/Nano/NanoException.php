<?php

declare(strict_types=1);

namespace App\Libraries\Nano;

use RuntimeException;

/**
 * Raised when a Nano payment cannot be credited: a block already spent on
 * another sale, a sale already paid by another block, or a malformed claim.
 * A caller catches this to show the cashier why the reference block was
 * refused, while the sale stays unpaid.
 */
final class NanoException extends RuntimeException
{
}
