<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use App\Libraries\Tax_lib;
use App\Models\Appconfig;
use CodeIgniter\Database\ResultInterface;

/**
 *
 *
 * @property appconfig appconfig
 * @property tax_lib tax_lib
 */
class Migration_TaxAmount extends Migration
{
	const ROUND_UP = 5;
	const ROUND_DOWN = 6;
	const HALF_FIVE = 7;
	const YES = '1';
	const VAT_TAX = '0';
	const SALES_TAX = '1';	//TODO: It appears that this constant is never used

	public function __construct()
	{
		parent::__construct();

		$this->appconfig = model('Appconfig');
	}

	public function up(): void
	{
		$tax_included = ($this->appconfig->get_value('tax_included', Migration_TaxAmount::YES) == Migration_TaxAmount::YES);

		if($tax_included)
		{
			$tax_decimals = $this->appconfig->get_value('tax_decimals', 2);
			$number_of_unmigrated = $this->get_count_of_unmigrated();

			error_log('Migrating sales tax fixing. The number of sales that will be migrated is ' . $number_of_unmigrated);

			if($number_of_unmigrated > 0)
			{
				$unmigrated_invoices = $this->get_unmigrated($number_of_unmigrated)->getResultArray();
				$this->db->query('RENAME TABLE ' . $this->db->prefixTable('sales_taxes') . ' TO ' . $this->db->prefixTable('sales_taxes_backup'));
				$this->db->query('CREATE TABLE ' . $this->db->prefixTable('sales_taxes') . ' LIKE ' . $this->db->prefixTable('sales_taxes_backup'));

				foreach($unmigrated_invoices as $key => $unmigrated_invoice)
				{
					$this->upgrade_tax_history_for_sale($unmigrated_invoice['sale_id'], $tax_decimals, TRUE);
				}
				$this->db->query('DROP TABLE ' . $this->db->prefixTable('sales_taxes_backup'));
			}

			error_log('Migrating sales tax fixing. The number of sales that will be migrated is finished.');
		}
	}

	public function down(): void
	{

	}

	private function upgrade_tax_history_for_sale(int $sale_id, string $tax_decimals, bool $tax_included): void	//TODO: $tax_included is passed as a parameter but never used in the function body.
	{
		$customer_sales_tax_support = false;
		$tax_type = Migration_TaxAmount::VAT_TAX;
		$sales_taxes = [];
		$tax_group_sequence = 0;
		$items = $this->get_sale_items_for_migration($sale_id)->getResultArray();

		foreach($items as $item)
		{
			// This computes tax for each line item and adds it to the tax type total
			$tax_group = (float)$item['percent'] . '% ' . $item['name'];
			$tax_basis = $this->get_item_total($item['quantity_purchased'], $item['item_unit_price'], $item['discount'], true);
			$item_tax_amount = $this->get_item_tax($tax_basis, $item['percent'], PHP_ROUND_HALF_UP, $tax_decimals);
			$this->update_sales_items_taxes_amount($sale_id, $item['line'], $item['name'], $item['percent'], $tax_type, $item_tax_amount);
			$this->update_sales_taxes($sales_taxes, $tax_type, $tax_group, $item['percent'], $tax_basis, $item_tax_amount, $tax_group_sequence, PHP_ROUND_HALF_UP, $sale_id, $item['name']);
			$tax_group_sequence += 1;
		}

		if($customer_sales_tax_support)	//TODO: This will always evaluate to false.
		{
			$this->apply_invoice_taxing($sales_taxes);
		}

		$this->round_sales_taxes($sales_taxes);
		$this->save_sales_tax($sales_taxes);
	}

	private function get_unmigrated(int $block_count): ResultInterface
	{
		$builder = $this->db->table('sales_items_taxes as SIT');
		$builder->select('SIT.sale_id');
		$builder->select('ST.sale_id as sales_taxes_sale_id');
		$builder->join('sales_taxes as ST', 'SIT.sale_id = ST.sale_id', 'left');
		$builder->groupBy('SIT.sale_id');
		$builder->groupBy('ST.sale_id');
		$builder->orderBy('SIT.sale_id');
		$builder->limit($block_count);

		return $builder->get();
	}

	private function get_count_of_unmigrated(): int
	{
		$result = $this->db->query('SELECT COUNT(*) FROM(SELECT SIT.sale_id, ST.sale_id as sales_taxes_sale_id FROM '
			. $this->db->prefixTable('sales_items_taxes')
			. ' as SIT LEFT JOIN '
			. $this->db->prefixTable('sales_taxes')
			. ' as ST ON SIT.sale_id = ST.sale_id GROUP BY SIT.sale_id, ST.sale_id'
			. ' ORDER BY SIT.sale_id) as US')->getResultArray();

		return $result[0]['COUNT(*)'];
	}

