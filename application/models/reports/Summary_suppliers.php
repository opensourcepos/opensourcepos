<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Summary_report.php");

class Summary_suppliers extends Summary_report
{
	function __construct()
	{
		parent::__construct();
	}

	protected function _get_data_columns()
	{
		return array($this->lang->line('reports_supplier'), $this->lang->line('reports_quantity'), $this->lang->line('reports_subtotal'), $this->lang->line('reports_tax'), $this->lang->line('reports_total'), $this->lang->line('reports_cost'), $this->lang->line('reports_profit'));
	}

	protected function _select(array $inputs)
	{
		parent::_select($inputs);

		$this->db->select('
				CONCAT(supplier_c.company_name, " (", supplier_p.first_name, " ", supplier_p.last_name, ")") AS supplier,
				SUM(sales_items.quantity_purchased) AS quantity_purchased
		');
	}

	protected function _from()
	{
		parent::_from();

		$this->db->join('items AS items', 'sales_items.item_id = items.item_id', 'inner');
		$this->db->join('suppliers AS supplier_c', 'supplier_c.person_id = items.supplier_id');
		$this->db->join('people AS supplier_p', 'supplier_c.person_id = supplier_p.person_id');
	}

	protected function _group_order()
	{
		$this->db->group_by('items.supplier_id');
		$this->db->order_by('supplier_p.last_name');
	}
}
?>