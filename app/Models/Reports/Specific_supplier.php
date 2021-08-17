<?php

namespace App\Models\Reports;

use CodeIgniter\Model;

require_once("Report.php");

class Specific_supplier extends Report
{
	public function create(array $inputs)
	{
		//Create our temp tables to work with the data in our report
		$this->Sale->create_temp_table($inputs);
	}

	public function getDataColumns()
	{
		return array(
			array('id' => lang('reports_sale_id')),
			array('type_code' => lang('reports_code_type')),
			array('sale_date' => lang('reports_date'), 'sortable' => FALSE),
			array('name' => lang('reports_name')),
			array('category' => lang('reports_category')),
			array('item_number' => lang('reports_item_number')),
			array('quantity' => lang('reports_quantity')),
			array('subtotal' => lang('reports_subtotal'), 'sorter' => 'number_sorter'),
			array('tax' => lang('reports_tax'), 'sorter' => 'number_sorter'),
			array('total' => lang('reports_total'), 'sorter' => 'number_sorter'),
			array('cost' => lang('reports_cost'), 'sorter' => 'number_sorter'),
			array('profit' => lang('reports_profit'), 'sorter' => 'number_sorter'),
			array('discount' => lang('reports_discount'))
		);
	}

	public function getData(array $inputs)
	{
		$this->db->select('sale_id,
			MAX(CASE
			WHEN sale_type = ' . SALE_TYPE_POS . ' && sale_status = ' . COMPLETED . ' THEN \'' . lang('reports_code_pos') . '\'
			WHEN sale_type = ' . SALE_TYPE_INVOICE . ' && sale_status = ' . COMPLETED . ' THEN \'' . lang('reports_code_invoice') . '\'
			WHEN sale_type = ' . SALE_TYPE_WORK_ORDER . ' && sale_status = ' . SUSPENDED . ' THEN \'' . lang('reports_code_work_order') . '\'
			WHEN sale_type = ' . SALE_TYPE_QUOTE . ' && sale_status = ' . SUSPENDED . ' THEN \'' . lang('reports_code_quote') . '\'
			WHEN sale_type = ' . SALE_TYPE_RETURN . ' && sale_status = ' . COMPLETED . ' THEN \'' . lang('reports_code_return') . '\'
			WHEN sale_status = ' . CANCELED . ' THEN \'' . lang('reports_code_canceled') . '\'
			ELSE \'\'
			END) AS type_code,
			MAX(sale_status) as sale_status,
			MAX(sale_date) AS sale_date,
			MAX(name) AS name,
			MAX(category) AS category,
			MAX(item_number) AS item_number,
			SUM(quantity_purchased) AS items_purchased,
			SUM(subtotal) AS subtotal,
			SUM(tax) AS tax,
			SUM(total) AS total,
			SUM(cost) AS cost,
			SUM(profit) AS profit,
			MAX(discount_type) AS discount_type,
			MAX(discount) AS discount');
		$this->db->from('sales_items_temp');

		$this->db->where('supplier_id', $inputs['supplier_id']);

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

		$this->db->group_by('item_id');
		$this->db->order_by('sale_id');

		return $this->db->get()->result_array();
	}

	public function getSummaryData(array $inputs)
	{
		$this->db->select('SUM(subtotal) AS subtotal, SUM(tax) AS tax, SUM(total) AS total, SUM(cost) AS cost, SUM(profit) AS profit');
		$this->db->from('sales_items_temp');

		$this->db->where('supplier_id', $inputs['supplier_id']);

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
