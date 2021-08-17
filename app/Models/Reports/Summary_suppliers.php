<?php

namespace App\Models\Reports;

use CodeIgniter\Model;

require_once("Summary_report.php");

class Summary_suppliers extends Summary_report
{
	protected function _get_data_columns()
	{
		return array(
			array('supplier_name' => lang('Reports.supplier')),
			array('quantity' => lang('Reports.quantity')),
			array('subtotal' => lang('Reports.subtotal'), 'sorter' => 'number_sorter'),
			array('tax' => lang('Reports.tax'), 'sorter' => 'number_sorter'),
			array('total' => lang('Reports.total'), 'sorter' => 'number_sorter'),
			array('cost' => lang('Reports.cost'), 'sorter' => 'number_sorter'),
			array('profit' => lang('Reports.profit'), 'sorter' => 'number_sorter'));
	}

	protected function _select(array $inputs)
	{
		parent::_select($inputs);

		$this->db->select('
				MAX(CONCAT(supplier_c.company_name, " (", supplier_p.first_name, " ", supplier_p.last_name, ")")) AS supplier,
				SUM(sales_items.quantity_purchased) AS quantity_purchased
		');
	}

	protected function _from()
	{
		parent::_from();

		$this->db->join('items AS items', 'sales_items.item_id = items.item_id');
		$this->db->join('suppliers AS supplier_c', 'items.supplier_id = supplier_c.person_id ');
		$this->db->join('people AS supplier_p', 'items.supplier_id = supplier_p.person_id');
	}

	protected function _group_order()
	{
		$this->db->group_by('items.supplier_id');
		$builder->orderBy('MAX(CONCAT(supplier_c.company_name, " (", supplier_p.first_name, " ", supplier_p.last_name, ")"))');
	}
}
?>
