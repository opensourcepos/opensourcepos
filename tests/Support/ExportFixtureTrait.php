<?php

namespace Tests\Support;

trait ExportFixtureTrait
{
    protected function saleExportFixture(array $overrides = []): object
    {
        return (object) array_merge([
            'sale_id'         => 1,
            'sale_time'       => '2024-01-15 10:30:00',
            'customer_name'   => 'Jane Doe',
            'amount_due'      => '10.00',
            'amount_tendered' => '20.00',
            'change_due'      => '10.00',
            'payment_type'    => 'Cash',
            'invoice_number'  => 'INV-1',
        ], $overrides);
    }

    protected function employeeExportFixture(array $overrides = []): object
    {
        return (object) array_merge([
            'person_id'    => 1,
            'last_name'    => 'Smith',
            'first_name'   => 'John',
            'email'        => 'john@example.com',
            'phone_number' => '555-1234',
            'address_1'    => '123 Main St',
            'address_2'    => '',
            'city'         => 'Springfield',
            'state'        => 'IL',
            'zip'          => '62701',
            'country'      => 'US',
            'comments'     => '',
        ], $overrides);
    }

    protected function customerExportFixture(array $overrides = []): object
    {
        return (object) array_merge([
            'person_id'    => 1,
            'last_name'    => 'Doe',
            'first_name'   => 'Jane',
            'email'        => 'jane@example.com',
            'phone_number' => '555-5678',
            'employee_id'  => 2,
        ], $overrides);
    }

    protected function customerStatsFixture(array $overrides = []): object
    {
        return (object) array_merge(['total' => '150.00'], $overrides);
    }

    protected function supplierExportFixture(array $overrides = []): object
    {
        return (object) array_merge([
            'person_id'    => 1,
            'company_name' => 'Acme Corp',
            'agency_name'  => 'Acme Agency',
            'category'     => 'Wholesale',
            'last_name'    => 'Roe',
            'first_name'   => 'Richard',
            'email'        => 'richard@acme.com',
            'phone_number' => '555-9999',
        ], $overrides);
    }

    protected function itemExportFixture(array $overrides = []): object
    {
        return (object) array_merge([
            'item_id'               => 1,
            'item_number'           => 'ITEM-001',
            'name'                  => 'Widget',
            'category'              => 'Hardware',
            'company_name'          => 'Acme Corp',
            'cost_price'            => '5.00',
            'unit_price'            => '10.00',
            'quantity'              => '25',
            'description'           => 'A widget',
            'allow_alt_description' => 0,
            'is_serialized'         => 0,
            'pack_name'             => '',
        ], $overrides);
    }

    protected function giftcardExportFixture(array $overrides = []): object
    {
        return (object) array_merge([
            'giftcard_id'     => 1,
            'last_name'       => 'Doe',
            'first_name'      => 'Jane',
            'giftcard_number' => 'GC-001',
            'value'           => '25.00',
        ], $overrides);
    }

    protected function itemKitExportFixture(array $overrides = []): object
    {
        return (object) array_merge([
            'item_kit_id'      => 1,
            'item_kit_number'  => 'KIT-001',
            'name'             => 'Starter Kit',
            'description'      => 'A starter kit',
            'total_cost_price' => '15.00',
            'total_unit_price' => '30.00',
        ], $overrides);
    }

    protected function attributeExportFixture(array $overrides = []): object
    {
        return (object) array_merge([
            'definition_id'     => 1,
            'definition_name'   => 'Color',
            'definition_type'   => 'TEXT',
            'definition_flags'  => ['Items'],
            'definition_group'  => '',
        ], $overrides);
    }

    protected function expenseCategoryExportFixture(array $overrides = []): object
    {
        return (object) array_merge([
            'expense_category_id'  => 1,
            'category_name'        => 'Utilities',
            'category_description' => 'Utility bills',
        ], $overrides);
    }

    protected function expenseExportFixture(array $overrides = []): object
    {
        return (object) array_merge([
            'expense_id'          => 1,
            'date'                => '2024-01-15 00:00:00',
            'supplier_name'       => 'Acme Corp',
            'supplier_tax_code'   => 'TX1',
            'amount'              => '100.00',
            'tax_amount'          => '10.00',
            'payment_type'        => 'Cash',
            'category_name'       => 'Utilities',
            'description'         => 'Monthly bill',
            'first_name'          => 'John',
            'last_name'           => 'Smith',
            'employee_id'         => 2,
            'expense_category_id' => 1,
        ], $overrides);
    }

    protected function cashupExportFixture(array $overrides = []): object
    {
        return (object) array_merge([
            'cashup_id'             => 1,
            'open_date'             => '2024-01-15 08:00:00',
            'open_first_name'       => 'John',
            'open_last_name'        => 'Smith',
            'open_employee_id'      => 2,
            'open_amount_cash'      => '100.00',
            'transfer_amount_cash'  => '0.00',
            'close_date'            => '2024-01-15 18:00:00',
            'close_first_name'      => 'John',
            'close_last_name'       => 'Smith',
            'close_employee_id'     => 2,
            'closed_amount_cash'    => '500.00',
            'note'                  => 1,
            'description'           => 'End of day',
            'closed_amount_due'     => '0.00',
            'closed_amount_card'    => '200.00',
            'closed_amount_check'   => '0.00',
            'closed_amount_total'   => '400.00',
        ], $overrides);
    }

    protected function taxRateExportFixture(array $overrides = []): object
    {
        return (object) array_merge([
            'tax_rate_id'          => 1,
            'tax_code'             => 'TX1',
            'tax_code_name'        => 'Standard VAT',
            'tax_category'         => 'General',
            'tax_rate'             => '20.0000',
            'jurisdiction_name'    => 'Federal',
            'tax_rounding_code'    => 0,
            'rate_tax_code_id'     => 1,
            'rate_tax_category_id' => 1,
            'rate_jurisdiction_id' => 1,
        ], $overrides);
    }
}
