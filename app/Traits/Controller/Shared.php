<?php

namespace App\Traits\Controller;

use Config\OSPOS;

/**
 * Shared trait for common controller functionality
 */
trait Shared
{
    /**
     * Build supplier info array for views
     * 
     * @param object $supplier_info Supplier info object
     * @param array $data Data array to populate
     * @return void
     */
    protected function buildSupplierInfo(object $supplier_info, array &$data): void
    {
        $data['supplier'] = $supplier_info->company_name;
        $data['first_name'] = $supplier_info->first_name;
        $data['last_name'] = $supplier_info->last_name;
        $data['supplier_email'] = $supplier_info->email;
        $data['supplier_address'] = $supplier_info->address_1;
        if (!empty($supplier_info->zip) || !empty($supplier_info->city)) {
            $data['supplier_location'] = $supplier_info->zip . ' ' . $supplier_info->city;
        } else {
            $data['supplier_location'] = '';
        }
    }

    /**
     * Get mode label and customer required text based on sale mode
     * 
     * @param string $mode The sale mode
     * @return array{mode_label: string, customer_required: string}
     */
    protected function getSaleModeLabel(string $mode): array
    {
        return match ($mode) {
            'sale_invoice' => [
                'mode_label' => lang('Sales.invoice'),
                'customer_required' => lang('Sales.customer_required')
            ],
            'sale_quote' => [
                'mode_label' => lang('Sales.quote'),
                'customer_required' => lang('Sales.customer_required')
            ],
            'sale_work_order' => [
                'mode_label' => lang('Sales.work_order'),
                'customer_required' => lang('Sales.customer_required')
            ],
            'return' => [
                'mode_label' => lang('Sales.return'),
                'customer_required' => lang('Sales.customer_optional')
            ],
            default => [
                'mode_label' => lang('Sales.receipt'),
                'customer_required' => lang('Sales.customer_optional')
            ]
        };
    }

    /**
     * Build company info string from config
     * 
     * @return string
     */
    protected function buildCompanyInfo(): string
    {
        $config = config(OSPOS::class)->settings;
        $company_info = implode("\n", [$config['address'], $config['phone']]);

        if (!empty($config['account_number'])) {
            $company_info .= "\n" . lang('Sales.account_number') . ": " . $config['account_number'];
        }
        if (!empty($config['tax_id'])) {
            $company_info .= "\n" . lang('Sales.tax_id') . ": " . $config['tax_id'];
        }

        return $company_info;
    }

    /**
     * Initialize default tax code data for new entry
     * 
     * @return array
     */
    protected function initDefaultTaxCodeData(): array
    {
        return [
            'tax_code' => '',
            'tax_code_name' => '',
            'tax_code_type' => '0',
            'city' => '',
            'state' => '',
            'tax_rate' => '0.0000',
            'rate_tax_code' => '',
            'rate_tax_category_id' => 1,
            'tax_category' => '',
            'add_tax_category' => '',
            'rounding_code' => '0'
        ];
    }

    /**
     * Populate tax code data from existing tax code info
     * 
     * @param object $tax_code_info Tax code info object
     * @param object $tax_rate_info Tax rate info object
     * @return array
     */
    protected function buildTaxCodeData(object $tax_code_info, object $tax_rate_info): array
    {
        return [
            'tax_code' => $tax_code_info->tax_code,
            'tax_code_name' => $tax_code_info->tax_code_name,
            'tax_code_type' => $tax_code_info->tax_code_type,
            'city' => $tax_code_info->city,
            'state' => $tax_code_info->state,
            'rate_tax_code' => $tax_code_info->rate_tax_code,
            'rate_tax_category_id' => $tax_code_info->rate_tax_category_id,
            'tax_category' => $tax_code_info->tax_category,
            'add_tax_category' => '',
            'tax_rate' => $tax_rate_info->tax_rate,
            'rounding_code' => $tax_rate_info->rounding_code
        ];
    }
}