<?php

namespace App\Controllers;



use app\Libraries\Item_lib;

use app\Models\Attribute;
use app\Models\Item;
use app\Models\Stock_location;
use Config\Services;

require_once('Secure_Controller.php');

/**
 *
 * @property image image
 *
 * @property item_lib item_lib
 *
 * @property attribute attribute
 * @property item item
 * @property stock_location stock_location
 *
 */
class Items extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('items');

		$this->image = Services::image();

		$this->item_lib = new Item_lib();

		$this->attribute = model('Attribute');
		$this->item = model('Item');
		$this->stock_location = model('Stock_location');
	}

	public function index()
	{
		$this->session->set_userdata('allow_temp_items', 0);

		$data['table_headers'] = $this->xss_clean(get_items_manage_table_headers());
		$data['stock_location'] = $this->xss_clean($this->item_lib->get_item_location());
		$data['stock_locations'] = $this->xss_clean($this->stock_location->get_allowed_locations());

		//Filters that will be loaded in the multiselect dropdown
		$data['filters'] = [
			'empty_upc' => lang('Items.empty_upc_items'),
			'low_inventory' => lang('Items.low_inventory_items'),
			'is_serialized' => lang('Items.serialized_items'),
			'no_description' => lang('Items.no_description_items'),
			'search_custom' => lang('Items.search_attributes'),
			'is_deleted' => lang('Items.is_deleted'),
			'temporary' => lang('Items.temp')
		];

		echo view('items/manage', $data);
	}

	/*
	 * Returns Items table data rows. This will be called with AJAX.
	 */
	public function search()
	{
		$search = $this->request->getGet('search');
		$limit = $this->request->getGet('limit');
		$offset = $this->request->getGet('offset');
		$sort = $this->request->getGet('sort');
		$order = $this->request->getGet('order');

		$this->item_lib->set_item_location($this->request->getGet('stock_location'));

		$definition_names = $this->attribute->get_definitions_by_flags(Attribute::SHOW_IN_ITEMS);

		$filters = [
			'start_date' => $this->request->getGet('start_date'),
			'end_date' => $this->request->getGet('end_date'),
			'stock_location_id' => $this->item_lib->get_item_location(),
			'empty_upc' => FALSE,
			'low_inventory' => FALSE,
			'is_serialized' => FALSE,
			'no_description' => FALSE,
			'search_custom' => FALSE,
			'is_deleted' => FALSE,
			'temporary' => FALSE,
			'definition_ids' => array_keys($definition_names)
		];

		//Check if any filter is set in the multiselect dropdown
		$filledup = array_fill_keys($this->request->getGet('filters'), TRUE);	//TODO: filled up does not meet naming standards
		$filters = array_merge($filters, $filledup);
		$items = $this->item->search($search, $filters, $limit, $offset, $sort, $order);
		$total_rows = $this->item->get_found_rows($search, $filters);
		$data_rows = [];

		foreach($items->getResult() as $item)
		{
			$data_rows[] = $this->xss_clean(get_item_data_row($item));

			if($item->pic_filename !== NULL)
			{
				$this->update_pic_filename($item);
			}
		}

		echo json_encode (['total' => $total_rows, 'rows' => $data_rows]);
	}

	public function pic_thumb($pic_filename)
	{
		helper('file');

		$file_extension = pathinfo($pic_filename, PATHINFO_EXTENSION);
		$images = glob('./uploads/item_pics/' . $pic_filename);

		$base_path = './uploads/item_pics/' . pathinfo($pic_filename, PATHINFO_FILENAME);

		if(sizeof($images) > 0)
		{
			$image_path = $images[0];
			$thumb_path = $base_path . $this->image->thumb_marker . '.' . $file_extension;

			if(sizeof($images) < 2 && !file_exists($thumb_path))
			{
				$config['image_library'] = 'gd2';
				$config['source_image']  = $image_path;
				$config['maintain_ratio'] = TRUE;
				$config['create_thumb'] = TRUE;
				$config['width'] = 52;
				$config['height'] = 32;

				$this->image->initialize($config);
				$this->image->resize();

				$thumb_path = $this->image->full_dst_path;
			}
			$this->response->setContentType(get_mime_by_extension($thumb_path));
			$this->output->set_output(file_get_contents($thumb_path));	//TODO: figure out the CI4 counterpart to this.
		}
	}

	/*
	 Gives search suggestions based on what is being searched for
	 */
	public function suggest_search()
	{
		$options = [
			'search_custom' => $this->request->getPost('search_custom'),
			'is_deleted' => $this->request->getPost('is_deleted') !== NULL
		];

		$suggestions = $this->xss_clean($this->item->get_search_suggestions($this->request->getPostGet('term'),	$options, FALSE));

		echo json_encode($suggestions);
	}

	public function suggest()
	{
		$suggestions = $this->xss_clean($this->item->get_search_suggestions($this->request->getPostGet('term'),	['search_custom' => FALSE, 'is_deleted' => FALSE], TRUE));

		echo json_encode($suggestions);
	}

	public function suggest_low_sell()
	{
		$suggestions = $this->xss_clean($this->item->get_low_sell_suggestions($this->request->getPostGet('name')));

		echo json_encode($suggestions);
	}

	public function suggest_kits()
	{
		$suggestions = $this->xss_clean($this->item->get_kit_search_suggestions($this->request->getPostGet('term'), ['search_custom' => FALSE, 'is_deleted' => FALSE], TRUE));

		echo json_encode($suggestions);
	}

	/*
	 Gives search suggestions based on what is being searched for
	 */
	public function suggest_category()
	{
		$suggestions = $this->xss_clean($this->item->get_category_suggestions($this->request->getGet('term')));

		echo json_encode($suggestions);
	}

	/*
	 Gives search suggestions based on what is being searched for
	 */
	public function suggest_location()
	{
		$suggestions = $this->xss_clean($this->item->get_location_suggestions($this->request->getGet('term')));

		echo json_encode($suggestions);
	}

	public function get_row($item_ids)
	{
		$item_infos = $this->item->get_multiple_info(explode(':', $item_ids), $this->item_lib->get_item_location());

		$result = [];

		foreach($item_infos->getResult() as $item_info)
		{
			$result[$item_info->item_id] = $this->xss_clean(get_item_data_row($item_info));
		}

		echo json_encode($result);
	}

	public function view($item_id = NEW_ITEM)
	{
		if($item_id === NEW_ITEM)
		{
			$data = [];
		}

		//allow_temp_items is set in the index function of items.php or sales.php
		$data['allow_temp_item'] = $this->session->userdata('allow_temp_items');
		$data['item_tax_info'] = $this->xss_clean($this->Item_taxes->get_info($item_id));
		$data['default_tax_1_rate'] = '';
		$data['default_tax_2_rate'] = '';
		$data['item_kit_disabled'] = !$this->Employee->has_grant('item_kits', $this->Employee->get_logged_in_employee_info()->person_id);
		$data['definition_values'] = $this->attribute->get_attributes_by_item($item_id);
		$data['definition_names'] = $this->attribute->get_definition_names();

		foreach($data['definition_values'] as $definition_id => $definition)
		{
			unset($data['definition_names'][$definition_id]);
		}

		$item_info = $this->item->get_info($item_id);

		foreach(get_object_vars($item_info) as $property => $value)
		{
			$item_info->$property = $this->xss_clean($value);
		}

		if($data['allow_temp_item'] === 1)
		{
			if($item_id !== NEW_ITEM)
			{
				if($item_info->item_type != ITEM_TEMP)
				{
					$data['allow_temp_item'] = 0;
				}
			}
		}
		else
		{
			if($item_info->item_type == ITEM_TEMP)
			{
				$data['allow_temp_item'] = 1;
			}
		}

		$use_destination_based_tax = (boolean)$this->config->get('use_destination_based_tax');
		$data['include_hsn'] = $this->config->get('include_hsn') === '1';
		$data['category_dropdown'] = $this->config->get('category_dropdown');

		if($data['category_dropdown'] === '1')
		{
			$categories 		= ['' => lang('Items.none')];
			$category_options 	= $this->attribute->get_definition_values(CATEGORY_DEFINITION_ID);
			$category_options	= array_combine($category_options,$category_options);	//Overwrite indexes with values for saving in items table instead of attributes
			$data['categories'] = array_merge($categories,$category_options);

			$data['selected_category'] = $item_info->category;
		}

		if($item_id === NEW_ITEM)
		{
			$data['default_tax_1_rate'] = $this->config->get('default_tax_1_rate');
			$data['default_tax_2_rate'] = $this->config->get('default_tax_2_rate');

			$item_info->receiving_quantity = 1;
			$item_info->reorder_level = 1;
			$item_info->item_type = ITEM;	//Standard
			$item_info->item_id = $item_id;
			$item_info->stock_type = HAS_STOCK;
			$item_info->tax_category_id = NULL;
			$item_info->qty_per_pack = 1;
			$item_info->pack_name = lang('Items.default_pack_name');
			$data['hsn_code'] = '';

			if($use_destination_based_tax)
			{
				$item_info->tax_category_id = $this->config->get('default_tax_category');
			}
		}

		$data['standard_item_locked'] = (
			$data['item_kit_disabled']
			&& $item_info->item_type == ITEM_KIT
			&& !$data['allow_temp_item']
			&& !($this->config->get('derive_sale_quantity') === '1'));

		$data['item_info'] = $item_info;

		$suppliers = ['' => lang('Items.none')];

		foreach($this->Supplier->get_all()->getResultArray() as $row)
		{
			$suppliers[$this->xss_clean($row['person_id'])] = $this->xss_clean($row['company_name']);
		}

		$data['suppliers'] = $suppliers;
		$data['selected_supplier'] = $item_info->supplier_id;

		if($data['include_hsn'])
		{
			$data['hsn_code'] = $item_info->hsn_code;
		}
		else
		{
			$data['hsn_code'] = '';
		}

		if($use_destination_based_tax)
		{
			$data['use_destination_based_tax'] = TRUE;
			$tax_categories = [];

			foreach($this->Tax_category->get_all()->getResultArray() as $row)
			{
				$tax_categories[$this->xss_clean($row['tax_category_id'])] = $this->xss_clean($row['tax_category']);
			}

			$tax_category = '';

			if ($item_info->tax_category_id !== NULL)
			{
				$tax_category_info=$this->Tax_category->get_info($item_info->tax_category_id);
				$tax_category= $tax_category_info->tax_category;
			}

			$data['tax_categories'] = $tax_categories;
			$data['tax_category'] = $tax_category;
			$data['tax_category_id'] = $item_info->tax_category_id;
		}
		else
		{
			$data['use_destination_based_tax'] = FALSE;
			$data['tax_categories'] = [];
			$data['tax_category'] = '';
		}

		$data['logo_exists'] = $item_info->pic_filename !== '';
		$file_extension = pathinfo($item_info->pic_filename, PATHINFO_EXTENSION);

		if(empty($file_extension))
		{
			$images = glob("./uploads/item_pics/$item_info->pic_filename.*");
		}
		else
		{
			$images = glob("./uploads/item_pics/$item_info->pic_filename");
		}

		$data['image_path']	= sizeof($images) > 0 ? base_url($images[0]) : '';
		$stock_locations	= $this->stock_location->get_undeleted_all()->getResultArray();

		foreach($stock_locations as $location)
		{
			$location = $this->xss_clean($location);

			$quantity = $this->xss_clean($this->Item_quantity->get_item_quantity($item_id, $location['location_id'])->quantity);
			$quantity = ($item_id === NEW_ITEM) ? 0 : $quantity;
			$location_array[$location['location_id']] = ['location_name' => $location['location_name'], 'quantity' => $quantity];
			$data['stock_locations'] = $location_array;
		}

		$data['selected_low_sell_item_id'] = $item_info->low_sell_item_id;

		if($item_id !== NEW_ITEM && $item_info->item_id !== $item_info->low_sell_item_id)
		{
			$low_sell_item_info = $this->item->get_info($item_info->low_sell_item_id);
			$data['selected_low_sell_item'] = implode(NAME_SEPARATOR, [$low_sell_item_info->name, $low_sell_item_info->pack_name]);
		}
		else
		{
			$data['selected_low_sell_item'] = '';
		}

		echo view('items/form', $data);
	}

	public function inventory($item_id = NEW_ITEM)
	{
		$item_info = $this->item->get_info($item_id);

		foreach(get_object_vars($item_info) as $property => $value)
		{
			$item_info->$property = $this->xss_clean($value);
		}

		$data['item_info'] = $item_info;
		$data['stock_locations'] = [];
		$stock_locations = $this->stock_location->get_undeleted_all()->getResultArray();

		foreach($stock_locations as $location)
		{
			$location = $this->xss_clean($location);
			$quantity = $this->xss_clean($this->Item_quantity->get_item_quantity($item_id, $location['location_id'])->quantity);

			$data['stock_locations'][$location['location_id']] = $location['location_name'];
			$data['item_quantities'][$location['location_id']] = $quantity;
		}

		echo view('items/form_inventory', $data);
	}

	public function count_details($item_id = NEW_ITEM)
	{
		$item_info = $this->item->get_info($item_id);

		foreach(get_object_vars($item_info) as $property => $value)
		{
			$item_info->$property = $this->xss_clean($value);
		}

		$data['item_info'] = $item_info;
		$data['stock_locations'] = [];
		$stock_locations = $this->stock_location->get_undeleted_all()->getResultArray();

		foreach($stock_locations as $location)
		{
			$location = $this->xss_clean($location);
			$quantity = $this->xss_clean($this->Item_quantity->get_item_quantity($item_id, $location['location_id'])->quantity);

			$data['stock_locations'][$location['location_id']] = $location['location_name'];
			$data['item_quantities'][$location['location_id']] = $quantity;
		}

		echo view('items/form_count_details', $data);
	}

	public function generate_barcodes($item_ids)
	{
		$this->barcode_lib = new Barcode_lib();

		$item_ids = explode(':', $item_ids);
		$result = $this->item->get_multiple_info($item_ids, $this->item_lib->get_item_location())->getResultArray();
		$config = $this->barcode_lib->get_barcode_config();

		$data['barcode_config'] = $config;

		foreach($result as &$item)
		{
			$item = $this->xss_clean($item);

			if(empty($item['item_number']) && $this->config->get('barcode_generate_if_empty'))
			{
				$barcode_instance = Barcode_lib::barcode_instance($item, $config);
				$item['item_number'] = $barcode_instance->getData();
				$save_item = ['item_number' => $item['item_number']];

				$this->item->save($save_item, $item['item_id']);
			}
		}
		$data['items'] = $result;

		echo view('barcodes/barcode_sheet', $data);
	}

	public function attributes($item_id = NEW_ITEM)
	{
		$data['item_id'] = $item_id;
		$definition_ids = json_decode($this->request->getPost('definition_ids'), TRUE);
		$data['definition_values'] = $this->attribute->get_attributes_by_item($item_id) + $this->attribute->get_values_by_definitions($definition_ids);
		$data['definition_names'] = $this->attribute->get_definition_names();

		foreach($data['definition_values'] as $definition_id => $definition_value)
		{
			$attribute_value = $this->attribute->get_attribute_value($item_id, $definition_id);
			$attribute_id = (empty($attribute_value) || empty($attribute_value->attribute_id)) ? NULL : $attribute_value->attribute_id;
			$values = &$data['definition_values'][$definition_id];
			$values['attribute_id'] = $attribute_id;
			$values['attribute_value'] = $attribute_value;
			$values['selected_value'] = '';

			if ($definition_value['definition_type'] === DROPDOWN)
			{
				$values['values'] = $this->attribute->get_definition_values($definition_id);
				$link_value = $this->attribute->get_link_value($item_id, $definition_id);
				$values['selected_value'] = (empty($link_value)) ? '' : $link_value->attribute_id;
			}

			if (!empty($definition_ids[$definition_id]))
			{
				$values['selected_value'] = $definition_ids[$definition_id];
			}

			unset($data['definition_names'][$definition_id]);
		}

		echo view('attributes/item', $data);
	}

	public function bulk_edit()
	{
		$suppliers = ['' => lang('Items.none')];

		foreach($this->Supplier->get_all()->getResultArray() as $row)
		{
			$row = $this->xss_clean($row);
			$suppliers[$row['person_id']] = $row['company_name'];
		}

		$data['suppliers'] = $suppliers;
		$data['allow_alt_description_choices'] = [
			'' => lang('Items.do_nothing'),
			1  => lang('Items.change_all_to_allow_alt_desc'),
			0  => lang('Items.change_all_to_not_allow_allow_desc')
		];

		$data['serialization_choices'] = [
			'' => lang('Items.do_nothing'),
			1  => lang('Items.change_all_to_serialized'),
			0  => lang('Items.change_all_to_unserialized')
		];

		echo view('items/form_bulk', $data);
	}

	public function save($item_id = NEW_ITEM)
	{
		$upload_success = $this->handle_image_upload();
		$upload_data = $this->upload->data();
		$receiving_quantity = parse_quantity($this->request->getPost('receiving_quantity'));
		$item_type = $this->request->getPost('item_type') === NULL ? ITEM : $this->request->getPost('item_type');

		if($receiving_quantity === 0 && $item_type !== ITEM_TEMP)
		{
			$receiving_quantity = 1;
		}

		$default_pack_name = lang('Items.default_pack_name');

		//Save item data
		$item_data = [
			'name' => $this->request->getPost('name'),
			'description' => $this->request->getPost('description'),
			'category' => $this->request->getPost('category'),
			'item_type' => $item_type,
			'stock_type' => $this->request->getPost('stock_type') === NULL ? HAS_STOCK : intval($this->request->getPost('stock_type')),
			'supplier_id' => empty($this->request->getPost('supplier_id')) ? NULL : intval($this->request->getPost('supplier_id')),
			'item_number' => empty($this->request->getPost('item_number')) ? NULL : $this->request->getPost('item_number'),
			'cost_price' => parse_decimals($this->request->getPost('cost_price')),
			'unit_price' => parse_decimals($this->request->getPost('unit_price')),
			'reorder_level' => parse_quantity($this->request->getPost('reorder_level')),
			'receiving_quantity' => $receiving_quantity,
			'allow_alt_description' => $this->request->getPost('allow_alt_description') !== NULL,
			'is_serialized' => $this->request->getPost('is_serialized') !== NULL,
			'qty_per_pack' => $this->request->getPost('qty_per_pack') === NULL ? 1 : $this->request->getPost('qty_per_pack'),
			'pack_name' => $this->request->getPost('pack_name') === NULL ? $default_pack_name : $this->request->getPost('pack_name'),
			'low_sell_item_id' => $this->request->getPost('low_sell_item_id') === NULL ? $item_id : $this->request->getPost('low_sell_item_id'),
			'deleted' => $this->request->getPost('is_deleted') !== NULL,
			'hsn_code' => $this->request->getPost('hsn_code') === NULL ? '' : $this->request->getPost('hsn_code')
		];

		if($item_data['item_type'] == ITEM_TEMP)
		{
			$item_data['stock_type'] = HAS_NO_STOCK;
			$item_data['receiving_quantity'] = 0;
			$item_data['reorder_level'] = 0;
		}

		$tax_category_id = $this->request->getPost('tax_category_id');

		if(!isset($tax_category_id))
		{
			$item_data['tax_category_id'] = '';
		}
		else
		{
			$item_data['tax_category_id'] = empty($this->request->getPost('tax_category_id')) ? NULL : $this->request->getPost('tax_category_id');
		}

		if(!empty($upload_data['orig_name']))
		{
			if($this->xss_clean($upload_data['orig_name'], TRUE) === TRUE)
			{
				$item_data['pic_filename'] = $upload_data['orig_name'];
			}
		}

		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;

		if($this->item->save($item_data, $item_id))
		{
			$success = TRUE;
			$new_item = FALSE;

			if($item_id == NEW_ITEM)
			{
				$item_id = $item_data['item_id'];
				$new_item = TRUE;
			}

			$use_destination_based_tax = (boolean)$this->config->get('use_destination_based_tax');

			if(!$use_destination_based_tax)
			{
				$items_taxes_data = [];
				$tax_names = $this->request->getPost('tax_names');
				$tax_percents = $this->request->getPost('tax_percents');

				$tax_name_index = 0;

				foreach($tax_percents as $tax_percent)
				{
					$tax_percentage = parse_tax($tax_percent);

					if(is_numeric($tax_percentage))
					{
						$items_taxes_data[] = ['name' => $tax_names[$tax_name_index], 'percent' => $tax_percentage];
					}

					$tax_name_index++;
				}
				$success &= $this->Item_taxes->save($items_taxes_data, $item_id);
			}

			//Save item quantity
			$stock_locations = $this->stock_location->get_undeleted_all()->getResultArray();
			foreach($stock_locations as $location)
			{
				$updated_quantity = parse_quantity($this->request->getPost('quantity_' . $location['location_id']));

				if($item_data['item_type'] == ITEM_TEMP)
				{
					$updated_quantity = 0;
				}

				$location_detail = [
						'item_id' => $item_id,
						'location_id' => $location['location_id'],
						'quantity' => $updated_quantity
				];

				$item_quantity = $this->Item_quantity->get_item_quantity($item_id, $location['location_id']);

				if($item_quantity->quantity != $updated_quantity || $new_item)
				{
					$success &= $this->Item_quantity->save($location_detail, $item_id, $location['location_id']);

					$inv_data = [
						'trans_date' => date('Y-m-d H:i:s'),
						'trans_items' => $item_id,
						'trans_user' => $employee_id,
						'trans_location' => $location['location_id'],
						'trans_comment' => lang('Items.manually_editing_of_quantity'),
						'trans_inventory' => $updated_quantity - $item_quantity->quantity
					];

					$success &= $this->Inventory->insert($inv_data);
				}
			}

			// Save item attributes
			$attribute_links = $this->request->getPost('attribute_links') !== NULL ? $this->request->getPost('attribute_links') : [];
			$attribute_ids = $this->request->getPost('attribute_ids');

			$this->attribute->delete_link($item_id);

			foreach($attribute_links as $definition_id => $attribute_id)
			{
				$definition_type = $this->attribute->get_info($definition_id)->definition_type;

				if($definition_type !== DROPDOWN)
				{
					$attribute_id = $this->attribute->save_value($attribute_id, $definition_id, $item_id, $attribute_ids[$definition_id], $definition_type);
				}

				$this->attribute->save_link($item_id, $definition_id, $attribute_id);
			}

			if($success && $upload_success)
			{
				$message = $this->xss_clean(lang('Items.successful_' . ($new_item ? 'adding' : 'updating')) . ' ' . $item_data['name']);

				echo json_encode (['success' => TRUE, 'message' => $message, 'id' => $item_id]);
			}
			else
			{
				$message = $this->xss_clean($upload_success ? lang('Items.error_adding_updating') . ' ' . $item_data['name'] : strip_tags($this->upload->display_errors()));

				echo json_encode (['success' => FALSE, 'message' => $message, 'id' => $item_id]);
			}
		}
		else
		{
			$message = $this->xss_clean(lang('Items.error_adding_updating') . ' ' . $item_data['name']);

			echo json_encode (['success' => FALSE, 'message' => $message, 'id' => NEW_ITEM]);
		}
	}

	public function check_item_number()
	{
		$exists = $this->item->item_number_exists($this->request->getPost('item_number'), $this->request->getPost('item_id'));
		echo !$exists ? 'true' : 'false';
	}

	/*
	 If adding a new item check to see if an item kit with the same name as the item already exists.
	 */
	public function check_kit_exists()
	{
		if($this->request->getPost('item_number') === NEW_ITEM)
		{
			$exists = $this->Item_kit->item_kit_exists_for_name($this->request->getPost('name'));
		}
		else
		{
			$exists = FALSE;
		}
		echo !$exists ? 'true' : 'false';
	}

	/*
	 * Let files be uploaded with their original name
	 */
	private function handle_image_upload()
	{
	//Load upload library
		$config = [
			'upload_path' => './uploads/item_pics/',
			'allowed_types' => $this->config->get('image_allowed_types'),
			'max_size' => $this->config->get('image_max_size'),
			'max_width' => $this->config->get('image_max_width'),
			'max_height' => $this->config->get('image_max_height')
		];

		$this->upload = new Upload($config);
		$this->upload->do_upload('item_image');

		return strlen($this->upload->display_errors()) === 0 || !strcmp($this->upload->display_errors(), '<p>' . lang('upload_no_file_selected') . '</p>');
	}

	public function remove_logo($item_id)
	{
		$item_data = ['pic_filename' => NULL];
		$result = $this->item->save($item_data, $item_id);

		echo json_encode (['success' => $result]);
	}

	public function save_inventory($item_id = NEW_ITEM)
	{
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$cur_item_info = $this->item->get_info($item_id);
		$location_id = $this->request->getPost('stock_location');
		$inv_data = [
			'trans_date' => date('Y-m-d H:i:s'),
			'trans_items' => $item_id,
			'trans_user' => $employee_id,
			'trans_location' => $location_id,
			'trans_comment' => $this->request->getPost('trans_comment'),
			'trans_inventory' => parse_quantity($this->request->getPost('newquantity'))
		];

		$this->Inventory->insert($inv_data);

	//Update stock quantity
		$item_quantity = $this->Item_quantity->get_item_quantity($item_id, $location_id);
		$item_quantity_data = [
			'item_id' => $item_id,
			'location_id' => $location_id,
			'quantity' => $item_quantity->quantity + parse_quantity($this->request->getPost('newquantity'))
		];

		if($this->Item_quantity->save($item_quantity_data, $item_id, $location_id))
		{
			$message = $this->xss_clean(lang('Items.successful_updating') . " $cur_item_info->name");

			echo json_encode (['success' => TRUE, 'message' => $message, 'id' => $item_id]);
		}
		else
		{
			$message = $this->xss_clean(lang('Items.error_adding_updating') . " $cur_item_info->name");

			echo json_encode (['success' => FALSE, 'message' => $message, 'id' => NEW_ITEM]);
		}
	}

	public function bulk_update()
	{
		$items_to_update = $this->request->getPost('item_ids');
		$item_data = [];

		foreach($_POST as $key => $value)
		{
			//This field is nullable, so treat it differently
			if($key === 'supplier_id' && $value !== '')
			{
				$item_data[$key] = $value;
			}
			elseif($value !== '' && !(in_array($key, ['item_ids', 'tax_names', 'tax_percents'])))
			{
				$item_data[$key] = $value;
			}
		}

		//Item data could be empty if tax information is being updated
		if(empty($item_data) || $this->item->update_multiple($item_data, $items_to_update))
		{
			$items_taxes_data = [];
			$tax_names = $this->request->getPost('tax_names');
			$tax_percents = $this->request->getPost('tax_percents');
			$tax_updated = FALSE;

			foreach($tax_percents as $tax_percent)
			{
				if(!empty($tax_names[$tax_percent]) && is_numeric($tax_percents[$tax_percent]))
				{
					$tax_updated = TRUE;
					$items_taxes_data[] = ['name' => $tax_names[$tax_percent], 'percent' => $tax_percents[$tax_percent]];
				}
			}

			if($tax_updated)
			{
				$this->Item_taxes->save_multiple($items_taxes_data, $items_to_update);
			}

			echo json_encode (['success' => TRUE, 'message' => lang('Items.successful_bulk_edit'), 'id' => $this->xss_clean($items_to_update)]);
		}
		else
		{
			echo json_encode (['success' => FALSE, 'message' => lang('Items.error_updating_multiple')]);
		}
	}

	public function delete()
	{
		$items_to_delete = $this->request->getPost('ids');

		if($this->item->delete_list($items_to_delete))
		{
			$message = lang('Items.successful_deleted') . ' ' . count($items_to_delete) . ' ' . lang('Items.one_or_multiple');
			echo json_encode (['success' => TRUE, 'message' => $message]);
		}
		else
		{
			echo json_encode (['success' => FALSE, 'message' => lang('Items.cannot_be_deleted')]);
		}
	}

	public function generate_csv_file()
	{
		$name = 'import_items.csv';
		$allowed_locations = $this->stock_location->get_allowed_locations();
		$allowed_attributes = $this->attribute->get_definition_names(FALSE);
		$data = generate_import_items_csv($allowed_locations, $allowed_attributes);

		force_download($name, $data, TRUE);
	}

	public function csv_import()
	{
		echo view('items/form_csv_import', NULL);
	}

	/**
	 * Imports items from CSV formatted file.
	 */
	public function import_csv_file()
	{
		if($_FILES['file_path']['error'] !== UPLOAD_ERR_OK)
		{
			echo json_encode (['success' => FALSE, 'message' => lang('Items.csv_import_failed')]);
		}
		else
		{
			if(file_exists($_FILES['file_path']['tmp_name']))
			{
				set_time_limit(240);

				$failCodes = [];
				$csv_rows = get_csv_file($_FILES['file_path']['tmp_name']);
				$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
				$allowed_stock_locations = $this->stock_location->get_allowed_locations();
				$attribute_definition_names	= $this->attribute->get_definition_names();

				unset($attribute_definition_names[-1]);	//Removes the common_none_selected_text from the array

				foreach($attribute_definition_names as $definition_name)
				{
					$attribute_data[$definition_name] = $this->attribute->get_definition_by_name($definition_name)[0];

					if($attribute_data[$definition_name]['definition_type'] === DROPDOWN)
					{
						$attribute_data[$definition_name]['dropdown_values'] = $this->attribute->get_definition_values($attribute_data[$definition_name]['definition_id']);
					}
				}

				$this->db->transBegin();

				foreach($csv_rows as $key => $row)
				{
					$is_failed_row = FALSE;
					$item_id = $row['Id'];
					$is_update = !empty($item_id);
					$item_data = [
						'item_id' => $item_id,
						'name' => $row['Item Name'],
						'description' => $row['Description'],
						'category' => $row['Category'],
						'cost_price' => $row['Cost Price'],
						'unit_price' => $row['Unit Price'],
						'reorder_level' => $row['Reorder Level'],
						'deleted' => FALSE,
						'hsn_code' => $row['HSN'],
						'pic_filename' => $row['Image']
					];

					if(!empty($row['supplier_id']))
					{
						$item_data['supplier_id'] = $this->Supplier->exists($row['Supplier ID']) ? $row['Supplier ID'] : NULL;
					}

					if($is_update)
					{
						$item_data['allow_alt_description'] = empty($row['Allow Alt Description']) ? NULL : $row['Allow Alt Description'];
						$item_data['is_serialized'] = empty($row['Item has Serial Number']) ? NULL : $row['Item has Serial Number'];
					}
					else
					{
						$item_data['allow_alt_description'] = empty($row['Allow Alt Description'])? '0' : '1';
						$item_data['is_serialized'] = empty($row['Item has Serial Number'])? '0' : '1';
					}

					if(!empty($row['Barcode']))
					{
						$item_data['item_number'] = $row['Barcode'];
						$is_failed_row = $this->item->item_number_exists($item_data['item_number']);
					}

					if(!$is_failed_row)
					{
						$is_failed_row = $this->data_error_check($row, $item_data, $allowed_stock_locations, $attribute_definition_names, $attribute_data);
					}

					//Remove FALSE, NULL, '' and empty strings but keep 0
					$item_data = array_filter($item_data, 'strlen');

					if(!$is_failed_row && $this->item->save($item_data, $item_id))
					{
						$this->save_tax_data($row, $item_data);
						$this->save_inventory_quantities($row, $item_data, $allowed_stock_locations, $employee_id);
						$is_failed_row = $this->save_attribute_data($row, $item_data, $attribute_data);

						if($is_update)
						{
							$item_data = array_merge($item_data, get_object_vars($this->item->get_info_by_id_or_number($item_id)));
						}
					}
					else
					{
						$failed_row = $key+2;
						$failCodes[] = $failed_row;
						log_message('ERROR',"CSV Item import failed on line $failed_row. This item was not imported.");
					}

					unset($csv_rows[$key]);
				}

				$csv_rows = NULL;

				if(count($failCodes) > 0)
				{
					$message = lang('Items.csv_import_partially_failed', count($failCodes), implode(', ', $failCodes));
					$this->db->transRollback();
					echo json_encode (['success' => FALSE, 'message' => $message]);
				}
				else
				{
					$this->db->transCommit();

					echo json_encode (['success' => TRUE, 'message' => lang('Items.csv_import_success')]);
				}
			}
			else
			{
				echo json_encode (['success' => FALSE, 'message' => lang('Items.csv_import_nodata_wrongformat')]);
			}
		}
	}

	/**
	 * Checks the entire line of data in an import file for errors
	 *
	 * @param	array	$line
	 * @param 	array	$item_data
	 *
	 * @return	bool	Returns FALSE if all data checks out and TRUE when there is an error in the data
	 */
	private function data_error_check($row, $item_data, $allowed_locations, $definition_names, $attribute_data)
	{
		$item_id = $row['Id'];
		$is_update = $item_id ? TRUE : FALSE;

		//Check for empty required fields
		$check_for_empty = [
			'name' => $item_data['name'],
			'category' => $item_data['category'],
			'unit_price' => $item_data['unit_price']
		];

		foreach($check_for_empty as $key => $val)
		{
			if (empty($val) && !$is_update)
			{
				log_message('Error',"Empty required value in $key.");
				return TRUE;
			}
		}

		if(!$is_update)
		{
			$item_data['cost_price'] = empty($item_data['cost_price']) ? 0 : $item_data['cost_price'];	//Allow for zero wholesale price
		}
		else
		{
			if(!$this->item->exists($item_id))
			{
				log_message('Error',"non-existent item_id: '$item_id' when either existing item_id or no item_id is required.");
				return TRUE;
			}
		}

		//Build array of fields to check for numerics
		$check_for_numeric_values = [
			'cost_price' => $item_data['cost_price'],
			'unit_price' => $item_data['unit_price'],
			'reorder_level' => $item_data['reorder_level'],
			'supplier_id' => $item_data['supplier_id'],
			'Tax 1 Percent' => $row['Tax 1 Percent'],
			'Tax 2 Percent' => $row['Tax 2 Percent']
		];

		foreach($allowed_locations as $location_name)
		{
			$check_for_numeric_values[] = $row["location_$location_name"];
		}

		//Check for non-numeric values which require numeric
		foreach($check_for_numeric_values as $key => $value)
		{
			if(!is_numeric($value) && !empty($value))
			{
				log_message('Error',"non-numeric: '$value' for '$key' when numeric is required");
				return TRUE;
			}
		}

		//Check Attribute Data
		foreach($definition_names as $definition_name)
		{
			if(!empty($row["attribute_$definition_name"]))
			{
				$definition_type = $attribute_data[$definition_name]['definition_type'];
				$attribute_value = $row["attribute_$definition_name"];

				switch($definition_type)
				{
					case DROPDOWN:
						$dropdown_values = $attribute_data[$definition_name]['dropdown_values'];
						$dropdown_values[] = '';

						if(!empty($attribute_value) && in_array($attribute_value, $dropdown_values) === FALSE)
						{
							log_message('Error',"Value: '$attribute_value' is not an acceptable DROPDOWN value");
							return TRUE;
						}
						break;
					case DECIMAL:
						if(!is_numeric($attribute_value) && !empty($attribute_value))
						{
							log_message('Error',"'$attribute_value' is not an acceptable DECIMAL value");
							return TRUE;
						}
						break;
					case DATE:
						if(valid_date($attribute_value) === FALSE && !empty($attribute_value))
						{
							log_message('Error',"'$attribute_value' is not an acceptable DATE value. The value must match the set locale.");
							return TRUE;
						}
						break;
				}
			}
		}

		return FALSE;
	}

	/**
	 * Saves attribute data found in the CSV import.
	 *
	 * @param line
	 * @param failCodes
	 * @param attribute_data
	 */
	private function save_attribute_data($row, $item_data, $definitions)
	{
		foreach($definitions as $definition)
		{
			$attribute_name = $definition['definition_name'];
			$attribute_value = $row["attribute_$attribute_name"];

			//Create attribute value
			if(!empty($attribute_value) || $attribute_value === '0')
			{
				if($definition['definition_type'] === CHECKBOX)
				{
					$checkbox_is_unchecked = (strcasecmp($attribute_value,'FALSE') === 0 || $attribute_value === '0');
					$attribute_value = $checkbox_is_unchecked ? '0' : '1';

					$attribute_id = $this->store_attribute_value($attribute_value, $definition, $item_data['item_id']);
				}
				elseif(!empty($attribute_value))
				{
					$attribute_id = $this->store_attribute_value($attribute_value, $definition, $item_data['item_id']);
				}
				else
				{
					return TRUE;
				}

				if($attribute_id === FALSE)
				{
					return TRUE;
				}
			}
		}
	}

	/**
	 * Saves the attribute_value and attribute_link if necessary
	 */
	private function store_attribute_value($value, $attribute_data, $item_id)
	{
		$attribute_id = $this->attribute->value_exists($value, $attribute_data['definition_type']);

		$this->attribute->delete_link($item_id, $attribute_data['definition_id']);

		if($attribute_id === FALSE)
		{
			$attribute_id = $this->attribute->save_value($value, $attribute_data['definition_id'], $item_id, FALSE, $attribute_data['definition_type']);
		}
		else if($this->attribute->save_link($item_id, $attribute_data['definition_id'], $attribute_id) === FALSE)
		{
			return FALSE;
		}
		return $attribute_id;
	}

	/**
	 * Saves inventory quantities for the row in the appropriate stock locations.
	 *
	 * @param	array	line
	 * @param			item_data
	 */
	private function save_inventory_quantities($row, $item_data, $allowed_locations, $employee_id)
	{
		//Quantities & Inventory Section
		$comment = lang('Items.inventory_CSV_import_quantity');
		$is_update = $row['Id'] ? TRUE : FALSE;

		foreach($allowed_locations as $location_id => $location_name)
		{
			$item_quantity_data = ['item_id' => $item_data['item_id'], 'location_id' => $location_id];

			$csv_data = [
				'trans_items' => $item_data['item_id'],
				'trans_user' => $employee_id,
				'trans_comment' => $comment,
				'trans_location' => $location_id
			];

			if(!empty($row["location_$location_name"]) || $row["location_$location_name"] === '0')
			{
				$item_quantity_data['quantity'] = $row["location_$location_name"];
				$this->Item_quantity->save($item_quantity_data, $item_data['item_id'], $location_id);

				$csv_data['trans_inventory'] = $row["location_$location_name"];
				$this->Inventory->insert($csv_data);
			}
			elseif($is_update)
			{
				return;
			}
			else
			{
				$item_quantity_data['quantity'] = 0;
				$this->Item_quantity->save($item_quantity_data, $item_data['item_id'], $location_id);

				$csv_data['trans_inventory'] = 0;
				$this->Inventory->insert($csv_data);
			}
		}
	}

	/**
	 * Saves the tax data found in the line of the CSV items import file
	 *
	 * @param	array	line
	 */
	private function save_tax_data($row, $item_data)
	{
		$items_taxes_data = [];

		if(is_numeric($row['Tax 1 Percent']) && $row['Tax 1 Name'] !== '')
		{
			$items_taxes_data[] = ['name' => $row['Tax 1 Name'], 'percent' => $row['Tax 1 Percent']];
		}

		if(is_numeric($row['Tax 2 Percent']) && $row['Tax 2 Name'] !== '')
		{
			$items_taxes_data[] = ['name' => $row['Tax 2 Name'], 'percent' => $row['Tax 2 Percent']];
		}

		if(isset($items_taxes_data))
		{
			$this->Item_taxes->save($items_taxes_data, $item_data['item_id']);
		}
	}

	/**
	 * Guess whether file extension is not in the table field, if it isn't, then it's an old-format (formerly pic_id) field, so we guess the right filename and update the table
	 *
	 * @param $item int item to update
	 */
	private function update_pic_filename($item)
	{
		$filename = pathinfo($item->pic_filename, PATHINFO_FILENAME);

		// if the field is empty there's nothing to check
		if(!empty($filename))
		{
			$ext = pathinfo($item->pic_filename, PATHINFO_EXTENSION);
			if(empty($ext))
			{
				$images = glob("./uploads/item_pics/$item->pic_filename.*");
				if(sizeof($images) > 0)
				{
					$new_pic_filename = pathinfo($images[0], PATHINFO_BASENAME);
					$item_data = ['pic_filename' => $new_pic_filename];
					$this->item->save($item_data, $item->item_id);
				}
			}
		}
	}
}
?>
