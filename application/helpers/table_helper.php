<?php

function get_sales_manage_table($sales, $controller)
{
	$CI =& get_instance();
	$table='<table class="tablesorter table table-striped table-hover" id="sortable_table">';

	$headers = array('&nbsp;',
		$CI->lang->line('sales_receipt_number'),
		$CI->lang->line('sales_sale_time'),
		$CI->lang->line('customers_customer'),
		$CI->lang->line('sales_amount_tendered'),
		$CI->lang->line('sales_amount_due'),
		$CI->lang->line('sales_change_due'),
		$CI->lang->line('sales_payment'));
		
	if($CI->config->item('invoice_enable') == TRUE)
	{
		$headers[] = $CI->lang->line('sales_invoice_number');
		$headers[] = '&nbsp';
		$headers[] = '&nbsp';
		$headers[] = '&nbsp';
	}
	else
	{
		$headers[] = '&nbsp';
		$headers[] = '&nbsp';
	}

	$table.='<thead><tr>';
	foreach($headers as $header)
	{
		$table.="<th>$header</th>";
	}
	$table.='</tr></thead><tbody>';
	$table.=get_sales_manage_table_data_rows($sales, $controller);
	$table.='</tbody></table>';

	return $table;
}

/*
 Gets the html data rows for the sales.
 */
function get_sales_manage_table_data_rows($sales, $controller)
{
	$CI =& get_instance();
	$table_data_rows = '';
	$sum_amount_tendered = 0;
	$sum_amount_due = 0;
	$sum_change_due = 0;

	foreach($sales as $key=>$sale)
	{
		$table_data_rows .= get_sales_manage_sale_data_row($sale, $controller);
		
		$sum_amount_tendered += $sale['amount_tendered'];
		$sum_amount_due += $sale['amount_due'];
		$sum_change_due += $sale['change_due'];
	}

	if($table_data_rows == '')
	{
		$table_data_rows .= "<tr><td colspan='12'><div class='alert alert-dismissible alert-info'>".$CI->lang->line('sales_no_sales_to_display')."</div></td></tr>";
	}
	else
	{
		$table_data_rows .= "<tr class='static-last'><td>&nbsp;</td><td>".$CI->lang->line('sales_total')."</td><td>&nbsp;</td><td>&nbsp;</td><td>".to_currency($sum_amount_tendered)."</td><td>".to_currency($sum_amount_due)."</td><td>".to_currency($sum_change_due)."</td><td colspan=\"5\"></td></tr>";
	}

	return $table_data_rows;
}

function get_sales_manage_sale_data_row($sale, $controller)
{
	$CI =& get_instance();
	$controller_name = $CI->uri->segment(1);

	$table_data_row='<tr>';
	$table_data_row.='<td width="3%"><input class="print_hide" type="checkbox" id="sale_' . $sale['sale_id'] . '" value="' . $sale['sale_id']. '" /></td>';
	$table_data_row.='<td width="15%">'.'POS ' . $sale['sale_id'] . '</td>';
	$table_data_row.='<td width="17%">'.date( $CI->config->item('dateformat') . ' ' . $CI->config->item('timeformat'), strtotime($sale['sale_time']) ).'</td>';
	$table_data_row.='<td width="23%">'.character_limiter( $sale['customer_name'], 25).'</td>';
	$table_data_row.='<td width="8%">'.to_currency( $sale['amount_tendered'] ).'</td>';
	$table_data_row.='<td width="8%">'.to_currency( $sale['amount_due'] ).'</td>';
	$table_data_row.='<td width="8%">'.to_currency( $sale['change_due'] ).'</td>';
	if($CI->config->item('invoice_enable') == TRUE)
	{
		$table_data_row.='<td width="12%">'.$sale['payment_type'].'</td>';
		$table_data_row.='<td width="8%">'.$sale['invoice_number'].'</td>';
	}
	else
	{
		// this size includes the 8% of invoice number and 5% of the invoice glyphicon, plus of course the 12% for the field itself
		$table_data_row.='<td width="25%">'.$sale['payment_type'].'</td>';
	}
	$table_data_row.='<td width="5%" class="print_hide">'.anchor($controller_name."/edit/" . $sale['sale_id'], '<span class="glyphicon glyphicon-edit"></span>', array('class'=>'modal-dlg modal-btn-delete modal-btn-submit print_hide', 'title'=>$CI->lang->line($controller_name.'_update'))).'</td>';
	$table_data_row.='<td width="5%" class="print_hide">'.anchor($controller_name."/receipt/" . $sale['sale_id'], '<span class="glyphicon glyphicon-print"></span>', array('class'=>'print_hide', 'title'=>$CI->lang->line('sales_show_receipt'))).'</td>';
	if($CI->config->item('invoice_enable') == TRUE)
	{
		$table_data_row.='<td width="5%" class="print_hide">'.anchor($controller_name."/invoice/" . $sale['sale_id'], '<span class="glyphicon glyphicon-list-alt"></span>', array('class'=>'print_hide', 'title'=>$CI->lang->line('sales_show_invoice'))).'</td>';
	}
	$table_data_row.='</tr>';

	return $table_data_row;
}

