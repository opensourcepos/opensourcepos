<?php

use App\Models\Enums\Rounding_mode;

/**
 * Tax Configuration tabular helpers
 */

/**
 * Get the header for the taxes tabular view
 */
function get_tax_code_table_headers(): string
{
    $headers = [
        ['tax_code'      => lang('Taxes.tax_code')],
        ['tax_code_name' => lang('Taxes.tax_code_name')],
        ['city'          => lang('Common.city')],
        ['state'         => lang('Common.state')]
    ];

    return transform_headers($headers);
}

/**
 * Get the html data row for the tax
 */
function get_tax_code_data_row($tax_code_row): array
{
    $controller_name = 'tax_codes';

    return [
        'tax_code'      => $tax_code_row->tax_code,
        'tax_code_name' => $tax_code_row->tax_code_name,
        'tax_code_type' => $tax_code_row->tax_code_type,
        'city'          => $tax_code_row->city,
        'state'         => $tax_code_row->state,
        'edit'          => anchor(
            "$controller_name/view_tax_codes/$tax_code_row->tax_code",
            '<span class="glyphicon glyphicon-edit"></span>',
            [
                'class'           => 'modal-dlg',
                'data-btn-submit' => lang('Common.submit'),
                'title'           => lang(ucfirst($controller_name) . ".update_tax_codes")
            ]
        )
    ];
}

/**
 * Get the header for the taxes tabular view
 */
function get_tax_categories_table_headers(): string
{
    $headers = [
        ['tax_category'       => lang('Taxes.tax_category_name')],
        ['tax_category_code'  => lang('Taxes.tax_category_code')],
        ['tax_group_sequence' => lang('Taxes.tax_group_sequence')],
    ];

    return transform_headers($headers);
}

/**
 * Get the html data row for the tax
 */
function get_tax_categories_data_row($tax_categories_row): array
{
    $controller_name = 'tax_categories';

    return [
        'tax_category_id'    => $tax_categories_row->tax_category_id,
        'tax_category'       => $tax_categories_row->tax_category,
        'tax_category_code'  => $tax_categories_row->tax_category_code,
        'tax_group_sequence' => $tax_categories_row->tax_group_sequence,
        'edit'               => anchor(
            "$controller_name/view/$tax_categories_row->tax_category_id",
            '<span class="glyphicon glyphicon-edit"></span>',
            [
                'class'           => 'modal-dlg',
                'data-btn-submit' => lang('Common.submit'),
                'title'           => lang(ucfirst($controller_name) . ".update")
            ]
        )
    ];
}

/**
 * Get the header for the taxes tabular view
 */
function get_tax_jurisdictions_table_headers(): string
{
    $headers = [
        ['jurisdiction_id'     => lang('Taxes.jurisdiction_id')],
        ['jurisdiction_name'   => lang('Taxes.jurisdiction_name')],
        ['reporting_authority' => lang('Taxes.reporting_authority')]
    ];

    return transform_headers($headers);
}

/**
 * Get the html data row for the tax
 */
function get_tax_jurisdictions_data_row($tax_jurisdiction_row): array
{
    $controller_name = 'tax_jurisdictions';

    return [
        'jurisdiction_id'     => $tax_jurisdiction_row->jurisdiction_id,
        'jurisdiction_name'   => $tax_jurisdiction_row->jurisdiction_name,
        'reporting_authority' => $tax_jurisdiction_row->reporting_authority,
        'edit'                => anchor(
            "$controller_name/view/$tax_jurisdiction_row->jurisdiction_id",
            '<span class="glyphicon glyphicon-edit"></span>',
            [
                'class'           => 'modal-dlg',
                'data-btn-submit' => lang('Common.submit'),
                'title'           => lang(ucfirst($controller_name) . ".update")
            ]
        )
    ];
}

/**
 * Get the header for the taxes tabular view
 */
function get_tax_rates_manage_table_headers(): string
{
    $headers = [
        ['tax_code'           => lang('Taxes.tax_code')],
        ['tax_code_name'      => lang('Taxes.tax_code_name')],
        ['jurisdiction_name'  => lang('Taxes.jurisdiction_name')],
        ['tax_category'       => lang('Taxes.tax_category')],
        ['tax_rate'           => lang('Taxes.tax_rate')],
        ['rounding_code_name' => lang('Taxes.rounding_code')]
    ];

    return transform_headers($headers);
}

/**
 * Get the html data row for the tax
 */
function get_tax_rates_data_row($tax_rates_row): array
{
    $router = service('router');
    $controller_name = strtolower($router->controllerName());

    return [
        'tax_rate_id'        => $tax_rates_row->tax_rate_id,
        'tax_code'           => $tax_rates_row->tax_code,
        'tax_code_name'      => $tax_rates_row->tax_code_name,
        'tax_category'       => $tax_rates_row->tax_category,
        'tax_rate'           => $tax_rates_row->tax_rate,
        'jurisdiction_name'  => $tax_rates_row->jurisdiction_name,
        'tax_rounding_code'  => $tax_rates_row->tax_rounding_code,
        'rounding_code_name' => Rounding_mode::get_rounding_code_name($tax_rates_row->tax_rounding_code),
        'edit'               => anchor(
            "$controller_name/view/$tax_rates_row->tax_rate_id",
            '<span class="glyphicon glyphicon-edit"></span>',
            [
                'class'           => 'modal-dlg',
                'data-btn-submit' => lang('Common.submit'),
                'title'           => lang(ucfirst($controller_name) . ".update")
            ]
        )
    ];
}
