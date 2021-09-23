<?php

namespace App\Models\Reports;

use CodeIgniter\Model;



class Summary_suppliers extends Summary_report
{
	protected function _get_data_columns()
	{
		return [
			['supplier_name' => lang('Reports.supplier')),
			['quantity' => lang('Reports.quantity')),
			['subtotal' => lang('Reports.subtotal'), 'sorter' => 'number_sorter'),
			['tax' => lang('Reports.tax'), 'sorter' => 'number_sorter'),
			['total' => lang('Reports.total'), 'sorter' => 'number_sorter'),
			['cost' => lang('Reports.cost'), 'sorter' => 'number_sorter'),
			['profit' => lang('Reports.profit'), 'sorter' => 'number_sorter'));
	}

	protected function _select(array $inputs)
	{
		parent::_select($inputs);

		$builder->select('
				MAX(CONCAT(supplier_c.company_name, " (", supplier_p.first_name, " ", supplier_p.last_name, ")")) AS supplier,
				SUM(sales_items.quantity_purchased) AS quantity_purchased
		');
	}

	protected function _from()
	{
		parent::_from();

		$builder->join('items AS items', 'sales_items.item_id = items.item_id');
		$builder->join('suppliers AS supplier_c', 'items.supplier_id = supplier_c.person_id ');
		$builder->join('people AS supplier_p', 'items.supplier_id = supplier_p.person_id');
	}

	protected function _group_order()
	{
		$builder->groupBy('items.supplier_id');
		$builder->orderBy('MAX(CONCAT(supplier_c.company_name, " (", supplier_p.first_name, " ", supplier_p.last_name, ")"))');
	}
}
?>