/*
Get the sales payments summary
*/
function get_sales_manage_payments_summary($payments, $sales, $controller)
{
	$CI =& get_instance();
	$table='<div id="report_summary">';

	foreach($payments as $key=>$payment)
	{
		$amount = $payment['payment_amount'];

		// WARNING: the strong assumption here is that if a change is due it was a cash transaction always
		// therefore we remove from the total cash amount any change due
		if( $payment['payment_type'] == $CI->lang->line('sales_cash') )
		{
			foreach($sales as $key=>$sale)
			{
				$amount -= $sale['change_due'];
			}
		}
		$table.='<div class="summary_row">'.$payment['payment_type'].': '.to_currency( $amount ) . '</div>';
	}
	$table.='</div>';
	return $table;
}

function transform_headers($array)
	if($CI->Employee->has_grant('messages', $CI->session->userdata('person_id')))
	{
		$headers[] = '&nbsp';
	}
{
 	return json_encode(array_map(function($v) {
		return array('field' => key($v), 'title' => current($v), 'checkbox' => (key($v) == 'checkbox'));
	}, array_merge(array(array('checkbox' => 'select')), $array, array(array('edit' => '')))));
}

function get_people_manage_table_headers()
{
	$CI =& get_instance();

	$headers = array(
		array('id' => $CI->lang->line('common_id')),
		array('last_name' => $CI->lang->line('common_last_name')),
		array('first_name' => $CI->lang->line('common_first_name')),
		array('email' => $CI->lang->line('common_email')),
		array('phone_number' => $CI->lang->line('common_phone_number'))
	);
	
	return transform_headers($headers);
}

function get_person_data_row($person, $controller) {
	$CI =& get_instance();
	$controller_name=strtolower(get_class($CI));

	$row = array (
		'id' => $person->person_id,
		'last_name' => character_limiter($person->last_name,13),
		'first_name' => character_limiter($person->first_name,13),
		'email' => empty($person->email) ? '' : mailto($person->email,character_limiter($person->email,22)),
		'phone_number' => character_limiter($person->phone_number,13),
		'messages' => anchor("Messages/view/$person->person_id", '<span class="glyphicon glyphicon-phone"></span>', 
			array('class'=>"modal-dlg modal-btn-submit", 'title'=>$CI->lang->line('messages_sms_send'))),
		'edit' => anchor($controller_name."/view/$person->person_id", '<span class="glyphicon glyphicon-edit"></span>',
			array('class'=>"modal-dlg modal-btn-submit", 'title'=>$CI->lang->line($controller_name.'_update'))
	));
}

function get_detailed_data_row($row, $controller)
{
	$table_data_row='<tr>';
	$table_data_row.='<td><a href="#" class="expand">+</a></td>';
	foreach($row as $cell)
	{
		$table_data_row.='<td>';
		$table_data_row.=$cell;
		$table_data_row.='</td>';
	}
	$table_data_row.='</tr>';

	return $table_data_row;
}

function get_suppliers_manage_table_headers()
	if($CI->Employee->has_grant('messages', $CI->session->userdata('person_id')))
	{
		$headers[] = '&nbsp';
	}
{
	$CI =& get_instance();

	$headers = array(
		array('id' => $CI->lang->line('common_id')),
		array('company_name' => $CI->lang->line('suppliers_company_name')),
		array('agency_name' => $CI->lang->line('suppliers_agency_name')),
		array('last_name' => $CI->lang->line('common_last_name')),
		array('first_name' => $CI->lang->line('common_first_name')),
		array('email' => $CI->lang->line('common_email')),
		array('phone_number' => $CI->lang->line('common_phone_number'))
	);

	return transform_headers($headers);
}

function get_supplier_data_row($supplier, $controller) {
	$CI =& get_instance();
	$controller_name=strtolower(get_class($CI));

	return array (
		'id' => $supplier->person_id,
		'company_name' => character_limiter($supplier->company_name,13),
		'agency_name' => character_limiter($supplier->agency_name,13),
		'last_name' => character_limiter($supplier->last_name,13),
		'first_name' => character_limiter($supplier->first_name,13),
		'email' => empty($supplier->email) ? '' : mailto($supplier->email,character_limiter($supplier->email,22)),
		'phone_number' => character_limiter($supplier->phone_number,13),
		'messages' => anchor("Messages/view/$supplier->person_id", '<span class="glyphicon glyphicon-phone"></span>', 
			array('class'=>"modal-dlg modal-btn-submit", 'title'=>$CI->lang->line('messages_sms_send'))),
		'edit' => anchor($controller_name."/view/$supplier->person_id", '<span class="glyphicon glyphicon-edit"></span>',
			array('class'=>"modal-dlg modal-btn-submit", 'title'=>$CI->lang->line($controller_name.'_update'))
		));
}