	private function get_sale_items_for_migration(int $sale_id): ResultInterface
	{
		$builder = $this->db->table('sales_items as sales_items');
		$builder->select('sales_items.sale_id as sale_id');
		$builder->select('sales_items.line as line');
		$builder->select('item_unit_price');
		$builder->select('discount');
		$builder->select('quantity_purchased');
		$builder->select('percent');
		$builder->select('name');
		$builder->join('sales_items_taxes as sales_items_taxes', 'sales_items.sale_id = sales_items_taxes.sale_id and sales_items.line = sales_items_taxes.line');
		$builder->where('sales_items.sale_id', $sale_id);

		return $builder->get();
	}

	private function update_sales_items_taxes_amount(int $sale_id, int $line, string $name, float $percent, int $tax_type, float $item_tax_amount): void
	{
		$builder = $this->db->table('sales_items_taxes');
		$builder->where('sale_id', $sale_id);
		$builder->where('line', $line);
		$builder->where('name', $name);
		$builder->where('percent', $percent);
		$builder->update(['tax_type' => $tax_type, 'item_tax_amount' => $item_tax_amount]);
	}

	private function save_sales_tax(array &$sales_taxes): void
	{
		$builder = $this->db->table('sales_taxes');

		foreach($sales_taxes as $line => $sales_tax)
		{
			$builder->insert($sales_tax);
		}
	}

	public function get_item_total(string $quantity, string $price, string $discount, bool $include_discount = FALSE): string
	{
		$total = bcmul($quantity, $price);

		if($include_discount)
		{
			$total = bcsub($total, bcmul(bcmul($quantity, $price), bcdiv($discount, 100)));
		}

		return $total;
	}

	public function get_item_tax(string $tax_basis, string $tax_percentage, int $rounding_mode, int $decimals): float	//TODO: is this currency safe?
	{
		$tax_fraction = bcdiv(bcadd('100', $tax_percentage), '100');
		$price_tax_excl = bcdiv($tax_basis, $tax_fraction);
		$tax_amount = bcsub($tax_basis, $price_tax_excl);

		return $this->round_number($rounding_mode, $tax_amount, $decimals);
	}

	public function get_sales_tax_for_amount(string $tax_basis, string $tax_percentage, int $rounding_mode, int $decimals): float	//TODO: is this currency safe?
	{
		$tax_fraction = bcdiv($tax_percentage, '100');
		$tax_amount = bcmul($tax_basis, $tax_fraction);

		return $this->round_number($rounding_mode, $tax_amount, $decimals);
	}

	public function round_number(int $rounding_mode, string $amount, int $decimals): float	//TODO: is this currency safe?
	{//TODO: This needs to be converted to a switch
		if($rounding_mode == Migration_TaxAmount::ROUND_UP)	//TODO: === ?
		{
			$fig = pow(10, $decimals);
			$rounded_total = (ceil($fig * $amount) + ceil($fig*$amount - ceil($fig * $amount)))/$fig;
		}
		elseif($rounding_mode == Migration_TaxAmount::ROUND_DOWN)	//TODO: === ?
		{
			$fig = pow(10, $decimals);
			$rounded_total = (floor($fig * $amount) + floor($fig * $amount - floor($fig * $amount)))/$fig;
		}
		elseif($rounding_mode == Migration_TaxAmount::HALF_FIVE)	//TODO: === ?
		{
			$rounded_total = round($amount / 5) * 5;
		}
		else
		{
			$rounded_total = round($amount, $decimals, $rounding_mode);
		}

		return $rounded_total;
	}

	public function update_sales_taxes(array &$sales_taxes, int $tax_type, string $tax_group, float $tax_rate, string $tax_basis, string $item_tax_amount, int $tax_group_sequence, int $rounding_code, int $sale_id, string $name = '', string $tax_code = ''): void
	{
		$tax_group_index = $this->clean('X' . $tax_group);

		if(!array_key_exists($tax_group_index, $sales_taxes))
		{
			$insertkey = $tax_group_index;
			$sales_tax = [
				$insertkey => [
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
				]
			];

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

	public function clean(string $string): string	//TODO: This can probably go into the migration helper as it's used it more than one migration. Also, $string needs to be refactored to a different name.
	{
		$string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

		return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
	}

	public function apply_invoice_taxing(array &$sales_taxes): void
	{
		if(!empty($sales_taxes))	//TODO: Duplicated code
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

	public function round_sales_taxes(array &$sales_taxes): void
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

			if ($rounding_code == PHP_ROUND_HALF_UP	//TODO: This block of if/elseif statements can be converted to a switch.
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
