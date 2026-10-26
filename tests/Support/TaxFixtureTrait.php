<?php

namespace Tests\Support;

trait TaxFixtureTrait
{
    protected function baseTaxPayload(array $overrides = []): array
    {
        return array_merge([
            'default_tax_1_rate'        => '5.00',
            'default_tax_1_name'        => 'VAT',
            'default_tax_2_rate'        => '10.00',
            'default_tax_2_name'        => 'GST',
            'tax_included'              => '',
            'use_destination_based_tax' => '',
            'default_tax_code'          => '',
            'default_tax_category'      => '',
            'default_tax_jurisdiction'  => '',
            'tax_id'                    => '',
        ], $overrides);
    }
}