function get_items_manage_table_headers()
{
	$CI =& get_instance();

	$headers = array(
		array('id' => $CI->lang->line('common_id')),
		array('item_number' => $CI->lang->line('items_item_number')),
		array('item_name' => $CI->lang->line('items_name')),
		array('item_category' => $CI->lang->line('items_category')),
		array('company_name' => $CI->lang->line('suppliers_company_name')),
		array('cost_price' => $CI->lang->line('items_cost_price')),
		array('unit_price' => $CI->lang->line('items_unit_price')),
		array('quantity' => $CI->lang->line('items_quantity')),
		array('tax_percents' => $CI->lang->line('items_tax_percents')),
		array('item_pic' => $CI->lang->line('items_image')),
		array('inventory' => ''),
		array('stock' => '')
	);

	return transform_headers($headers);
}

function get_item_data_row($item, $controller) {

	$CI =& get_instance();
	$item_tax_info=$CI->Item_taxes->get_info($item->item_id);
	$tax_percents = '';
	foreach($item_tax_info as $tax_info)
	{
		$tax_percents.=to_tax_decimals($tax_info['percent']) . '%, ';
	}
	// remove ', ' from last item
	$tax_percents=substr($tax_percents, 0, -2);
	$controller_name=strtolower(get_class($CI));

	$image = '';
	if (!empty($item->pic_id))
	{
		$images = glob("uploads/item_pics/" . $item->pic_id . ".*");
		if (sizeof($images) > 0)
		{
			$image .= '<a class="rollover" href="'. base_url($images[0]) .'"><img src="'.site_url('items/pic_thumb/'.$item->pic_id).'"></a>';
		}
	}

	return array (
		'id' => $item->item_id,
		'item_number' => $item->item_number,
		'item_name' => character_limiter($item->name,13),
		'item_category' => character_limiter($item->category,13),
		'company_name' => character_limiter($item->company_name,20),
		'cost_price' => to_currency($item->cost_price),
		'unit_price' => to_currency($item->unit_price),
		'quantity' => to_quantity_decimals($item->quantity),
		'tax_percents' => $tax_percents,
		'item_pic' => $image,
		'inventory' => anchor($controller_name."/inventory/$item->item_id", '<span class="glyphicon glyphicon-pushpin"></span>',
			array('class' => "modal-dlg modal-btn-submit", 'title' => $CI->lang->line($controller_name.'_count'))
		),
		'stock' => anchor($controller_name."/count_details/$item->item_id", '<span class="glyphicon glyphicon-list-alt"></span>',
		array('class' => "modal-dlg modal-btn-submit", 'title' => $CI->lang->line($controller_name.'_details_count'))
		),
		'edit' => anchor($controller_name."/view/$item->item_id", '<span class="glyphicon glyphicon-edit"></span>',
			array('class' => "modal-dlg modal-btn-submit", 'title' => $CI->lang->line($controller_name.'_update'))
		));
}

function get_giftcards_manage_table_headers()
{
	$CI =& get_instance();

	$headers = array(
		array('id' => $CI->lang->line('common_id')),
		array('last_name' => $CI->lang->line('common_last_name')),
		array('first_name' => $CI->lang->line('common_first_name')),
		array('giftcard_number' => $CI->lang->line('giftcards_giftcard_number')),
		array('giftcard_value' => $CI->lang->line('giftcards_card_value'))
	);

	return transform_headers($headers);
}

function get_giftcard_data_row($giftcard, $controller) {
	$CI =& get_instance();
	$controller_name=strtolower(get_class($CI));

	return array (
		'id' => $giftcard->giftcard_id,
		'last_name' => character_limiter($giftcard->last_name,13),
		'first_name' => character_limiter($giftcard->first_name,13),
		'giftcard_number' => $giftcard->giftcard_number,
		'giftcard_value' => to_currency($giftcard->value),
		'edit' => anchor($controller_name."/view/$giftcard->giftcard_id", '<span class="glyphicon glyphicon-edit"></span>',
			array('class'=>"modal-dlg modal-btn-submit", 'title'=>$CI->lang->line($controller_name.'_update'))
		));
}

function get_item_kits_manage_table_headers()
{
	$CI =& get_instance();

	$headers = array(
		array('id' => $CI->lang->line('item_kits_kit')),
		array('kit_name' => $CI->lang->line('item_kits_name')),
		array('kit_description' => $CI->lang->line('item_kits_description')),
		array('cost_price' => $CI->lang->line('items_cost_price')),
		array('unit_price' => $CI->lang->line('items_unit_price'))
	);

	return transform_headers($headers);
}


function get_item_kit_data_row($item_kit, $controller) {
	$CI =& get_instance();
	$controller_name=strtolower(get_class($CI));

	return array (
		'id' => 'KIT '.$item_kit->item_kit_id,
		'kit_name' => character_limiter($item_kit->name,13),
		'kit_description' => character_limiter($item_kit->description,13),
		'cost_price' => character_limiter($item_kit->total_cost_price,13),
		'unit_price' => character_limiter($item_kit->total_unit_price,13),
		'edit' => anchor($controller_name."/view/$item_kit->item_kit_id", '<span class="glyphicon glyphicon-edit"></span>',
			array('class'=>"modal-dlg modal-btn-submit", 'title'=>$CI->lang->line($controller_name.'_update'))
		));
}

?>
