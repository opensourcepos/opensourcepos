<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_TaxAmount extends CI_Migration
{
	const ROUND_UP = 5;
	const ROUND_DOWN = 6;
	const HALF_FIVE = 7;
	const YES = '1';
	const VAT_TAX = '0';
	const SALES_TAX = '1';

	public function __construct()
	{
		parent::__construct();
		$this->load->library('tax_lib');
	}

	public function up()
	{
		$CI =& get_instance();
		$tax_included = $CI->Appconfig->get('tax_included', Migration_TaxAmount::YES) == Migration_TaxAmount::YES;

		if($tax_included)
		{
			$tax_decimals = $CI->Appconfig->get('tax_decimals', 2);
			$number_of_unmigrated = $this->get_count_of_unmigrated();
			error_log('Migrating sales tax fixing. The number of sales that will be migrated is ' . $number_of_unmigrated);
			if($number_of_unmigrated > 0)
			{
				$unmigrated_invoices = $this->get_unmigrated($number_of_unmigrated)->result_array();
				$this->db->query('RENAME TABLE ' . $this->db->dbprefix('sales_taxes') . ' TO ' . $this->db->dbprefix('sales_taxes_backup'));
				$this->db->query('CREATE TABLE ' . $this->db->dbprefix('sales_taxes') . ' LIKE ' . $this->db->dbprefix('sales_taxes_backup'));
				foreach($unmigrated_invoices as $key=>$unmigrated_invoice)
				{
					$this->upgrade_tax_history_for_sale($unmigrated_invoice['sale_id'], $tax_decimals, $tax_included);
				}
				$this->db->query('DROP TABLE ' . $this->db->dbprefix('sales_taxes_backup'));
			}
			error_log('Migrating sales tax fixing. The number of sales that will be migrated is finished.');
		}
	}

	public function down()
	{
	}

	private function upgrade_tax_history_for_sale($sale_id, $tax_decimals, $tax_included)
	{
		$customer_sales_tax_support = FALSE;
		$tax_type = Migration_TaxAmount::VAT_TAX;
		$sales_taxes = array();
		$tax_group_sequence = 0;
		$items = $this->get_sale_items_for_migration($sale_id)->result_array();
		foreach($items as $item)
		{
			// This computes tax for each line item and adds it to the tax type total
			$tax_group = (float)$item['percent'] . '% ' . $item['name'];
			$tax_basis = $this->get_item_total($item['quantity_purchased'], $item['item_unit_price'], $item['discount'], TRUE);
			$item_tax_amount = $this->get_item_tax($tax_basis, $item['percent'], PHP_ROUND_HALF_UP, $tax_decimals);
			$this->update_sales_items_taxes_amount($sale_id, $item['line'], $item['name'], $item['percent'], $tax_type, $item_tax_amount);
			$this->update_sales_taxes($sales_taxes, $tax_type, $tax_group, $item['percent'], $tax_basis, $item_tax_amount, $tax_group_sequence, PHP_ROUND_HALF_UP, $sale_id, $item['name']);
			$tax_group_sequence += 1;
		}
		// Not sure when this would ever kick in, but this is technically the correct logic.
		if($customer_sales_tax_support)
		{
			$this->apply_invoice_taxing($sales_taxes);
		}
		$this->round_sales_taxes($sales_taxes);
		$this->save_sales_tax($sales_taxes);
	}

	private function get_unmigrated($block_count)
	{
		$this->db->select('SIT.sale_id');
		$this->db->select('ST.sale_id as sales_taxes_sale_id');
		$this->db->from('sales_items_taxes as SIT');
		$this->db->join('sales_taxes as ST', 'SIT.sale_id = ST.sale_id', 'left');
		$this->db->group_by('SIT.sale_id');
		$this->db->group_by('ST.sale_id');
		$this->db->order_by('SIT.sale_id');
		$this->db->limit($block_count);
		return $this->db->get();
	}

	private function get_count_of_unmigrated()
	{
		$result = $this->db->query('SELECT COUNT(*) FROM(SELECT SIT.sale_id, ST.sale_id as sales_taxes_sale_id FROM '
			. $this->db->dbprefix('sales_items_taxes')
			. ' as SIT LEFT JOIN '
			. $this->db->dbprefix('sales_taxes')
			. ' as ST ON SIT.sale_id = ST.sale_id GROUP BY SIT.sale_id, ST.sale_id'
			. ' ORDER BY SIT.sale_id) as US')->result_array();
		return $result[0]['COUNT(*)'];
	}

	private function get_sale_items_for_migration($sale_id)
	{
		$this->db->select('sales_items.sale_id as sale_id');
		$this->db->select('sales_items.line as line');
		$this->db->select('item_unit_price');
		$this->db->select('discount');
		$this->db->select('quantity_purchased');
		$this->db->select('percent');
		$this->db->select('name');
		$this->db->from('sales_items as sales_items');
		$this->db->join('sales_items_taxes as sales_items_taxes', 'sales_items.sale_id = sales_items_taxes.sale_id and sales_items.line = sales_items_taxes.line');
		$this->db->where('sales_items.sale_id', $sale_id);
		return $this->db->get();
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

	public function get_item_total($quantity, $price, $discount, $include_discount = FALSE)
	{
		$total = bcmul($quantity, $price);
		
		if($include_discount)
		{
			$total = bcsub($total, bcmul(bcmul($quantity, $price), bcdiv($discount, 100)));
		}

		return $total;
	}

	public function get_item_tax($tax_basis, $tax_percentage, $rounding_mode, $decimals)
	{
		$tax_fraction = bcdiv(bcadd(100, $tax_percentage), 100);
		$price_tax_excl = bcdiv($tax_basis, $tax_fraction);
		$tax_amount = bcsub($tax_basis, $price_tax_excl);

		return $this->round_number($rounding_mode, $tax_amount, $decimals);
	}

	public function get_sales_tax_for_amount($tax_basis, $tax_percentage, $rounding_mode, $decimals)
	{
		$tax_fraction = bcdiv($tax_percentage, 100);
		$tax_amount = bcmul($tax_basis, $tax_fraction);

		return $this->round_number($rounding_mode, $tax_amount, $decimals);
	}

	public function round_number($rounding_mode, $amount, $decimals)
	{
		if($rounding_mode == Migration_TaxAmount::ROUND_UP)
		{
			$fig = pow(10,$decimals);
			$rounded_total = (ceil($fig*$amount) + ceil($fig*$amount - ceil($fig*$amount)))/$fig;
		}
		elseif($rounding_mode == Migration_TaxAmount::ROUND_DOWN)
		{
			$fig = pow(10,$decimals);
			$rounded_total = (floor($fig*$amount) + floor($fig*$amount - floor($fig*$amount)))/$fig;
		}
		elseif($rounding_mode == Migration_TaxAmount::HALF_FIVE)
		{
			$rounded_total = round($amount / 5) * 5;
		}
		else
		{
			$rounded_total = round($amount, $decimals, $rounding_mode);
		}

		return $rounded_total;
	}

	public function update_sales_taxes(&$sales_taxes, $tax_type, $tax_group, $tax_rate, $tax_basis, $item_tax_amount, $tax_group_sequence, $rounding_code, $sale_id, $name='', $tax_code='')
	{
		$tax_group_index = $this->clean('X'.$tax_group);
		if(!array_key_exists($tax_group_index, $sales_taxes))
		{
			$insertkey = $tax_group_index;
			$sales_tax = array($insertkey => array(
				'sale_id' => $sale_id,
				'tax_type' => $tax_type,
				'tax_group' => $tax_group,
				'sale_tax_basis' => $tax_basis,
				'sale_tax_amount' => $item_tax_amount,
				'print_sequence' => $tax_group_sequence,
				'name' => $name,
				'tax_rate' => $tax_rate,
				'sales_tax_code_id' => $tax_code,
				'rounding_code' => $rounding_code
			));
			//add to existing array
			$sales_taxes += $sales_tax;
		}
		else
		{
			// Important ... the sales amounts are accumulated for the group at the maximum configurable scale value of 4
			// but the scale will in reality be the scale specified by the tax_decimal configuration value  used for sales_items_taxes
			$sales_taxes[$tax_group_index]['sale_tax_basis'] = bcadd($sales_taxes[$tax_group_index]['sale_tax_basis'], $tax_basis, 4);
			$sales_taxes[$tax_group_index]['sale_tax_amount'] = bcadd($sales_taxes[$tax_group_index]['sale_tax_amount'], $item_tax_amount, 4);
		}
	}

	public function clean($string)
	{
		$string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

		return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
	}

	public function apply_invoice_taxing(&$sales_taxes)
	{
		if(!empty($sales_taxes))
		{
			$sort = array();
			foreach($sales_taxes as $k => $v)
			{
				$sort['print_sequence'][$k] = $v['print_sequence'];
			}
			array_multisort($sort['print_sequence'], SORT_ASC, $sales_taxes);
		}
		$decimals = totals_decimals();
		foreach($sales_taxes as $row_number => $sales_tax)
		{
			$sales_taxes[$row_number]['sale_tax_amount'] = $this->get_sales_tax_for_amount($sales_tax['sale_tax_basis'], $sales_tax['tax_rate'], $sales_tax['rounding_code'], $decimals);
		}
	}

	public function round_sales_taxes(&$sales_taxes)
	{
		if(!empty($sales_taxes))
		{
			$sort = array();
			foreach($sales_taxes as $k=>$v)
			{
				$sort['print_sequence'][$k] = $v['print_sequence'];
			}
			array_multisort($sort['print_sequence'], SORT_ASC, $sales_taxes);
		}
		$decimals = totals_decimals();
		foreach($sales_taxes as $row_number => $sales_tax)
		{
			$sale_tax_amount = $sales_tax['sale_tax_amount'];
			$rounding_code = $sales_tax['rounding_code'];
			$rounded_sale_tax_amount = $sale_tax_amount;

			if ($rounding_code == PHP_ROUND_HALF_UP
				|| $rounding_code == PHP_ROUND_HALF_DOWN
				|| $rounding_code == PHP_ROUND_HALF_EVEN
				|| $rounding_code == PHP_ROUND_HALF_ODD)
			{
				$rounded_sale_tax_amount = round($sale_tax_amount, $decimals, $rounding_code);
			}
			elseif($rounding_code == Migration_TaxAmount::ROUND_UP)
			{
				$fig = (int) str_pad('1', $decimals, '0');
				$rounded_sale_tax_amount = (ceil($sale_tax_amount * $fig) / $fig);
			}
			elseif($rounding_code == Migration_TaxAmount::ROUND_DOWN)
			{
				$fig = (int) str_pad('1', $decimals, '0');
				$rounded_sale_tax_amount = (floor($sale_tax_amount * $fig) / $fig);
			}
			elseif($rounding_code == Migration_TaxAmount::HALF_FIVE)
			{
				$rounded_sale_tax_amount = round($sale_tax_amount / 5) * 5;
			}
			$sales_taxes[$row_number]['sale_tax_amount'] = $rounded_sale_tax_amount;
		}
	}
}
?>
