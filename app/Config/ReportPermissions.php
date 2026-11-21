<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class ReportPermissions extends BaseConfig
{
    public array $routes = [
        'summary_sales'             => 'reports_sales',
        'summary_expenses_categories' => 'reports_expenses_categories',
        'summary_payments'          => 'reports_payments',
        'summary_discounts'         => 'reports_discounts',
        'summary_categories'        => 'reports_categories',
        'summary_customers'         => 'reports_customers',
        'summary_items'             => 'reports_items',
        'summary_suppliers'         => 'reports_suppliers',
        'summary_employees'         => 'reports_employees',
        'summary_taxes'             => 'reports_taxes',
        'summary_sales_taxes'       => 'reports_sales_taxes',
        'detailed_item_sales'       => 'reports_sales',
        'detailed_sales'            => 'reports_sales',
        'detailed_receivings'       => 'reports_receivings',
        'inventory_summary'         => 'reports_inventory',
        'inventory_low'             => 'reports_inventory',
        'specific_customers'        => 'reports_customers',
        'specific_employees'        => 'reports_employees',
        'specific_discounts'        => 'reports_discounts',
        'specific_suppliers'        => 'reports_suppliers',
        'get_detailed_sales'        => 'reports_sales',
        'get_detailed_receivings'   => 'reports_receivings',
    ];

    public array $helperRoutes = [
        '',
        'index',
        'getIndex',
        'date_input',
        'date_input_only',
        'date_input_sales',
        'dateInputItemSales',
        'date_input_recv',
        'summary_discounts_input',
        'specific_customer_input',
        'get_payment_type',
        'specific_employee_input',
        'specific_discount_input',
        'specific_supplier_input',
        'get_sale_type_options',
        'inventory_summary_input',
    ];
}
