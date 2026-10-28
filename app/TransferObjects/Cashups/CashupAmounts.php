<?php

namespace App\TransferObjects\Cashups;

class CashupAmounts
{
    public function __construct(
        public readonly float $openAmountCash,
        public readonly float $transferAmountCash,
        public readonly float $closedAmountDue,
        public readonly float $closedAmountCash,
        public readonly float $closedAmountCard,
        public readonly float $closedAmountCheck
    ) {
    }
}
