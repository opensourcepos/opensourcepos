<?php

namespace App\Controllers;

use App\Libraries\Barcode_lib;

use App\Models\Item;
use App\Models\Item_kit;
use App\Models\Item_kit_items;
use CodeIgniter\Model;

class Item_kits extends Secure_Controller
{
	private Item $item;
	private Item_kit $item_kit;
	private Item_kit_items $item_kit_items;

	public function __construct()
	{
		parent::__construct('item_kits');

		$this->item = model(Item::class);
		$this->item_kit = model(Item_kit::class);
		$this->item_kit_items = model(Item_kit_items::class);
	}

	/**
	 * Add the total cost and retail price to a passed item_kit retrieving the data from each singular item part of the kit
	 */
	private function _add_totals_to_item_kit(object $item_kit): object    //TODO: Hungarian notation
	{
		$kit_item_info = $this->item->get_info($item_kit->kit_item_id ?? $item_kit->item_id);

		$item_kit->total_cost_price = 0;
		$item_kit->total_unit_price = $kit_item_info->unit_price;
		$total_quantity = 0;

		foreach($this->item_kit_items->get_info($item_kit->item_kit_id) as $item_kit_item)
		{
			$item_info = $this->item->get_info($item_kit_item['item_id']);
			foreach(get_object_vars($item_info) as $property => $value)
			{
				$item_info->$property = $value;
			}

			$item_kit->total_cost_price += $item_info->cost_price * $item_kit_item['quantity'];

			if($item_kit->price_option == PRICE_OPTION_ALL || ($item_kit->price_option == PRICE_OPTION_KIT_STOCK && $item_info->stock_type == HAS_STOCK ))
			{
				$item_kit->total_unit_price += $item_info->unit_price * $item_kit_item['quantity'];
				$total_quantity += $item_kit_item['quantity'];
			}
		}

		$discount_fraction = bcdiv($item_kit->kit_discount, '100');

		$item_kit->total_unit_price = $item_kit->total_unit_price - round(($item_kit->kit_discount_type == PERCENT)
				? bcmul($item_kit->total_unit_price, $discount_fraction)
				: $item_kit->kit_discount, totals_decimals(), PHP_ROUND_HALF_UP);

		return $item_kit;
	}

	public function getIndex(): void
	{
		$data['table_headers'] = get_item_kits_manage_table_headers();

		echo view('item_kits/manage', $data);
	}

