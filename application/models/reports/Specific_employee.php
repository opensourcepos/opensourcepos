<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Report.php");

class Specific_employee extends Report
{
	public function create(array $inputs)
	{
		//Create our temp tables to work with the data in our report
		$this->Sale->create_temp_table($inputs);
	}

	public function getDataColumns()
	{
		return array(
			'summary' => array(
				array('id' => $this->lang->line('reports_sale_id')),
				array('sale_date' => $this->lang->line('reports_date')),
				array('quantity' => $this->lang->line('reports_quantity')),
				array('customer_name' => $this->lang->line('reports_sold_to')),
				array('subtotal' => $this->lang->line('reports_subtotal'), 'sorter' => 'number_sorter'),
				array('tax' => $this->lang->line('reports_tax'), 'sorter' => 'number_sorter'),
				array('total' => $this->lang->line('reports_total'), 'sorter' => 'number_sorter'),
				array('cost' => $this->lang->line('reports_cost'), 'sorter' => 'number_sorter'),
				array('profit' => $this->lang->line('reports_profit'), 'sorter' => 'number_sorter'),
				array('payment_type' => $this->lang->line('reports_payment_type')),
				array('comment' => $this->lang->line('reports_comments'))),
			'details' => array(
				$this->lang->line('reports_name'),
				$this->lang->line('reports_category'),
				$this->lang->line('reports_serial_number'),
				$this->lang->line('reports_description'),
				$this->lang->line('reports_quantity'),
				$this->lang->line('reports_subtotal'),
				$this->lang->line('reports_tax'),
				$this->lang->line('reports_total'),
				$this->lang->line('reports_cost'),
				$this->lang->line('reports_profit'),
				$this->lang->line('reports_discount')),
			'details_rewards' => array(
				$this->lang->line('reports_used'),
				$this->lang->line('reports_earned'))
		);
	}

	public function getData(array $inputs)
	{
		$this->db->select('sale_id,
			MAX(sale_date) AS sale_date,
			SUM(quantity_purchased) AS items_purchased,
			MAX(customer_name) AS customer_name,
			SUM(subtotal) AS subtotal,
			SUM(tax) AS tax,
			SUM(total) AS total,
			SUM(cost) AS cost,
			SUM(profit) AS profit,
			MAX(payment_type) AS payment_type,
			MAX(comment) AS comment');
		$this->db->from('sales_items_temp');
		$this->db->where('employee_id', $inputs['employee_id']);

		if($inputs['sale_type'] == 'sales')
		{
			$this->db->where('sale_status = ' . COMPLETED . ' and quantity_purchased > 0');
		}
		elseif($inputs['sale_type'] == 'all')
		{
			$this->db->where('sale_status = ' . COMPLETED);
		}
		elseif($inputs['sale_type'] == 'quotes')
		{
			$this->db->where('sale_status = ' . SUSPENDED . ' and quote_number IS NOT NULL');
		}
		elseif($inputs['sale_type'] == 'returns')
		{
			$this->db->where('sale_status = ' . COMPLETED . ' and quantity_purchased < 0');
		}

		$this->db->group_by('sale_id');
		$this->db->order_by('MAX(sale_date)');

		$data = array();
		$data['summary'] = $this->db->get()->result_array();
		$data['details'] = array();
		$data['rewards'] = array();

		foreach($data['summary'] as $key=>$value)
		{
			$this->db->select('name, category, serialnumber, description, quantity_purchased, subtotal, tax, total, cost, profit, discount_percent');
			$this->db->from('sales_items_temp');
			$this->db->where('sale_id', $value['sale_id']);
			$data['details'][$key] = $this->db->get()->result_array();
			$this->db->select('used, earned');
			$this->db->from('sales_reward_points');
			$this->db->where('sale_id', $value['sale_id']);
			$data['rewards'][$key] = $this->db->get()->result_array();
		}

		return $data;
	}

	public function getSummaryData(array $inputs)
	{
		$this->db->select('SUM(subtotal) AS subtotal, SUM(tax) AS tax, SUM(total) AS total, SUM(cost) AS cost, SUM(profit) AS profit');
		$this->db->from('sales_items_temp');
		$this->db->where('employee_id', $inputs['employee_id']);

		if($inputs['sale_type'] == 'sales')
		{
			$this->db->where('sale_status = ' . COMPLETED . ' and quantity_purchased > 0');
		}
		elseif($inputs['sale_type'] == 'all')
		{
			$this->db->where('sale_status = ' . COMPLETED);
		}
		elseif($inputs['sale_type'] == 'quotes')
		{
			$this->db->where('sale_status = ' . SUSPENDED . ' and quote_number IS NOT NULL');
		}
		elseif($inputs['sale_type'] == 'returns')
		{
			$this->db->where('sale_status = ' . COMPLETED . ' and quantity_purchased < 0');
		}

		return $this->db->get()->row_array();
	}
}
?>
