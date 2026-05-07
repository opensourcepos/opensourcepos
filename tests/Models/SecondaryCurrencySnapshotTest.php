<?php

namespace Tests\Models;

use App\Models\Expense;
use App\Models\Receiving;
use App\Models\Sale;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\OSPOS;

class SecondaryCurrencySnapshotTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = true;
    protected $migrateOnce = true;
    protected $refresh = true;
    protected $namespace = null;

    private function setSecondaryRate(float $rate): void
    {
        $config = config(OSPOS::class);
        $config->settings['secondary_currency_rate'] = $rate;
    }

    private function getFirstItem(): array
    {
        return $this->db->table('items')->orderBy('item_id', 'asc')->get()->getRowArray();
    }

    private function getFirstEmployeeId(): int
    {
        $row = $this->db->table('employees')->orderBy('person_id', 'asc')->get()->getRowArray();

        return (int) $row['person_id'];
    }

    public function testSaleSaveStoresSecondaryCurrencyRateSnapshot(): void
    {
        $config = config(OSPOS::class);
        $originalRate = (float) ($config->settings['secondary_currency_rate'] ?? 0);

        try {
            $saleModel = model(Sale::class);
            $item = $this->getFirstItem();
            $employeeId = $this->getFirstEmployeeId();

            $items = [[
                'item_id'        => (int) $item['item_id'],
                'line'           => 1,
                'description'    => 'Snapshot test item',
                'serialnumber'   => '',
                'quantity'       => 1,
                'discount'       => 0,
                'discount_type'  => FIXED,
                'cost_price'     => (float) $item['cost_price'],
                'price'          => (float) $item['unit_price'],
                'item_location'  => 1,
                'print_option'   => 0
            ]];
            $salesTaxes = [[], []];
            $payments = [];

            $this->setSecondaryRate(1.11);
            $saleStatus = SUSPENDED;
            $firstSaleId = $saleModel->save_value(
                NEW_ENTRY,
                $saleStatus,
                $items,
                NEW_ENTRY,
                $employeeId,
                'Snapshot test sale',
                null,
                null,
                null,
                SALE_TYPE_POS,
                $payments,
                null,
                $salesTaxes
            );

            $firstSale = $saleModel->get_info($firstSaleId)->getRowArray();
            $this->assertSame(1.11, (float) $firstSale['secondary_currency_rate']);

            $this->setSecondaryRate(2.22);
            $secondSaleStatus = SUSPENDED;
            $secondSaleId = $saleModel->save_value(
                NEW_ENTRY,
                $secondSaleStatus,
                $items,
                NEW_ENTRY,
                $employeeId,
                'Snapshot test sale 2',
                null,
                null,
                null,
                SALE_TYPE_POS,
                $payments,
                null,
                $salesTaxes
            );

            $secondSale = $saleModel->get_info($secondSaleId)->getRowArray();
            $this->assertSame(2.22, (float) $secondSale['secondary_currency_rate']);

            $firstSaleAgain = $saleModel->get_info($firstSaleId)->getRowArray();
            $this->assertSame(1.11, (float) $firstSaleAgain['secondary_currency_rate']);
        } finally {
            $this->setSecondaryRate($originalRate);
        }
    }

    public function testReceivingSaveStoresSecondaryCurrencyRateSnapshot(): void
    {
        $config = config(OSPOS::class);
        $originalRate = (float) ($config->settings['secondary_currency_rate'] ?? 0);

        try {
            $receivingModel = model(Receiving::class);
            $item = $this->getFirstItem();
            $employeeId = $this->getFirstEmployeeId();

            $items = [[
                'item_id'            => (int) $item['item_id'],
                'line'               => 1,
                'description'        => 'Snapshot test receiving item',
                'serialnumber'       => '',
                'quantity'           => 1,
                'receiving_quantity' => 1,
                'discount'           => 0,
                'discount_type'      => FIXED,
                'price'              => (float) $item['unit_price'],
                'item_location'      => 1
            ]];

            $this->setSecondaryRate(3.33);
            $firstReceivingId = $receivingModel->save_value($items, NEW_ENTRY, $employeeId, 'Snapshot receiving', 'REC-1', null);
            $firstReceiving = $receivingModel->get_info($firstReceivingId)->getRowArray();
            $this->assertSame(3.33, (float) $firstReceiving['secondary_currency_rate']);

            $this->setSecondaryRate(4.44);
            $secondReceivingId = $receivingModel->save_value($items, NEW_ENTRY, $employeeId, 'Snapshot receiving 2', 'REC-2', null);
            $secondReceiving = $receivingModel->get_info($secondReceivingId)->getRowArray();
            $this->assertSame(4.44, (float) $secondReceiving['secondary_currency_rate']);

            $firstReceivingAgain = $receivingModel->get_info($firstReceivingId)->getRowArray();
            $this->assertSame(3.33, (float) $firstReceivingAgain['secondary_currency_rate']);
        } finally {
            $this->setSecondaryRate($originalRate);
        }
    }

    public function testExpenseSaveStoresSecondaryCurrencyRateSnapshot(): void
    {
        $config = config(OSPOS::class);
        $originalRate = (float) ($config->settings['secondary_currency_rate'] ?? 0);

        try {
            $expenseModel = model(Expense::class);
            $employeeId = $this->getFirstEmployeeId();
            $expenseCategory = $this->db->table('expense_categories')->orderBy('expense_category_id', 'asc')->get()->getRowArray();
            $expenseCategoryId = $expenseCategory ? (int) $expenseCategory['expense_category_id'] : null;

            $expenseData = [
                'date'                => date('Y-m-d H:i:s'),
                'supplier_id'         => null,
                'supplier_tax_code'   => 'SNAPSHOT',
                'amount'              => 12.34,
                'tax_amount'          => 0,
                'payment_type'        => lang('Expenses.cash'),
                'expense_category_id' => $expenseCategoryId,
                'description'         => 'Snapshot expense',
                'employee_id'         => $employeeId,
                'deleted'             => false
            ];

            $this->setSecondaryRate(5.55);
            $this->assertTrue($expenseModel->save_value($expenseData, NEW_ENTRY));
            $firstExpenseId = (int) $expenseData['expense_id'];
            $firstExpense = $expenseModel->get_info($firstExpenseId);
            $this->assertSame(5.55, (float) $firstExpense->secondary_currency_rate);

            $this->setSecondaryRate(6.66);
            $secondExpenseData = $expenseData;
            unset($secondExpenseData['expense_id']);
            unset($secondExpenseData['secondary_currency_rate']);
            $secondExpenseData['description'] = 'Snapshot expense 2';
            $this->assertTrue($expenseModel->save_value($secondExpenseData, NEW_ENTRY));
            $secondExpenseId = (int) $secondExpenseData['expense_id'];
            $secondExpense = $expenseModel->get_info($secondExpenseId);
            $this->assertSame(6.66, (float) $secondExpense->secondary_currency_rate);

            $firstExpenseAgain = $expenseModel->get_info($firstExpenseId);
            $this->assertSame(5.55, (float) $firstExpenseAgain->secondary_currency_rate);
        } finally {
            $this->setSecondaryRate($originalRate);
        }
    }
}
