<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Sales_Tax_Data extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();

		$this->load->library('tax_lib');
		$this->load->library('sale_lib');

	}

	public function up()
	{
		$number_of_unmigrated = $this->get_count_of_unmigrated();

		error_log('Migrating sales tax history.  The number of sales that will be migrated is '.$number_of_unmigrated);

		if($number_of_unmigrated > 0)
		{
			$unmigrated_invoices = $this->get_unmigrated($number_of_unmigrated)->result_array();

			foreach($unmigrated_invoices as $key=>$unmigrated_invoice)
			{
				$this->upgrade_tax_history_for_sale($unmigrated_invoice['sale_id']);
			}
		}

		error_log('Migrating sales tax history.  The number of sales that will be migrated is finished.');
	}

	public function down()
	{

	}

	private function upgrade_tax_history_for_sale($sale_id)
	{
		$CI =& get_instance();
		$tax_decimals = $CI->config->config['tax_decimals'];
		$tax_included = $CI->config->config['tax_included'];
		$customer_sales_tax_support = $CI->config->config['customer_sales_tax_support'];

		if($tax_included)
		{
			$tax_type = Tax_lib::TAX_TYPE_VAT;
		}
		else
		{
			$tax_type = Tax_lib::TAX_TYPE_SALES;
		}

		$sales_taxes = array();
		$tax_group_sequence = 0;

		$items = $this->get_sale_items_for_migration($sale_id)->result_array();
		foreach($items as $item)
		{
			// This computes tax for each line item and adds it to the tax type total
			$tax_group = (float)$item['percent'] . '% ' . $item['name'];
			$tax_basis = $this->sale_lib->get_item_total($item['quantity_purchased'], $item['item_unit_price'], $item['discount_percent'], TRUE);
			$item_tax_amount = 0;
			if($tax_included)
			{
				$item_tax_amount = $this->sale_lib->get_item_tax($item['quantity_purchased'], $item['item_unit_price'], $item['discount_percent'], $item['percent']);
			}
			else
			{
				$item_tax_amount = $this->tax_lib->get_sales_tax_for_amount($tax_basis, $item['percent'], PHP_ROUND_HALF_UP, $tax_decimals);
			}
			$this->update_sales_items_taxes_amount($sale_id, $item['line'], $item['name'], $item['percent'], $tax_type, $item_tax_amount);
			$this->tax_lib->update_sales_taxes($sales_taxes, $tax_type, $tax_group, $item['percent'], $tax_basis, $item_tax_amount, $tax_group_sequence, PHP_ROUND_HALF_UP, $sale_id, $item['name']);
			$tax_group_sequence += 1;
		}

		// Not sure when this would ever kick in, but this is technically the correct logic.
		if($customer_sales_tax_support)
		{
			$this->tax_lib->apply_invoice_taxing($sales_taxes);
		}

		$this->tax_lib->round_sales_taxes($sales_taxes);
		$this->save_sales_tax($sales_taxes);
	}

	private function get_unmigrated($block_count)
	{
		$this->db->select('SIT.sale_id');
		$this->db->select('ST.sale_id as sales_taxes_sale_id');
		$this->db->from('sales_items_taxes as SIT');
		$this->db->join('sales_taxes as ST','SIT.sale_id = ST.sale_id', 'left');
		$this->db->where('ST.sale_id is null');
		$this->db->group_by('SIT.sale_id');
		$this->db->group_by('ST.sale_id');
		$this->db->order_by('SIT.sale_id');
		$this->db->limit($block_count);

		return $this->db->get();
	}

	private function get_sale_items_for_migration($sale_id)
	{
		$this->db->select('sales_items.sale_id as sale_id');
		$this->db->select('sales_items.line as line');
		$this->db->select('item_unit_price');
		$this->db->select('discount_percent');
		$this->db->select('quantity_purchased');
		$this->db->select('percent');
		$this->db->select('name');
		$this->db->from('sales_items as sales_items');
		$this->db->join('sales_items_taxes as sales_items_taxes', 'sales_items.sale_id = sales_items_taxes.sale_id and sales_items.line = sales_items_taxes.line');
		$this->db->where('sales_items.sale_id', $sale_id);

		return $this->db->get();
	}

	private function get_count_of_unmigrated()
	{
		$result = $this->db->query('SELECT COUNT(*) FROM(SELECT SIT.sale_id, ST.sale_id as sales_taxes_sale_id FROM '
			. $this->db->dbprefix('sales_items_taxes')
			. ' as SIT LEFT JOIN '
			. $this->db->dbprefix('sales_taxes')
			. ' as ST ON SIT.sale_id = ST.sale_id WHERE ST.sale_id is null GROUP BY SIT.sale_id, ST.sale_id'
			. ' ORDER BY SIT.sale_id) as US')->result_array();

		return $result[0]['COUNT(*)'];
	}

	private function update_sales_items_taxes_amount($sale_id, $line, $name, $percent, $tax_type, $item_tax_amount)
	{
		$this->db->where('sale_id', $sale_id);
		$this->db->where('line', $line);
		$this->db->where('name', $name);
		$this->db->where('percent', $percent);
		$this->db->update('sales_items_taxes', array('tax_type' => $tax_type, 'item_tax_amount' => $item_tax_amount));
	}

	private function save_sales_tax(&$sales_taxes)
	{
		foreach($sales_taxes as $line=>$sales_tax)
		{
			$this->db->insert('sales_taxes', $sales_tax);
		}
	}
}
?>
