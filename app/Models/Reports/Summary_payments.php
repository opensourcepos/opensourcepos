<?php

namespace App\Models\Reports;

use Config\OSPOS;

class Summary_payments extends Summary_report
{
	protected function _get_data_columns(): array    //TODO: Hungarian notation
	{
		return [
			['trans_group' => lang('Reports.trans_group')],
			['trans_type' => lang('Reports.trans_type')],
			['trans_sales' => lang('Reports.sales')],
			['trans_amount' => lang('Reports.trans_amount')],
			['trans_payments' => lang('Reports.trans_payments')],
			['trans_refunded' => lang('Reports.trans_refunded')],
			['trans_due' => lang('Reports.trans_due')]
		];
	}

	public function getData(array $inputs): array
	{
		$cash_payment = lang('Sales.cash');    //TODO: This is never used.  Should it be?
		$config = config(OSPOS::class)->settings;

		$separator[] = [
			'trans_group' => '<HR>',
			'trans_type' => '',
			'trans_sales' => '',
			'trans_amount' => '',
			'trans_payments' => '',
			'trans_refunded' => '',
			'trans_due' => ''
		];

		$where = '';    //TODO: Duplicated code

		//TODO: this needs to be converted to ternary notation
		if(empty($config['date_or_time_format'])) {
			$where .= 'DATE(sale_time) BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']);
		} else {
			$where .= 'sale_time BETWEEN ' . $this->db->escape(rawurldecode($inputs['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($inputs['end_date']));
		}

		$this->create_summary_payments_temp_tables($where);

		$select = '\'' . lang('Reports.trans_sales') . '\' AS trans_group, ';
		$select .= '(CASE sale_type WHEN ' . SALE_TYPE_POS . ' THEN \'' . lang('Reports.code_pos')
			. '\' WHEN ' . SALE_TYPE_INVOICE . ' THEN \'' . lang('Sales.invoice')
			. '\' WHEN ' . SALE_TYPE_RETURN . ' THEN \'' . lang('Sales.return')
			. '\' END) AS trans_type, ';
		$select .= 'COUNT(sales.sale_id) AS trans_sales, ';
		$select .= 'SUM(sumpay_items.trans_amount) AS trans_amount, ';
		$select .= 'IFNULL(SUM(sumpay_payments.total_payments),0) AS trans_payments, ';
		$select .= 'IFNULL(SUM(sumpay_payments.total_cash_refund),0) AS trans_refunded, ';
		$select .= 'SUM(CASE WHEN sumpay_items.trans_amount - IFNULL(sumpay_payments.total_payments,0) > 0 THEN sumpay_items.trans_amount - IFNULL(sumpay_payments.total_payments,0) ELSE 0 END) as trans_due ';

		$builder = $this->db->table('ospos_sales AS sales');
		$builder->select($select);
		$builder->join('sumpay_items_temp AS sumpay_items', 'sales.sale_id = sumpay_items.sale_id', 'left outer');
		$builder->join('sumpay_payments_temp AS sumpay_payments', 'sales.sale_id = sumpay_payments.sale_id', 'left outer');
		$builder->where('sales.sale_status', COMPLETED);
		$this->_where($inputs, $builder);

		$builder->groupBy('trans_type');

		$sales = $builder->get()->getResultArray();

		// At this point in time refunds are assumed to be cash refunds.
		$total_cash_refund = 0;
		foreach($sales as $key => $sale_summary) {
			if($sale_summary['trans_refunded'] <> 0) {
				$total_cash_refund += $sale_summary['trans_refunded'];
			}
		}

		$select = '\'' . lang('Reports.trans_payments') . '\' AS trans_group, ';
		$select .= 'sales_payments.payment_type as trans_type, ';
		$select .= 'COUNT(sales.sale_id) AS trans_sales, ';
		$select .= 'SUM(payment_amount - cash_refund) AS trans_amount,';
		$select .= 'SUM(payment_amount) AS trans_payments,';
		$select .= 'SUM(cash_refund) AS trans_refunded, ';
		$select .= '0 AS trans_due ';

		$builder = $this->db->table('sales AS sales');
		$builder->select($select);
		$builder->join('sales_payments AS sales_payments', 'sales.sale_id = sales_payments.sale_id', 'left outer');
		$builder->where('sales.sale_status', COMPLETED);
		$this->_where($inputs, $builder);

		$builder->groupBy('sales_payments.payment_type');

		$payments = $builder->get()->getResultArray();

		// consider Gift Card as only one type of payment and do not show "Gift Card: 1, Gift Card: 2, etc." in the total
		$gift_card_count = 0;
		$gift_card_amount = 0;
		foreach($payments as $key => $payment) {
			if(strstr($payment['trans_type'], lang('Sales.giftcard')) !== FALSE) {
				$gift_card_count += $payment['trans_sales'];
				$gift_card_amount += $payment['trans_amount'];

				// Remove the "Gift Card: 1", "Gift Card: 2", etc. payment string
				unset($payments[$key]);
			}
		}

		if($gift_card_count > 0) {
			$payments[] = [
				'trans_group' => lang('Reports.trans_payments'),
				'trans_type' => lang('Sales.giftcard'),
				'trans_sales' => $gift_card_count,
				'trans_amount' => $gift_card_amount,
				'trans_payments' => $gift_card_amount,
				'trans_refunded' => 0,
				'trans_due' => 0
			];
		}

		return array_merge($sales, $separator, $payments);
	}

	protected function create_summary_payments_temp_tables(string $where): void
	{
		$decimals = totals_decimals();

		$trans_amount = 'SUM(CASE WHEN sales_items.discount_type = ' . PERCENT
			. " THEN sales_items.quantity_purchased * sales_items.item_unit_price - ROUND(sales_items.quantity_purchased * sales_items.item_unit_price * sales_items.discount / 100, $decimals) "
			. ' ELSE sales_items.quantity_purchased * (sales_items.item_unit_price - sales_items.discount) END) AS trans_amount';
//TODO: look into converting these to use QueryBuilder
		$this->db->query('CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->prefixTable('sumpay_taxes_temp') .
			' (INDEX(sale_id)) ENGINE=MEMORY
			(
				SELECT sales.sale_id, SUM(sales_taxes.sale_tax_amount) AS total_taxes
				FROM ' . $this->db->prefixTable('sales') . ' AS sales
				LEFT OUTER JOIN ' . $this->db->prefixTable('sales_taxes') . ' AS sales_taxes
					ON sales.sale_id = sales_taxes.sale_id
				WHERE ' . $where . ' AND sales_taxes.tax_type = \'1\'
				GROUP BY sale_id
			)'
		);

		$this->db->query('CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->prefixTable('sumpay_items_temp') .
			' (INDEX(sale_id)) ENGINE=MEMORY
			(
				SELECT sales.sale_id, ' . $trans_amount
			. ' FROM ' . $this->db->prefixTable('sales') . ' AS sales '
			. 'LEFT OUTER JOIN ' . $this->db->prefixTable('sales_items') . ' AS sales_items '
			. 'ON sales.sale_id = sales_items.sale_id '
			. 'LEFT OUTER JOIN ' . $this->db->prefixTable('sumpay_taxes_temp') . ' AS sumpay_taxes '
			. 'ON sales.sale_id = sumpay_taxes.sale_id '
			. 'WHERE ' . $where . ' GROUP BY sale_id
			)'
		);

		$this->db->query('UPDATE ' . $this->db->prefixTable('sumpay_items_temp') . ' AS sumpay_items '
			. 'SET trans_amount = trans_amount + IFNULL((SELECT total_taxes FROM ' . $this->db->prefixTable('sumpay_taxes_temp')
			. ' AS sumpay_taxes WHERE sumpay_items.sale_id = sumpay_taxes.sale_id),0)');

		$this->db->query('CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->prefixTable('sumpay_payments_temp') .
			' (INDEX(sale_id)) ENGINE=MEMORY
			(
				SELECT sales.sale_id, COUNT(sales.sale_id) AS number_payments,
				SUM(CASE WHEN sales_payments.cash_adjustment = 0 THEN sales_payments.payment_amount ELSE 0 END) AS total_payments,
				SUM(CASE WHEN sales_payments.cash_adjustment = 1 THEN sales_payments.payment_amount ELSE 0 END) AS total_cash_adjustment,
				SUM(sales_payments.cash_refund) AS total_cash_refund
				FROM ' . $this->db->prefixTable('sales') . ' AS sales
				LEFT OUTER JOIN ' . $this->db->prefixTable('sales_payments') . ' AS sales_payments
					ON sales.sale_id = sales_payments.sale_id
				WHERE ' . $where . '
				GROUP BY sale_id
			)'
		);
	}
}
