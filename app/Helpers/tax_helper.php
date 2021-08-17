<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Tax Configuration tabular helpers
 */

/*
Get the header for the taxes tabular view
*/
function get_tax_code_table_headers()
{
	$CI =& get_instance();

	$headers = array(
		array('tax_code' => lang('Taxes.tax_code')),
		array('tax_code_name' => lang('Taxes.tax_code_name')),
		array('city' => lang('Common.city')),
		array('state' => lang('Common.state'))
	);

	return transform_headers($headers);
}

/*
Get the html data row for the tax
*/
function get_tax_code_data_row($tax_code_row)
{
	$CI =& get_instance();

	$controller_name = 'tax_codes';

	return array (
		'tax_code' => $tax_code_row->tax_code,
		'tax_code_name' => $tax_code_row->tax_code_name,
		'tax_code_type' => $tax_code_row->tax_code_type,
		'city' => $tax_code_row->city,
		'state' => $tax_code_row->state,
		'edit' => anchor($controller_name."/view_tax_codes/$tax_code_row->tax_code", '<span class="glyphicon glyphicon-edit"></span>',
			array('class'=>'modal-dlg', 'data-btn-submit' => lang('Common.submit'), 'title'=>lang($controller_name . '.update_tax_codes'))
		)
	);
}

/*
Get the header for the taxes tabular view
*/
function get_tax_categories_table_headers()
{
	$CI =& get_instance();

	$headers = array(
		array('tax_category' => lang('Taxes.tax_category_name')),
		array('tax_category_code' => lang('Taxes.tax_category_code')),
		array('tax_group_sequence' => lang('Taxes.tax_group_sequence')),
	);

	return transform_headers($headers);
}

/*
Get the html data row for the tax
*/
function get_tax_categories_data_row($tax_categories_row)
{
	$CI =& get_instance();

	$controller_name = 'tax_categories';

	return array (
		'tax_category_id' => $tax_categories_row->tax_category_id,
		'tax_category' => $tax_categories_row->tax_category,
		'tax_category_code' => $tax_categories_row->tax_category_code,
		'tax_group_sequence' => $tax_categories_row->tax_group_sequence,
		'edit' => anchor($controller_name."/view/$tax_categories_row->tax_category_id", '<span class="glyphicon glyphicon-edit"></span>',
			array('class'=>'modal-dlg', 'data-btn-submit' => lang('Common.submit'), 'title'=>lang($controller_name . '.update'))
		)
	);
}

/*
Get the header for the taxes tabular view
*/
function get_tax_jurisdictions_table_headers()
{
	$CI =& get_instance();

	$headers = array(
		array('jurisdiction_id' => lang('Taxes.jurisdiction_id')),
		array('jurisdiction_name' => lang('Taxes.jurisdiction_name')),
		array('reporting_authority' => lang('Taxes.reporting_authority'))
	);

	return transform_headers($headers);
}

/*
Get the html data row for the tax
*/
function get_tax_jurisdictions_data_row($tax_jurisdiction_row)
{
	$CI =& get_instance();
	$controller_name='tax_jurisdictions';

	return array (
		'jurisdiction_id' => $tax_jurisdiction_row->jurisdiction_id,
		'jurisdiction_name' => $tax_jurisdiction_row->jurisdiction_name,
		'reporting_authority' => $tax_jurisdiction_row->reporting_authority,
		'edit' => anchor($controller_name."/view/$tax_jurisdiction_row->jurisdiction_id", '<span class="glyphicon glyphicon-edit"></span>',
			array('class'=>'modal-dlg', 'data-btn-submit' => lang('Common.submit'), 'title'=>lang($controller_name . '.update'))
		)
	);
}

/*
Get the header for the taxes tabular view
*/
function get_tax_rates_manage_table_headers()
{
	$CI =& get_instance();

	$headers = array(
		array('tax_code' => lang('Taxes.tax_code')),
		array('tax_code_name' => lang('Taxes.tax_code_name')),
		array('jurisdiction_name' => lang('Taxes.jurisdiction_name')),
		array('tax_category' => lang('Taxes.tax_category')),
		array('tax_rate' => lang('Taxes.tax_rate')),
		array('rounding_code_name' => lang('Taxes.rounding_code'))
	);

	return transform_headers($headers);
}

/*
Get the html data row for the tax
*/
function get_tax_rates_data_row($tax_rates_row)
{
	$CI =& get_instance();

	$controller_name = strtolower(get_class($CI));

	return array (
		'tax_rate_id' => $tax_rates_row->tax_rate_id,
		'tax_code' => $tax_rates_row->tax_code,
		'tax_code_name' => $tax_rates_row->tax_code_name,
		'tax_category' => $tax_rates_row->tax_category,
		'tax_rate' => $tax_rates_row->tax_rate,
		'jurisdiction_name' => $tax_rates_row->jurisdiction_name,
		'tax_rounding_code' =>$tax_rates_row->tax_rounding_code,
		'rounding_code_name' => Rounding_mode::get_rounding_code_name($tax_rates_row->tax_rounding_code),
		'edit' => anchor($controller_name."/view/$tax_rates_row->tax_rate_id", '<span class="glyphicon glyphicon-edit"></span>',
			array('class'=>'modal-dlg', 'data-btn-submit' => lang('Common.submit'), 'title'=>lang($controller_name . '.update'))
		)
	);
}

?>
