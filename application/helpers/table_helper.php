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
	}, $array));
}

/*
Gets the html table to manage people.
*/
function get_people_manage_table_headers()
{
	$CI =& get_instance();

	$headers = array(
		array('checkbox' => 'select'),
		array('id' => $CI->lang->line('common_id')),
		array('last_name' => $CI->lang->line('common_last_name')),
		array('first_name' => $CI->lang->line('common_first_name')),
		array('email' => $CI->lang->line('common_email')),
		array('phone_number' => $CI->lang->line('common_phone_number')),
		array('edit' => '')
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
		'email' => mailto($person->email,character_limiter($person->email,22)),
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

/*
Gets the html table to manage suppliers.
*/
function get_supplier_manage_table($suppliers,$controller)
{
	$CI =& get_instance();
	$table='<table class="tablesorter table table-striped table-hover" id="sortable_table">';
	
	$headers = array('<input type="checkbox" id="select_all" />',
	$CI->lang->line('suppliers_company_name'),
	$CI->lang->line('suppliers_agency_name'),
	$CI->lang->line('common_last_name'),
	$CI->lang->line('common_first_name'),
	$CI->lang->line('common_email'),
	$CI->lang->line('common_phone_number'),
	$CI->lang->line('common_id'),
	'&nbsp');
	if($CI->Employee->has_grant('messages', $CI->session->userdata('person_id')))
	{
		$headers[] = '&nbsp';
	}
	
	$table.='<thead><tr>';
	foreach($headers as $header)
	{
		$table.="<th>$header</th>";
	}
	$table.='</tr></thead><tbody>';
	$table.=get_supplier_manage_table_data_rows($suppliers,$controller);
	$table.='</tbody></table>';

	return $table;
}

/*
Gets the html data rows for the supplier.
*/
function get_supplier_manage_table_data_rows($suppliers,$controller)
{
	$CI =& get_instance();
	$table_data_rows='';
	
	foreach($suppliers->result() as $supplier)
	{
		$table_data_rows.=get_supplier_data_row($supplier,$controller);
	}
	
	if($suppliers->num_rows()==0)
	{
		$table_data_rows.="<tr><td colspan='9'><div class='alert alert-dismissible alert-info'>".$CI->lang->line('common_no_persons_to_display')."</div></td></tr>";
	}
	
	return $table_data_rows;
}

function get_supplier_data_row($supplier,$controller)
{
	$CI =& get_instance();
	$controller_name=strtolower(get_class($CI));

	$table_data_row='<tr>';
	$table_data_row.="<td width='2%'><input type='checkbox' id='person_$supplier->person_id' value='".$supplier->person_id."'/></td>";
	$table_data_row.='<td width="15%">'.character_limiter($supplier->company_name,13).'</td>';
	$table_data_row.='<td width="14%">'.character_limiter($supplier->agency_name,13).'</td>';
	$table_data_row.='<td width="15%">'.character_limiter($supplier->last_name,13).'</td>';
	$table_data_row.='<td width="15%">'.character_limiter($supplier->first_name,13).'</td>';
	$table_data_row.='<td width="20%">'.mailto($supplier->email,character_limiter($supplier->email,22)).'</td>';
	$table_data_row.='<td width="10%">'.character_limiter($supplier->phone_number,13).'</td>';
	$table_data_row.='<td width="3%">'.character_limiter($supplier->person_id,5).'</td>';
	if($CI->Employee->has_grant('messages', $CI->session->userdata('person_id')))
	{
		$table_data_row.='<td width="3%">'.anchor("Messages/view/$supplier->person_id", '<span class="glyphicon glyphicon-phone"></span>', array('class'=>"modal-dlg modal-btn-submit", 'title'=>$CI->lang->line('messages_sms_send'))).'</td>';
		$table_data_row.='<td width="3%">'.anchor($controller_name."/view/$supplier->person_id", '<span class="glyphicon glyphicon-edit"></span>', array('class'=>"modal-dlg modal-btn-submit",'title'=>$CI->lang->line($controller_name.'_update'))).'</td>';
	}
	else
	{
		$table_data_row.='<td width="6%">'.anchor($controller_name."/view/$supplier->person_id", '<span class="glyphicon glyphicon-edit"></span>', array('class'=>"modal-dlg modal-btn-submit",'title'=>$CI->lang->line($controller_name.'_update'))).'</td>';		
	}
	$table_data_row.='</tr>';
	
	return $table_data_row;
}

/*
Gets the html table to manage items.
*/
function get_items_manage_table($items,$controller)
{
	$CI =& get_instance();
	$table='<table class="tablesorter table table-striped table-hover" id="sortable_table">';
	
	$headers = array('<input type="checkbox" id="select_all" />', 
	$CI->lang->line('items_item_number'),
	$CI->lang->line('items_name'),
	$CI->lang->line('items_category'),
	$CI->lang->line('suppliers_company_name'),
	$CI->lang->line('items_cost_price'),
	$CI->lang->line('items_unit_price'),
	$CI->lang->line('items_quantity'),
	$CI->lang->line('items_tax_percents'),
	$CI->lang->line('items_image'),
	'&nbsp;',
	'&nbsp;',
	'&nbsp;'	
	);
	
	$table.='<thead><tr>';
	foreach($headers as $header)
	{
		$table.="<th>$header</th>";
	}
	$table.='</tr></thead><tbody>';
	$table.=get_items_manage_table_data_rows($items,$controller);
	$table.='</tbody></table>';

	return $table;
}

/*
Gets the html data rows for the items.
*/
function get_items_manage_table_data_rows($items,$controller)
{
	$CI =& get_instance();
	$table_data_rows='';
	
	foreach($items->result() as $item)
	{
		$table_data_rows.=get_item_data_row($item,$controller);
	}
	
	if($items->num_rows()==0)
	{
		$table_data_rows.="<tr><td colspan='13'><div class='alert alert-dismissible alert-info'>".$CI->lang->line('items_no_items_to_display')."</div></td></tr>";
	}
	
	return $table_data_rows;
}

function get_item_data_row($item,$controller)
{
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

    $item_quantity='';
    
	$table_data_row='<tr>';
	$table_data_row.="<td width='2%'><input type='checkbox' id='item_$item->item_id' value='".$item->item_id."'/></td>";
	$table_data_row.='<td width="10%">'.$item->item_number.'</td>';
	$table_data_row.='<td width="15%">'.$item->name.'</td>';
	$table_data_row.='<td width="10%">'.$item->category.'</td>';
	$table_data_row.='<td width="10%">'.$item->company_name.'</td>';
	$table_data_row.='<td width="10%">'.to_currency($item->cost_price).'</td>';
	$table_data_row.='<td width="10%">'.to_currency($item->unit_price).'</td>';
    $table_data_row.='<td width="8%">'.to_quantity_decimals($item->quantity).'</td>';
	$table_data_row.='<td width="8%">'.$tax_percents.'</td>';
	$image = '';
	if (!empty($item->pic_id))
	{
		$images = glob("uploads/item_pics/" . $item->pic_id . ".*");
		if (sizeof($images) > 0)
		{
			$image.='<a class="rollover" href="'. base_url($images[0]) .'"><img src="'.site_url('items/pic_thumb/'.$item->pic_id).'"></a>';
		}
	}
	$table_data_row.='<td align="center" width="8%">' . $image . '</td>';
	$table_data_row.='<td width="3%">'.anchor($controller_name."/view/$item->item_id", '<span class="glyphicon glyphicon-edit"></span>', array('class'=>"modal-dlg modal-btn-new modal-btn-submit",'title'=>$CI->lang->line($controller_name.'_update'))).'</td>';
	$table_data_row.='<td width="3%">'.anchor($controller_name."/inventory/$item->item_id", '<span class="glyphicon glyphicon-pushpin"></span>', array('class'=>"modal-dlg modal-btn-submit",'title'=>$CI->lang->line($controller_name.'_count'))).'</td>';//inventory count
	$table_data_row.='<td width="3%">'.anchor($controller_name."/count_details/$item->item_id", '<span class="glyphicon glyphicon-list-alt"></span>', array('class'=>"modal-dlg",'title'=>$CI->lang->line($controller_name.'_details_count'))).'</td>';//inventory details
	$table_data_row.='</tr>';

	return $table_data_row;
}

/*
Gets the html table to manage giftcards.
*/
function get_giftcards_manage_table( $giftcards, $controller )
{
	$CI =& get_instance();
	$table='<table class="tablesorter table table-striped table-hover" id="sortable_table">';
	
	$headers = array('<input type="checkbox" id="select_all" />', 
	$CI->lang->line('common_last_name'),
	$CI->lang->line('common_first_name'),
	$CI->lang->line('giftcards_giftcard_number'),
	$CI->lang->line('giftcards_card_value'),
	'&nbsp');
	
	$table.='<thead><tr>';
	foreach($headers as $header)
	{
		$table.="<th>$header</th>";
	}
	$table.='</tr></thead><tbody>';
	$table.=get_giftcards_manage_table_data_rows( $giftcards, $controller );
	$table.='</tbody></table>';

	return $table;
}

/*
Gets the html data rows for the giftcard.
*/
function get_giftcards_manage_table_data_rows( $giftcards, $controller )
{
	$CI =& get_instance();
	$table_data_rows='';
	
	foreach($giftcards->result() as $giftcard)
	{
		$table_data_rows.=get_giftcard_data_row( $giftcard, $controller );
	}
	
	if($giftcards->num_rows()==0)
	{
		$table_data_rows.="<tr><td colspan='6'><div class='alert alert-dismissible alert-info'>".$CI->lang->line('giftcards_no_giftcards_to_display')."</div></td></tr>";
	}
	
	return $table_data_rows;
}

function get_giftcard_data_row($giftcard,$controller)
{
	$CI =& get_instance();
	$controller_name=strtolower(get_class($CI));

	$table_data_row='<tr>';
	$table_data_row.="<td width='3%'><input type='checkbox' id='giftcard_$giftcard->giftcard_id' value='".$giftcard->giftcard_id."'/></td>";
	$table_data_row.='<td width="15%">'.$giftcard->last_name.'</td>';
	$table_data_row.='<td width="15%">'.$giftcard->first_name.'</td>';
	$table_data_row.='<td width="15%">'.$giftcard->giftcard_number.'</td>';
	$table_data_row.='<td width="20%">'.to_currency($giftcard->value).'</td>';
	$table_data_row.='<td width="5%">'.anchor($controller_name."/view/$giftcard->giftcard_id", '<span class="glyphicon glyphicon-edit"></span>', array('class'=>"modal-dlg modal-btn-submit",'title'=>$CI->lang->line($controller_name.'_update'))).'</td>';
	$table_data_row.='</tr>';

	return $table_data_row;
}

/*
Gets the html table to manage item kits.
*/
function get_item_kits_manage_table( $item_kits, $controller )
{
	$CI =& get_instance();
	$table='<table class="tablesorter table table-striped table-hover" id="sortable_table">';
	
	$headers = array('<input type="checkbox" id="select_all" />', 
	$CI->lang->line('item_kits_kit'),
	$CI->lang->line('item_kits_name'),
	$CI->lang->line('item_kits_description'),
	$CI->lang->line('items_cost_price'),
	$CI->lang->line('items_unit_price'),
	'&nbsp');
	
	$table.='<thead><tr>';
	foreach($headers as $header)
	{
		$table.="<th>$header</th>";
	}
	$table.='</tr></thead><tbody>';
	$table.=get_item_kits_manage_table_data_rows( $item_kits, $controller );
	$table.='</tbody></table>';

	return $table;
}

/*
Gets the html data rows for the item kits.
*/
function get_item_kits_manage_table_data_rows($item_kits, $controller)
{
	$CI =& get_instance();
	$table_data_rows='';
	
	foreach($item_kits->result() as $item_kit)
	{
		$table_data_rows .= get_item_kit_data_row($item_kit, $controller);
	}
	
	if($item_kits->num_rows()==0)
	{
		$table_data_rows .= "<tr><td colspan='7'><div class='alert alert-dismissible alert-info'>".$CI->lang->line('item_kits_no_item_kits_to_display')."</div></td></tr>";
	}
	
	return $table_data_rows;
}

function get_item_kit_data_row($item_kit, $controller)
{
	$CI =& get_instance();
	$controller_name=strtolower(get_class($CI));

	$table_data_row='<tr>';
	$table_data_row.="<td width='3%'><input type='checkbox' id='item_kit_$item_kit->item_kit_id' value='".$item_kit->item_kit_id."'/></td>";
	$table_data_row.='<td width="15%">'.'KIT '.$item_kit->item_kit_id.'</td>';
	$table_data_row.='<td width="15%">'.$item_kit->name.'</td>';
	$table_data_row.='<td width="20%">'.character_limiter($item_kit->description, 25).'</td>';
	$table_data_row.='<td width="15%">'.to_currency($item_kit->total_cost_price).'</td>';
	$table_data_row.='<td width="15%">'.to_currency($item_kit->total_unit_price).'</td>';
	$table_data_row.='<td width="5%">'.anchor($controller_name."/view/$item_kit->item_kit_id", '<span class="glyphicon glyphicon-edit"></span>', array('class'=>"modal-dlg modal-btn-submit",'title'=>$CI->lang->line($controller_name.'_update'))).'</td>';
	$table_data_row.='</tr>';

	return $table_data_row;
}

?>
