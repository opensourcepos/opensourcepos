<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once("Report.php");

class Detailed_sales extends Report
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
				array('type_code' => $this->lang->line('reports_code_type')),
				array('sale_time' => $this->lang->line('reports_date'), 'sortable' => FALSE),
				array('quantity' => $this->lang->line('reports_quantity')),
				array('employee_name' => $this->lang->line('reports_sold_by')),
				array('customer_name' => $this->lang->line('reports_sold_to')),
				array('subtotal' => $this->lang->line('reports_subtotal'), 'sorter' => 'number_sorter'),
				array('tax' => $this->lang->line('reports_tax'), 'sorter' => 'number_sorter'),
				array('total' => $this->lang->line('reports_total'), 'sorter' => 'number_sorter'),
				array('cost' => $this->lang->line('reports_cost'), 'sorter' => 'number_sorter'),
				array('profit' => $this->lang->line('reports_profit'), 'sorter' => 'number_sorter'),
				array('payment_type' => $this->lang->line('reports_payment_type'), 'sortable' => FALSE),
				array('comment' => $this->lang->line('reports_comments'))),
			'details' => array(
				$this->lang->line('reports_name'),
				$this->lang->line('reports_category'),
				$this->lang->line('reports_item_number'),
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

	public function getDataBySaleId($sale_id)
	{
		$this->db->select('sale_id,
			sale_time as sale_time,
			SUM(quantity_purchased) AS items_purchased,
			MAX(employee_name) AS employee_name,
			MAX(customer_name) AS customer_name,
			SUM(subtotal) AS subtotal,
			SUM(tax) AS tax,
			SUM(total) AS total,
			SUM(cost) AS cost,
			SUM(profit) AS profit,
			MAX(payment_type) AS payment_type,
			MAX(sale_status) AS sale_status,
			comment');
		$this->db->from('sales_items_temp');
		$this->db->where('sale_id', $sale_id);

		return $this->db->get()->row_array();
	}

	public function getData(array $inputs)
	{
		$this->db->select('sale_id, 
			MAX(CASE
			WHEN sale_type = ' . SALE_TYPE_POS . ' && sale_status = ' . COMPLETED . ' THEN \'' . $this->lang->line('reports_code_pos') . '\'			
			WHEN sale_type = ' . SALE_TYPE_INVOICE . ' && sale_status = ' . COMPLETED . ' THEN \'' . $this->lang->line('reports_code_invoice') . '\'
			WHEN sale_type = ' . SALE_TYPE_WORK_ORDER . ' && sale_status = ' . SUSPENDED . ' THEN \'' . $this->lang->line('reports_code_work_order') . '\'
			WHEN sale_type = ' . SALE_TYPE_QUOTE . ' && sale_status = ' . SUSPENDED . ' THEN \'' . $this->lang->line('reports_code_quote') . '\'
			WHEN sale_type = ' . SALE_TYPE_RETURN . ' && sale_status = ' . COMPLETED . ' THEN \'' . $this->lang->line('reports_code_return') . '\'
			WHEN sale_status = ' . CANCELED . ' THEN \'' . $this->lang->line('reports_code_canceled') . '\'
			ELSE \'\'
			END) AS type_code,
			MAX(sale_status) as sale_status,
			MAX(sale_time) AS sale_time,
			SUM(quantity_purchased) AS items_purchased,
			MAX(employee_name) AS employee_name,
			MAX(customer_name) AS customer_name,
			SUM(subtotal) AS subtotal,
			SUM(tax) AS tax,
			SUM(total) AS total,
			SUM(cost) AS cost,
			SUM(profit) AS profit,
			MAX(payment_type) AS payment_type,
			MAX(comment) AS comment');
		$this->db->from('sales_items_temp');

		if($inputs['location_id'] != 'all')
		{
			$this->db->where('item_location', $inputs['location_id']);
		}

		if($inputs['sale_type'] == 'complete')
		{
			$this->db->where('sale_status', COMPLETED);
			$this->db->group_start();
			$this->db->where('sale_type', SALE_TYPE_POS);
			$this->db->or_where('sale_type', SALE_TYPE_INVOICE);
			$this->db->or_where('sale_type', SALE_TYPE_RETURN);
			$this->db->group_end();
		}
		elseif($inputs['sale_type'] == 'sales')
		{
			$this->db->where('sale_status', COMPLETED);
			$this->db->group_start();
			$this->db->where('sale_type', SALE_TYPE_POS);
			$this->db->or_where('sale_type', SALE_TYPE_INVOICE);
			$this->db->group_end();
		}
		elseif($inputs['sale_type'] == 'quotes')
		{
			$this->db->where('sale_status', SUSPENDED);
			$this->db->where('sale_type', SALE_TYPE_QUOTE);
		}
		elseif($inputs['sale_type'] == 'work_orders')
		{
			$this->db->where('sale_status', SUSPENDED);
			$this->db->where('sale_type', SALE_TYPE_WORK_ORDER);
		}
		elseif($inputs['sale_type'] == 'canceled')
		{
			$this->db->where('sale_status', CANCELED);
		}
		elseif($inputs['sale_type'] == 'returns')
		{
			$this->db->where('sale_status', COMPLETED);
			$this->db->where('sale_type', SALE_TYPE_RETURN);
		}

		$this->db->group_by('sale_id');
		$this->db->order_by('MAX(sale_time)');

		$data = array();
		$data['summary'] = $this->db->get()->result_array();
		$data['details'] = array();
		$data['rewards'] = array();

		foreach($data['summary'] as $key=>$value)
		{
			$this->db->select('
				MAX(name) AS name, 
				MAX(category) AS category, 
				MAX(quantity_purchased) AS quantity_purchased, 
				MAX(item_location) AS item_location, 
				MAX(item_number) AS item_number, 
				MAX(description) AS description, 
				MAX(subtotal) AS subtotal, 
				MAX(tax) AS tax, 
				MAX(total) AS total, 
				MAX(cost) AS cost, 
				MAX(profit) AS profit, 
				MAX(discount) AS discount, 
				MAX(discount_type) AS discount_type, 
				MAX(sale_status) AS sale_status');
			$this->db->from('sales_items_temp');
			if(count($inputs['definition_ids']) > 0)
			{
				$format = $this->db->escape(dateformat_mysql());
				$this->db->select('GROUP_CONCAT(DISTINCT CONCAT_WS(\'_\', definition_id, attribute_value) ORDER BY definition_id SEPARATOR \'|\') AS attribute_values');
				$this->db->select("GROUP_CONCAT(DISTINCT CONCAT_WS('_', definition_id, DATE_FORMAT(attribute_date, $format)) SEPARATOR '|') AS attribute_dtvalues");
				$this->db->select('GROUP_CONCAT(DISTINCT CONCAT_WS(\'_\', definition_id, attribute_decimal) SEPARATOR \'|\') AS attribute_dvalues');
				$this->db->join('attribute_links', 'attribute_links.item_id = sales_items_temp.item_id AND attribute_links.sale_id = sales_items_temp.sale_id AND definition_id IN (' . implode(',', $inputs['definition_ids']) . ')', 'left');
				$this->db->join('attribute_values', 'attribute_values.attribute_id = attribute_links.attribute_id', 'left');
			}
			$this->db->group_by('sales_items_temp.sale_id, sales_items_temp.item_id, sales_items_temp.line');
			$this->db->where('sales_items_temp.sale_id', $value['sale_id']);
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

		if($inputs['location_id'] != 'all')
		{
			$this->db->where('item_location', $inputs['location_id']);
		}

		if($inputs['sale_type'] == 'complete')
		{
			$this->db->where('sale_status', COMPLETED);
			$this->db->group_start();
			$this->db->where('sale_type', SALE_TYPE_POS);
			$this->db->or_where('sale_type', SALE_TYPE_INVOICE);
			$this->db->or_where('sale_type', SALE_TYPE_RETURN);
			$this->db->group_end();
		}
		elseif($inputs['sale_type'] == 'sales')
		{
			$this->db->where('sale_status', COMPLETED);
			$this->db->group_start();
			$this->db->where('sale_type', SALE_TYPE_POS);
			$this->db->or_where('sale_type', SALE_TYPE_INVOICE);
			$this->db->group_end();
		}
		elseif($inputs['sale_type'] == 'quotes')
		{
			$this->db->where('sale_status', SUSPENDED);
			$this->db->where('sale_type', SALE_TYPE_QUOTE);
		}
		elseif($inputs['sale_type'] == 'work_orders')
		{
			$this->db->where('sale_status', SUSPENDED);
			$this->db->where('sale_type', SALE_TYPE_WORK_ORDER);
		}
		elseif($inputs['sale_type'] == 'canceled')
		{
			$this->db->where('sale_status', CANCELED);
		}
		elseif($inputs['sale_type'] == 'returns')
		{
			$this->db->where('sale_status', COMPLETED);
			$this->db->where('sale_type', SALE_TYPE_RETURN);
		}

		return $this->db->get()->row_array();
	}
}
?>
