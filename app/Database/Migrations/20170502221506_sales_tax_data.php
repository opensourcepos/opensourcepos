<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Sales_Tax_Data extends CI_Migration
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
		$this->tax_lib = new Tax_lib();
	}

	public function up()
	{
		$number_of_unmigrated = $this->get_count_of_unmigrated();
		error_log('Migrating sales tax history. The number of sales that will be migrated is ' . $number_of_unmigrated);
		if($number_of_unmigrated > 0)
		{
			$unmigrated_invoices = $this->get_unmigrated($number_of_unmigrated)->getResultArray();
			foreach($unmigrated_invoices as $key=>$unmigrated_invoice)
			{
				$this->upgrade_tax_history_for_sale($unmigrated_invoice['sale_id']);
			}
		}
		error_log('Migrating sales tax history. The number of sales that will be migrated is finished.');
	}

	public function down()
	{
	}

	private function upgrade_tax_history_for_sale($sale_id)
	{
		$CI =& get_instance();
		$tax_decimals = $CI->Appconfig->get('tax_decimals', 2);
		$tax_included = $CI->Appconfig->get('tax_included', Migration_Sales_Tax_Data::YES) == Migration_Sales_Tax_Data::YES;
		$customer_sales_tax_support = FALSE;
		if($tax_included)
		{
			$tax_type = Migration_Sales_Tax_Data::VAT_TAX;
		}
		else
		{
			$tax_type = Migration_Sales_Tax_Data::SALES_TAX;
		}
		$sales_taxes = [];
		$tax_group_sequence = 0;
		$items = $this->get_sale_items_for_migration($sale_id)->getResultArray();
		foreach($items as $item)
		{
			// This computes tax for each line item and adds it to the tax type total
			$tax_group = (float)$item['percent'] . '% ' . $item['name'];
			$tax_basis = $this->get_item_total($item['quantity_purchased'], $item['item_unit_price'], $item['discount_percent'], TRUE);
			$item_tax_amount = 0;
			if($tax_included)
			{
				$item_tax_amount = $this->get_item_tax($item['quantity_purchased'], $item['item_unit_price'], $item['discount_percent'], $item['percent']);
			}
			else
			{
				$item_tax_amount = $this->get_sales_tax_for_amount($tax_basis, $item['percent'], PHP_ROUND_HALF_UP, $tax_decimals);
			}
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
		$builder->select('SIT.sale_id');
		$builder->select('ST.sale_id as sales_taxes_sale_id');
		$builder = $this->db->table('sales_items_taxes as SIT');
		$builder->join('sales_taxes as ST','SIT.sale_id = ST.sale_id', 'left');
		$builder->where('ST.sale_id is null');
		$builder->groupBy('SIT.sale_id');
		$builder->groupBy('ST.sale_id');
		$builder->orderBy('SIT.sale_id');
		$builder->limit($block_count);
		return $builder->get();
	}

	private function get_sale_items_for_migration($sale_id)
	{
		$builder->select('sales_items.sale_id as sale_id');
		$builder->select('sales_items.line as line');
		$builder->select('item_unit_price');
		$builder->select('discount_percent');
		$builder->select('quantity_purchased');
		$builder->select('percent');
		$builder->select('name');
		$builder = $this->db->table('sales_items as sales_items');
		$builder->join('sales_items_taxes as sales_items_taxes', 'sales_items.sale_id = sales_items_taxes.sale_id and sales_items.line = sales_items_taxes.line');
		$builder->where('sales_items.sale_id', $sale_id);
		return $builder->get();
	}

	private function get_count_of_unmigrated()
	{
		$result = $this->db->query('SELECT COUNT(*) FROM(SELECT SIT.sale_id, ST.sale_id as sales_taxes_sale_id FROM '
			. $this->db->prefixTable('sales_items_taxes')
			. ' as SIT LEFT JOIN '
			. $this->db->prefixTable('sales_taxes')
			. ' as ST ON SIT.sale_id = ST.sale_id WHERE ST.sale_id is null GROUP BY SIT.sale_id, ST.sale_id'
			. ' ORDER BY SIT.sale_id) as US')->getResultArray();
		return $result[0]['COUNT(*)'];
	}

	private function update_sales_items_taxes_amount($sale_id, $line, $name, $percent, $tax_type, $item_tax_amount)
	{
		$builder->where('sale_id', $sale_id);
		$builder->where('line', $line);
		$builder->where('name', $name);
		$builder->where('percent', $percent);
		$builder->update('sales_items_taxes', ['tax_type' => $tax_type, 'item_tax_amount' => $item_tax_amount));
	}

	private function save_sales_tax(&$sales_taxes)
	{
		foreach($sales_taxes as $line=>$sales_tax)
		{
			$builder = $this->db->Table('sales_taxes');	//TODO: need to confirm if this should be inside the for loop or after.  Do I need to "reset" the builder variable after running insert?
			$builder->insert($sales_tax);
		}
	}

	public function get_item_total($quantity, $price, $discount_percentage, $include_discount = FALSE)
	{
		$total = bcmul($quantity, $price);
		if($include_discount)
		{
			$discount_amount = $this->get_item_discount($quantity, $price, $discount_percentage);
			return bcsub($total, $discount_amount);
		}
		return $total;
	}

	public function get_item_discount($quantity, $price, $discount)
	{
		$total = bcmul($quantity, $price);
		$discount_fraction = bcdiv($discount, 100);
		$discount = bcmul($total, $discount_fraction);

		return round($discount, totals_decimals(), PHP_ROUND_HALF_UP);
	}

	public function get_item_tax($quantity, $price, $discount_percentage, $tax_percentage)
	{
		$CI =& get_instance();
		$tax_included = $CI->Appconfig->get('tax_included', Migration_Sales_Tax_Data::YES) == Migration_Sales_Tax_Data::YES;

		$price = $this->get_item_total($quantity, $price, $discount_percentage, TRUE);

		if($tax_included)
		{
			$tax_fraction = bcadd(100, $tax_percentage);
			$tax_fraction = bcdiv($tax_fraction, 100);
			$price_tax_excl = bcdiv($price, $tax_fraction);
			return bcsub($price, $price_tax_excl);
		}
		$tax_fraction = bcdiv($tax_percentage, 100);
		return bcmul($price, $tax_fraction);
	}

	public function get_sales_tax_for_amount($tax_basis, $tax_percentage, $rounding_mode, $decimals)
	{
		$tax_fraction = bcdiv($tax_percentage, 100);
		$tax_amount = bcmul($tax_basis, $tax_fraction);
		return $this->round_number($rounding_mode, $tax_amount, $decimals);
	}

	public function round_number($rounding_mode, $amount, $decimals)
	{
		if($rounding_mode == Migration_Sales_Tax_Data::ROUND_UP)
		{
			$fig = pow(10,$decimals);
			$rounded_total = (ceil($fig*$amount) + ceil($fig*$amount - ceil($fig*$amount)))/$fig;
		}
		elseif($rounding_mode == Migration_Sales_Tax_Data::ROUND_DOWN)
		{
			$fig = pow(10,$decimals);
			$rounded_total = (floor($fig*$amount) + floor($fig*$amount - floor($fig*$amount)))/$fig;
		}
		elseif($rounding_mode == Migration_Sales_Tax_Data::HALF_FIVE)
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
			$sales_tax = [$insertkey => [
				'sale_id' => $sale_id,
				'tax_type' => $tax_type,
				'tax_group' => $tax_group,
				'sale_tax_basis' => $tax_basis,
				'sale_tax_amount' => $item_tax_amount,
				'print_sequence' => $tax_group_sequence,
				'name' => $name,
				'tax_rate' => $tax_rate,
				'sales_tax_code' => $tax_code,
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
			$sort = [];
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
			$sort = [];
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
			elseif($rounding_code == Migration_Sales_Tax_Data::ROUND_UP)
			{
				$fig = (int) str_pad('1', $decimals, '0');
				$rounded_sale_tax_amount = (ceil($sale_tax_amount * $fig) / $fig);
			}
			elseif($rounding_code == Migration_Sales_Tax_Data::ROUND_DOWN)
			{
				$fig = (int) str_pad('1', $decimals, '0');
				$rounded_sale_tax_amount = (floor($sale_tax_amount * $fig) / $fig);
			}
			elseif($rounding_code == Migration_Sales_Tax_Data::HALF_FIVE)
			{
				$rounded_sale_tax_amount = round($sale_tax_amount / 5) * 5;
			}
			$sales_taxes[$row_number]['sale_tax_amount'] = $rounded_sale_tax_amount;
		}
	}
}
?>
