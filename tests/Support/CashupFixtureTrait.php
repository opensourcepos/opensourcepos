<?php

namespace Tests\Support;

use App\Models\Cashup;

trait CashupFixtureTrait
{
    protected function createCashup(int $openEmployeeId, int $closeEmployeeId, array $overrides = []): int
    {
        $cashupData = array_merge([
            'open_date'            => date('Y-m-d H:i:s'),
            'close_date'           => date('Y-m-d H:i:s'),
            'open_amount_cash'     => 0,
            'transfer_amount_cash' => 0,
            'closed_amount_cash'   => 0,
            'closed_amount_due'    => 0,
            'closed_amount_card'   => 0,
            'closed_amount_check'  => 0,
            'closed_amount_total'  => 0,
            'note'                 => 0,
            'description'          => 'Test cashup',
            'open_employee_id'     => $openEmployeeId,
            'close_employee_id'    => $closeEmployeeId,
            'deleted'              => 0,
        ], $overrides);

        model(Cashup::class)->save_value($cashupData, NEW_ENTRY);

        return (int) $cashupData['cashup_id'];
    }
}
