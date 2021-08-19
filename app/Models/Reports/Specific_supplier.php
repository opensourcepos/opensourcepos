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
			array('id' => lang('Reports.sale_id')),
			array('type_code' => lang('Reports.code_type')),
			array('sale_date' => lang('Reports.date'), 'sortable' => FALSE),
			array('name' => lang('Reports.name')),
			array('category' => lang('Reports.category')),
			array('item_number' => lang('Reports.item_number')),
			array('quantity' => lang('Reports.quantity')),
			array('subtotal' => lang('Reports.subtotal'), 'sorter' => 'number_sorter'),
			array('tax' => lang('Reports.tax'), 'sorter' => 'number_sorter'),
			array('total' => lang('Reports.total'), 'sorter' => 'number_sorter'),
			array('cost' => lang('Reports.cost'), 'sorter' => 'number_sorter'),
			array('profit' => lang('Reports.profit'), 'sorter' => 'number_sorter'),
			array('discount' => lang('Reports.discount'))
		);
	}

	public function getData(array $inputs)
	{
		$builder->select('sale_id,
			MAX(CASE
			WHEN sale_type = ' . SALE_TYPE_POS . ' && sale_status = ' . COMPLETED . ' THEN \'' . lang('Reports.code_pos') . '\'
			WHEN sale_type = ' . SALE_TYPE_INVOICE . ' && sale_status = ' . COMPLETED . ' THEN \'' . lang('Reports.code_invoice') . '\'
			WHEN sale_type = ' . SALE_TYPE_WORK_ORDER . ' && sale_status = ' . SUSPENDED . ' THEN \'' . lang('Reports.code_work_order') . '\'
			WHEN sale_type = ' . SALE_TYPE_QUOTE . ' && sale_status = ' . SUSPENDED . ' THEN \'' . lang('Reports.code_quote') . '\'
			WHEN sale_type = ' . SALE_TYPE_RETURN . ' && sale_status = ' . COMPLETED . ' THEN \'' . lang('Reports.code_return') . '\'
			WHEN sale_status = ' . CANCELED . ' THEN \'' . lang('Reports.code_canceled') . '\'
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
		$builder = $this->db->table('sales_items_temp');

		$builder->where('supplier_id', $inputs['supplier_id']);

		if($inputs['sale_type'] == 'complete')
		{
			$builder->where('sale_status', COMPLETED);
			$builder->groupStart();
			$builder->where('sale_type', SALE_TYPE_POS);
			$this->db->or_where('sale_type', SALE_TYPE_INVOICE);
			$this->db->or_where('sale_type', SALE_TYPE_RETURN);
			$builder->groupEnd();
		}
		elseif($inputs['sale_type'] == 'sales')
		{
			$builder->where('sale_status', COMPLETED);
			$builder->groupStart();
			$builder->where('sale_type', SALE_TYPE_POS);
			$this->db->or_where('sale_type', SALE_TYPE_INVOICE);
			$builder->groupEnd();
		}
		elseif($inputs['sale_type'] == 'quotes')
		{
			$builder->where('sale_status', SUSPENDED);
			$builder->where('sale_type', SALE_TYPE_QUOTE);
		}
		elseif($inputs['sale_type'] == 'work_orders')
		{
			$builder->where('sale_status', SUSPENDED);
			$builder->where('sale_type', SALE_TYPE_WORK_ORDER);
		}
		elseif($inputs['sale_type'] == 'canceled')
		{
			$builder->where('sale_status', CANCELED);
		}
		elseif($inputs['sale_type'] == 'returns')
		{
			$builder->where('sale_status', COMPLETED);
			$builder->where('sale_type', SALE_TYPE_RETURN);
		}

		$this->db->group_by('item_id');
		$builder->orderBy('sale_id');

		return $builder->get()->getResultArray();
	}

	public function getSummaryData(array $inputs)
	{
		$builder->select('SUM(subtotal) AS subtotal, SUM(tax) AS tax, SUM(total) AS total, SUM(cost) AS cost, SUM(profit) AS profit');
		$builder = $this->db->table('sales_items_temp');

		$builder->where('supplier_id', $inputs['supplier_id']);

		if($inputs['sale_type'] == 'complete')
		{
			$builder->where('sale_status', COMPLETED);
			$builder->groupStart();
			$builder->where('sale_type', SALE_TYPE_POS);
			$this->db->or_where('sale_type', SALE_TYPE_INVOICE);
			$this->db->or_where('sale_type', SALE_TYPE_RETURN);
			$builder->groupEnd();
		}
		elseif($inputs['sale_type'] == 'sales')
		{
			$builder->where('sale_status', COMPLETED);
			$builder->groupStart();
			$builder->where('sale_type', SALE_TYPE_POS);
			$this->db->or_where('sale_type', SALE_TYPE_INVOICE);
			$builder->groupEnd();
		}
		elseif($inputs['sale_type'] == 'quotes')
		{
			$builder->where('sale_status', SUSPENDED);
			$builder->where('sale_type', SALE_TYPE_QUOTE);
		}
		elseif($inputs['sale_type'] == 'work_orders')
		{
			$builder->where('sale_status', SUSPENDED);
			$builder->where('sale_type', SALE_TYPE_WORK_ORDER);
		}
		elseif($inputs['sale_type'] == 'canceled')
		{
			$builder->where('sale_status', CANCELED);
		}
		elseif($inputs['sale_type'] == 'returns')
		{
			$builder->where('sale_status', COMPLETED);
			$builder->where('sale_type', SALE_TYPE_RETURN);
		}

		return $builder->get()->row_array();
	}
}
?>
