<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Summary_report.php");

class Summary_payments extends Summary_report
{
	function __construct()
	{
		parent::__construct();
	}
	
	protected function _get_data_columns()
	{
		return array(
			array('payment_type' => $this->lang->line('reports_payment_type')),
			array('report_count' => $this->lang->line('reports_count')),
			array('amount_tendered' => $this->lang->line('sales_amount_tendered'), 'sorter' => 'number_sorter'));
	}

	public function getData(array $inputs)
	{
		$w = 'WHERE DATE(salesx.sale_time) BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']) . ' ';

		if (!empty($inputs['sale_type']) && $inputs['sale_type'] != 'all')
		{
			$w .= ' and salesx.sales_type = ' . $this->db->escape($inputs['sale_type']) . ' ';
		}

		if (!empty($inputs['location']) && $inputs['location'] != 'all')
		{
			$w .= ' and salesx.location = ' . $this->db->escape($inputs['location']) . ' ';
		}

		$sql = 'select sales_payments.payment_type, count(*) AS count, SUM(sales_payments.payment_amount) AS payment_amount 
		    from ' . $this->db->dbprefix('sales_payments') . ' as sales_payments
		    join (select sales_items.sale_id, case when sum(sales_items.quantity_purchased) > 0 then \'sales\' else \'returns\' end as sales_type, sales_items.item_location as location, max(sales.sale_time) as sale_time 
		    from ' . $this->db->dbprefix('sales_items') . ' as sales_items 
		    join ' . $this->db->dbprefix('sales') . ' as sales 
		    group by sales_items.sale_id, sales_items.item_location) as salesx on sales_payments.sale_id = salesx.sale_id '
		. $w
		. ' group by payment_type';

		$payments = $this->db->query($sql)->result_array();

		// consider Gift Card as only one type of payment and do not show "Gift Card: 1, Gift Card: 2, etc." in the total
		$gift_card_count = 0;
		$gift_card_amount = 0;
		foreach($payments as $key=>$payment)
		{
			if( strstr($payment['payment_type'], $this->lang->line('sales_giftcard')) != FALSE )
			{
				$gift_card_count  += $payment['count'];
				$gift_card_amount += $payment['payment_amount'];

				// remove the "Gift Card: 1", "Gift Card: 2", etc. payment string
				unset($payments[$key]);
			}
		}

		if($gift_card_count > 0)
		{
			$payments[] = array('payment_type' => $this->lang->line('sales_giftcard'), 'count' => $gift_card_count, 'payment_amount' => $gift_card_amount);
		}

		return $payments;
	}
}
?>