	/**
	 * Returns Item_kit table data rows. This will be called with AJAX.
	 */
	public function getSearch(): void
	{
		$search = $this->request->getGet('search', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
		$limit  = $this->request->getGet('limit', FILTER_SANITIZE_NUMBER_INT);
		$offset = $this->request->getGet('offset', FILTER_SANITIZE_NUMBER_INT);
		$sort   = $this->request->getGet('sort', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$order  = $this->request->getGet('order', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		$item_kits = $this->item_kit->search($search, $limit, $offset, $sort, $order);
		$total_rows = $this->item_kit->get_found_rows($search);

		$data_rows = [];
		foreach($item_kits->getResult() as $item_kit)
		{
			// calculate the total cost and retail price of the Kit, so it can be printed out in the manage table
			$item_kit = $this->_add_totals_to_item_kit($item_kit);
			$data_rows[] = get_item_kit_data_row($item_kit);
		}

		echo json_encode (['total' => $total_rows, 'rows' => $data_rows]);
	}

	public function suggest_search(): void
	{
		$suggestions = $this->item_kit->get_search_suggestions($this->request->getPost('term', FILTER_SANITIZE_FULL_SPECIAL_CHARS));

		echo json_encode($suggestions);
	}

	public function getRow(int $row_id): void
	{
		// calculate the total cost and retail price of the Kit, so it can be added to the table refresh
		$item_kit = $this->_add_totals_to_item_kit($this->item_kit->get_info($row_id));

		echo json_encode(get_item_kit_data_row($item_kit));
	}

	public function getView(int $item_kit_id = NEW_ENTRY): void
	{
		$info = $this->item_kit->get_info($item_kit_id);

		if($item_kit_id == NEW_ENTRY)
		{
			$info->price_option = '0';
			$info->print_option = PRINT_ALL;
			$info->kit_item_id = 0;
			$info->item_number = '';
		}

		foreach(get_object_vars($info) as $property => $value)
		{
			$info->$property = $value;
		}

		$data['item_kit_info']  = $info;

		$items = [];

		foreach($this->item_kit_items->get_info($item_kit_id) as $item_kit_item)
		{
			$item['kit_sequence'] = $item_kit_item['kit_sequence'];
			$item['name'] = $this->item->get_info($item_kit_item['item_id'])->name;
			$item['item_id'] = $item_kit_item['item_id'];
			$item['quantity'] = $item_kit_item['quantity'];

			$items[] = $item;
		}

		$data['item_kit_items'] = $items;

		$data['selected_kit_item_id'] = $info->kit_item_id;
		$data['selected_kit_item'] = ($item_kit_id > 0 && isset($info->kit_item_id)) ? $info->item_name : '';

		echo view("item_kits/form", $data);
	}

	public function postSave(int $item_kit_id = NEW_ENTRY): void
	{
		$item_kit_data = [
			'name' => $this->request->getPost('name'),
			'item_kit_number' => $this->request->getPost('item_kit_number'),
			'item_id' => empty($this->request->getPost('kit_item_id')) ? NULL : intval($this->request->getPost('kit_item_id')),
			'kit_discount' => parse_decimals($this->request->getPost('kit_discount')),
			'kit_discount_type' => $this->request->getPost('kit_discount_type') === NULL ? PERCENT : intval($this->request->getPost('kit_discount_type')),
			'price_option' => $this->request->getPost('price_option') === NULL ? PRICE_ALL : intval($this->request->getPost('price_option')),
			'print_option' => $this->request->getPost('print_option') === NULL ? PRINT_ALL : intval($this->request->getPost('print_option')),
			'description' => $this->request->getPost('description')
		];

		if($this->item_kit->save_value($item_kit_data, $item_kit_id))
		{
			$new_item = FALSE;
			//New item kit
			if($item_kit_id == NEW_ENTRY)
			{
				$item_kit_id = $item_kit_data['item_kit_id'];
				$new_item = TRUE;
			}

			$item_kit_items_array = $this->request->getPost('item_kit_qty') === NULL ? NULL : $this->request->getPost('item_kit_qty');

			if($item_kit_items_array != NULL)
			{
				$item_kit_items = [];
				foreach($item_kit_items_array as $item_id => $item_kit_qty)
				{
					$item_kit_items[] = [
						'item_id' => $item_id,
						'quantity' => $item_kit_qty === NULL ? 0 : parse_quantity($item_kit_qty),
						'kit_sequence' => $this->request->getPost("item_kit_seq[$item_id]") === NULL ? 0 : intval($this->request->getPost("item_kit_seq[$item_id]"))
					];
				}
			}

			if (!empty($item_kit_items))
			{
				$success = $this->item_kit_items->save_value($item_kit_items, $item_kit_id);
			}
			else
			{
				$success = TRUE;
			}

			if($new_item)
			{
				echo json_encode ([
					'success' => $success,
					'message' => lang('Item_kits.successful_adding').' '.$item_kit_data['name'],
					'id' => $item_kit_id
				]);

			}
			else
			{
				echo json_encode ([
					'success' => $success,
					'message' => lang('Item_kits.successful_updating').' '.$item_kit_data['name'],
					'id' => $item_kit_id
				]);
			}
		}
		else//failure
		{
			echo json_encode ([
				'success' => FALSE,
				'message' => lang('Item_kits.error_adding_updating') . ' ' . $item_kit_data['name'],
				'id' => NEW_ENTRY
			]);
		}
	}

	public function postDelete(): void
	{
		$item_kits_to_delete = $this->request->getPost('ids', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		if($this->item_kit->delete_list($item_kits_to_delete))
		{
			echo json_encode ([
				'success' => TRUE,
				'message' => lang('Item_kits.successful_deleted') . ' ' . count($item_kits_to_delete) . ' ' . lang('Item_kits.one_or_multiple')
			]);
		}
		else
		{
			echo json_encode (['success' => FALSE, 'message' => lang('Item_kits.cannot_be_deleted')]);
		}
	}

	public function postCheckItemNumber(): void
	{
		$exists = $this->item_kit->item_number_exists($this->request->getPost('item_kit_number', FILTER_SANITIZE_FULL_SPECIAL_CHARS), $this->request->getPost('item_kit_id', FILTER_SANITIZE_NUMBER_INT));
		echo !$exists ? 'true' : 'false';
	}

	public function generate_barcodes(string $item_kit_ids): void
	{
		$barcode_lib = new Barcode_lib();
		$result = [];

		$item_kit_ids = explode(':', $item_kit_ids);
		foreach($item_kit_ids as $item_kid_id)
		{
			// calculate the total cost and retail price of the Kit, so it can be added to the barcode text at the bottom
			$item_kit = $this->_add_totals_to_item_kit($this->item_kit->get_info($item_kid_id));

			$item_kid_id = 'KIT '. urldecode($item_kid_id);

			$result[] = [
				'name' => $item_kit->name,
				'item_id' => $item_kid_id,
				'item_number' => $item_kid_id,
				'cost_price' => $item_kit->total_cost_price,
				'unit_price' => $item_kit->total_unit_price
			];
		}

		$data['items'] = $result;
		$barcode_config = $barcode_lib->get_barcode_config();
		// in case the selected barcode type is not Code39 or Code128 we set by default Code128
		// the rationale for this is that EAN codes cannot have strings as seed, so 'KIT ' is not allowed
		if($barcode_config['barcode_type'] != 'Code39' && $barcode_config['barcode_type'] != 'Code128')
		{
			$barcode_config['barcode_type'] = 'Code128';
		}
		$data['barcode_config'] = $barcode_config;

		// display barcodes
		echo view("barcodes/barcode_sheet", $data);
	}
}
