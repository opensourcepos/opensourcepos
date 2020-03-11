<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Summary_report.php");

class Summary_payments extends Summary_report
{
	protected function _get_data_columns()
	{
		return array(
			array('trans_group' => $this->lang->line('reports_trans_group')),
			array('trans_type' => $this->lang->line('reports_trans_type')),
			array('trans_count' => $this->lang->line('reports_count')),
			array('trans_amount' => $this->lang->line('reports_trans_amount')),
			array('trans_payments' => $this->lang->line('reports_trans_payments')),
			array('trans_refunded' => $this->lang->line('reports_trans_refunded')),
			array('trans_due' => $this->lang->line('reports_trans_due')));
	}

	public function getData(array $inputs)
	{
		$cash_payment = $this->lang->line('sales_cash');

		$separator[] = array(
			'trans_group' => '<HR>',
			'trans_type' => '',
			'trans_count' => '',
			'trans_amount' => '',
			'trans_payments' => '',
			'trans_refunded' => '',
			'trans_due' => ''
		);

		$where = '';

		if(empty($this->config->item('date_or_time_format')))
		{
			$where .= 'DATE(sale_time) BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']);
		}
		else
		{
			$where .= 'sale_time BETWEEN ' . $this->db->escape(rawurldecode($inputs['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($inputs['end_date']));
		}

		$this->create_summary_payments_temp_tables($where);

		$select = '\'' . $this->lang->line('reports_trans_sales') . '\' AS trans_group, ';
		$select .= '(CASE sale_type WHEN ' . SALE_TYPE_POS . ' THEN \'' . $this->lang->line('reports_code_pos')
			. '\' WHEN ' . SALE_TYPE_INVOICE . ' THEN \'' . $this->lang->line('sales_invoice')
			. '\' WHEN ' . SALE_TYPE_RETURN . ' THEN \'' . $this->lang->line('sales_return')
			. '\' END) AS trans_type, ';
		$select .= 'COUNT(sales.sale_id) AS trans_count, ';
		$select .= 'SUM(sumpay_items.trans_amount) AS trans_amount, ';
		$select .= 'IFNULL(SUM(sumpay_payments.total_payments),0) AS trans_payments, ';
		$select .= 'IFNULL(SUM(sumpay_payments.total_cash_refund),0) AS trans_refunded, ';
		$select .= 'SUM(CASE WHEN sumpay_items.trans_amount - IFNULL(sumpay_payments.total_payments,0) > 0 THEN sumpay_items.trans_amount - IFNULL(sumpay_payments.total_payments,0) ELSE 0 END) as trans_due ';

		$this->db->select($select);
		$this->db->from('ospos_sales AS sales');
		$this->db->join('sumpay_items_temp AS sumpay_items', 'sales.sale_id = sumpay_items.sale_id', 'left outer');
		$this->db->join('sumpay_payments_temp AS sumpay_payments', 'sales.sale_id = sumpay_payments.sale_id', 'left outer');
		$this->db->where('sales.sale_status', COMPLETED);
		$this->_where($inputs);

		$this->db->group_by('trans_type');

		$sales = $this->db->get()->result_array();

		// At this point in time refunds are assumed to be cash refunds.
		$total_cash_refund = 0;
		foreach($sales as $key => $sale_summary)
		{
			if($sale_summary['trans_refunded'] <> 0)
			{
				$total_cash_refund += $sale_summary['trans_refunded'];
			}
		}

		$select = '\'' . $this->lang->line('reports_trans_payments') . '\' AS trans_group, ';
		$select .= 'sales_payments.payment_type as trans_type, ';
		$select .= 'COUNT(sales.sale_id) AS trans_count, ';
		$select .= 'SUM(payment_amount - cash_refund) AS trans_amount,';
		$select .= 'SUM(payment_amount) AS trans_payments,';
		$select .= 'SUM(cash_refund) AS trans_refunded, ';
		$select .= '0 AS trans_due ';

		$this->db->select($select);
		$this->db->from('sales AS sales');
		$this->db->join('sales_payments AS sales_payments', 'sales.sale_id = sales_payments.sale_id', 'left outer');
		$this->db->where('sales.sale_status', COMPLETED);
		$this->_where($inputs);

		$this->db->group_by('sales_payments.payment_type');

		$payments = $this->db->get()->result_array();

		// consider Gift Card as only one type of payment and do not show "Gift Card: 1, Gift Card: 2, etc." in the total
		$gift_card_count = 0;
		$gift_card_amount = 0;
		foreach($payments as $key => $payment)
		{
			if(strstr($payment['trans_type'], $this->lang->line('sales_giftcard')) !== FALSE)
			{
				$gift_card_count  += $payment['trans_count'];
				$gift_card_amount += $payment['trans_amount'];

				// Remove the "Gift Card: 1", "Gift Card: 2", etc. payment string
				unset($payments[$key]);
			}
		}

		if($gift_card_count > 0)
		{
			$payments[] = array('trans_group' => $this->lang->line('reports_trans_payments'), 'trans_type' => $this->lang->line('sales_giftcard'), 'trans_count' => $gift_card_count,
				'trans_amount' => $gift_card_amount, 'trans_payments' => $gift_card_amount, 'trans_refunded' => 0, 'trans_due' => 0);
		}

		return array_merge($sales, $separator, $payments);
	}

	protected function create_summary_payments_temp_tables($where)
	{
		$decimals = totals_decimals();

		$trans_amount = 'ROUND(SUM(CASE WHEN sales_items.discount_type = ' . PERCENT
			. ' THEN sales_items.item_unit_price * sales_items.quantity_purchased * (1 - sales_items.discount / 100) '
			. 'ELSE sales_items.item_unit_price * sales_items.quantity_purchased - sales_items.discount END), ' . $decimals . ') AS trans_amount';

		$this->db->query('CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->dbprefix('sumpay_taxes_temp') .
			' (INDEX(sale_id)) ENGINE=MEMORY
			(
				SELECT sales.sale_id, SUM(sales_taxes.sale_tax_amount) AS total_taxes
				FROM ' . $this->db->dbprefix('sales') . ' AS sales
				LEFT OUTER JOIN ' . $this->db->dbprefix('sales_taxes') . ' AS sales_taxes
					ON sales.sale_id = sales_taxes.sale_id
				WHERE ' . $where . ' AND sales_taxes.tax_type = \'1\'
				GROUP BY sale_id
			)'
		);

		$this->db->query('CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->dbprefix('sumpay_items_temp') .
			' (INDEX(sale_id)) ENGINE=MEMORY
			(
				SELECT sales.sale_id, '. $trans_amount
			. ' FROM ' . $this->db->dbprefix('sales') . ' AS sales '
			. 'LEFT OUTER JOIN ' . $this->db->dbprefix('sales_items') . ' AS sales_items '
			. 'ON sales.sale_id = sales_items.sale_id '
			. 'LEFT OUTER JOIN ' . $this->db->dbprefix('sumpay_taxes_temp') . ' AS sumpay_taxes '
			. 'ON sales.sale_id = sumpay_taxes.sale_id '
			. 'WHERE ' . $where . ' GROUP BY sale_id
			)'
		);

		$this->db->query('UPDATE ' . $this->db->dbprefix('sumpay_items_temp') . ' AS sumpay_items '
			. 'SET trans_amount = trans_amount + IFNULL((SELECT total_taxes FROM ' . $this->db->dbprefix('sumpay_taxes_temp')
			. ' AS sumpay_taxes WHERE sumpay_items.sale_id = sumpay_taxes.sale_id),0)');

		$this->db->query('CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->dbprefix('sumpay_payments_temp') .
			' (INDEX(sale_id)) ENGINE=MEMORY
			(
				SELECT sales.sale_id, COUNT(sales.sale_id) AS number_payments, SUM(sales_payments.payment_amount) AS total_payments,
				SUM(sales_payments.cash_refund) AS total_cash_refund
				FROM ' . $this->db->dbprefix('sales') . ' AS sales
				LEFT OUTER JOIN ' . $this->db->dbprefix('sales_payments') . ' AS sales_payments
					ON sales.sale_id = sales_payments.sale_id
				WHERE ' . $where . '
				GROUP BY sale_id
			)'
		);
	}
}
?>
