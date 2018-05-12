<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Summary_report.php");

class Summary_payments extends Summary_report
{
	protected function _get_data_columns()
	{
		return array(
			array('payment_type' => $this->lang->line('reports_payment_type')),
			array('report_count' => $this->lang->line('reports_count')),
			array('amount_due' => $this->lang->line('sales_amount_due'), 'sorter' => 'number_sorter'));
	}

	public function getData(array $inputs)
	{
		$this->db->select('sales_payments.payment_type, COUNT(DISTINCT sales_payments.sale_id) AS count, SUM(sales_items.item_unit_price * sales_items.quantity_purchased * (1 - sales_items.discount_percent / 100)) AS payment_amount');
		$this->db->from('sales_payments AS sales_payments');
		$this->db->join('sales AS sales', 'sales.sale_id = sales_payments.sale_id');
		$this->db->join('sales_items AS sales_items', 'sales_items.sale_id = sales_payments.sale_id', 'left');

		$this->_where($inputs);

		$this->db->group_by("payment_type");

		$payments = $this->db->get()->result_array();

		// consider Gift Card as only one type of payment and do not show "Gift Card: 1, Gift Card: 2, etc." in the total
		$gift_card_count = 0;
		$gift_card_amount = 0;
		foreach($payments as $key => $payment)
		{
			if(strstr($payment['payment_type'], $this->lang->line('sales_giftcard')) !== FALSE)
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
