<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once('Secure_Controller.php');

class Items extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('items');

		$this->load->library('item_lib');
	}

	public function index()
	{
		$this->session->set_userdata('allow_temp_items', 0);

		$data['table_headers'] = $this->xss_clean(get_items_manage_table_headers());
		$data['stock_location'] = $this->xss_clean($this->item_lib->get_item_location());
		$data['stock_locations'] = $this->xss_clean($this->Stock_location->get_allowed_locations());

		//Filters that will be loaded in the multiselect dropdown
		$data['filters'] = array(
			'empty_upc' => $this->lang->line('items_empty_upc_items'),
			'low_inventory' => $this->lang->line('items_low_inventory_items'),
			'is_serialized' => $this->lang->line('items_serialized_items'),
			'no_description' => $this->lang->line('items_no_description_items'),
			'search_custom' => $this->lang->line('items_search_attributes'),
			'is_deleted' => $this->lang->line('items_is_deleted'),
			'temporary' => $this->lang->line('items_temp'));

		$this->load->view('items/manage', $data);
	}

	/*
	 * Returns Items table data rows. This will be called with AJAX.
	 */
	public function search()
	{
		$search = $this->input->get('search');
		$limit = $this->input->get('limit');
		$offset = $this->input->get('offset');
		$sort = $this->input->get('sort');
		$order = $this->input->get('order');

		$this->item_lib->set_item_location($this->input->get('stock_location'));

		$definition_names = $this->Attribute->get_definitions_by_flags(Attribute::SHOW_IN_ITEMS);

		$filters = array(
			'start_date' => $this->input->get('start_date'),
			'end_date' => $this->input->get('end_date'),
			'stock_location_id' => $this->item_lib->get_item_location(),
			'empty_upc' => FALSE,
			'low_inventory' => FALSE,
			'is_serialized' => FALSE,
			'no_description' => FALSE,
			'search_custom' => FALSE,
			'is_deleted' => FALSE,
			'temporary' => FALSE,
			'definition_ids' => array_keys($definition_names));

		//Check if any filter is set in the multiselect dropdown
		$filledup = array_fill_keys($this->input->get('filters'), TRUE);
		$filters = array_merge($filters, $filledup);
		$items = $this->Item->search($search, $filters, $limit, $offset, $sort, $order);
		$total_rows = $this->Item->get_found_rows($search, $filters);
		$data_rows = [];

		foreach($items->result() as $item)
		{
			$data_rows[] = $this->xss_clean(get_item_data_row($item));

			if($item->pic_filename !== NULL)
			{
				$this->update_pic_filename($item);
			}
		}

		echo json_encode(array('total' => $total_rows, 'rows' => $data_rows));
	}

	public function pic_thumb($pic_filename)
	{
		$this->load->helper('file');
		$this->load->library('image_lib');

		$file_extension = pathinfo($pic_filename, PATHINFO_EXTENSION);
		$images = glob('./uploads/item_pics/' . $pic_filename);

		$base_path = './uploads/item_pics/' . pathinfo($pic_filename, PATHINFO_FILENAME);

		if(sizeof($images) > 0)
		{
			$image_path = $images[0];
			$thumb_path = $base_path . $this->image_lib->thumb_marker . '.' . $file_extension;

			if(sizeof($images) < 2 && !file_exists($thumb_path))
			{
				$config['image_library'] = 'gd2';
				$config['source_image']  = $image_path;
				$config['maintain_ratio'] = TRUE;
				$config['create_thumb'] = TRUE;
				$config['width'] = 52;
				$config['height'] = 32;

				$this->image_lib->initialize($config);
				$this->image_lib->resize();

				$thumb_path = $this->image_lib->full_dst_path;
			}
			$this->output->set_content_type(get_mime_by_extension($thumb_path));
			$this->output->set_output(file_get_contents($thumb_path));
		}
	}

	/*
	 Gives search suggestions based on what is being searched for
	 */
	public function suggest_search()
	{
		$options = array(
			'search_custom' => $this->input->post('search_custom'),
			'is_deleted' => $this->input->post('is_deleted') !== NULL);
		$suggestions = $this->xss_clean($this->Item->get_search_suggestions($this->input->post_get('term'),	$options, FALSE));

		echo json_encode($suggestions);
	}

	public function suggest()
	{
		$suggestions = $this->xss_clean($this->Item->get_search_suggestions($this->input->post_get('term'),
			array('search_custom' => FALSE, 'is_deleted' => FALSE), TRUE));

		echo json_encode($suggestions);
	}

	public function suggest_low_sell()
	{
		$suggestions = $this->xss_clean($this->Item->get_low_sell_suggestions($this->input->post_get('name')));

		echo json_encode($suggestions);
	}

	public function suggest_kits()
	{
		$suggestions = $this->xss_clean($this->Item->get_kit_search_suggestions($this->input->post_get('term'),
			array('search_custom' => FALSE, 'is_deleted' => FALSE), TRUE));

		echo json_encode($suggestions);
	}

	/*
	 Gives search suggestions based on what is being searched for
	 */
	public function suggest_category()
	{
		$suggestions = $this->xss_clean($this->Item->get_category_suggestions($this->input->get('term')));

		echo json_encode($suggestions);
	}

	/*
	 Gives search suggestions based on what is being searched for
	 */
	public function suggest_location()
	{
		$suggestions = $this->xss_clean($this->Item->get_location_suggestions($this->input->get('term')));

		echo json_encode($suggestions);
	}

	public function get_row($item_ids)
	{
		$item_infos = $this->Item->get_multiple_info(explode(':', $item_ids), $this->item_lib->get_item_location());

		$result = [];

		foreach($item_infos->result() as $item_info)
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
		$data['definition_values'] = $this->Attribute->get_attributes_by_item($item_id);
		$data['definition_names'] = $this->Attribute->get_definition_names();

		foreach($data['definition_values'] as $definition_id => $definition)
		{
			unset($data['definition_names'][$definition_id]);
		}

		$item_info = $this->Item->get_info($item_id);

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

		$use_destination_based_tax = (boolean)$this->config->item('use_destination_based_tax');
		$data['include_hsn'] = $this->config->item('include_hsn') === '1';
		$data['category_dropdown'] = $this->config->item('category_dropdown');

		if($data['category_dropdown'] === '1')
		{
			$categories 		= array('' => $this->lang->line('items_none'));
			$category_options 	= $this->Attribute->get_definition_values(CATEGORY_DEFINITION_ID);
			$category_options	= array_combine($category_options,$category_options);	//Overwrite indexes with values for saving in items table instead of attributes
			$data['categories'] = array_merge($categories,$category_options);

			$data['selected_category'] = $item_info->category;
		}

		if($item_id === NEW_ITEM)
		{
			$data['default_tax_1_rate'] = $this->config->item('default_tax_1_rate');
			$data['default_tax_2_rate'] = $this->config->item('default_tax_2_rate');

			$item_info->receiving_quantity = 1;
			$item_info->reorder_level = 1;
			$item_info->item_type = ITEM;	//Standard
			$item_info->item_id = $item_id;
			$item_info->stock_type = HAS_STOCK;
			$item_info->tax_category_id = NULL;
			$item_info->qty_per_pack = 1;
			$item_info->pack_name = $this->lang->line('items_default_pack_name');
			$data['hsn_code'] = '';

			if($use_destination_based_tax)
			{
				$item_info->tax_category_id = $this->config->item('default_tax_category');
			}
		}

		$data['standard_item_locked'] = (
			$data['item_kit_disabled']
			&& $item_info->item_type == ITEM_KIT
			&& !$data['allow_temp_item']
			&& !($this->config->item('derive_sale_quantity') === '1'));

		$data['item_info'] = $item_info;

		$suppliers = array('' => $this->lang->line('items_none'));

		foreach($this->Supplier->get_all()->result_array() as $row)
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

			foreach($this->Tax_category->get_all()->result_array() as $row)
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
		$stock_locations	= $this->Stock_location->get_undeleted_all()->result_array();

		foreach($stock_locations as $location)
		{
			$location = $this->xss_clean($location);

			$quantity = $this->xss_clean($this->Item_quantity->get_item_quantity($item_id, $location['location_id'])->quantity);
			$quantity = ($item_id === NEW_ITEM) ? 0 : $quantity;
			$location_array[$location['location_id']] = array('location_name' => $location['location_name'], 'quantity' => $quantity);
			$data['stock_locations'] = $location_array;
		}

		$data['selected_low_sell_item_id'] = $item_info->low_sell_item_id;

		if($item_id !== NEW_ITEM && $item_info->item_id !== $item_info->low_sell_item_id)
		{
			$low_sell_item_info = $this->Item->get_info($item_info->low_sell_item_id);
			$data['selected_low_sell_item'] = implode(NAME_SEPARATOR, array($low_sell_item_info->name, $low_sell_item_info->pack_name));
		}
		else
		{
			$data['selected_low_sell_item'] = '';
		}

		$this->load->view('items/form', $data);
	}

	public function inventory($item_id = NEW_ITEM)
	{
		$item_info = $this->Item->get_info($item_id);

		foreach(get_object_vars($item_info) as $property => $value)
		{
			$item_info->$property = $this->xss_clean($value);
		}

		$data['item_info'] = $item_info;
		$data['stock_locations'] = [];
		$stock_locations = $this->Stock_location->get_undeleted_all()->result_array();

		foreach($stock_locations as $location)
		{
			$location = $this->xss_clean($location);
			$quantity = $this->xss_clean($this->Item_quantity->get_item_quantity($item_id, $location['location_id'])->quantity);

			$data['stock_locations'][$location['location_id']] = $location['location_name'];
			$data['item_quantities'][$location['location_id']] = $quantity;
		}

		$this->load->view('items/form_inventory', $data);
	}

	public function count_details($item_id = NEW_ITEM)
	{
		$item_info = $this->Item->get_info($item_id);

		foreach(get_object_vars($item_info) as $property => $value)
		{
			$item_info->$property = $this->xss_clean($value);
		}

		$data['item_info'] = $item_info;
		$data['stock_locations'] = [];
		$stock_locations = $this->Stock_location->get_undeleted_all()->result_array();

		foreach($stock_locations as $location)
		{
			$location = $this->xss_clean($location);
			$quantity = $this->xss_clean($this->Item_quantity->get_item_quantity($item_id, $location['location_id'])->quantity);

			$data['stock_locations'][$location['location_id']] = $location['location_name'];
			$data['item_quantities'][$location['location_id']] = $quantity;
		}

		$this->load->view('items/form_count_details', $data);
	}

	public function generate_barcodes($item_ids)
	{
		$this->load->library('barcode_lib');

		$item_ids = explode(':', $item_ids);
		$result = $this->Item->get_multiple_info($item_ids, $this->item_lib->get_item_location())->result_array();
		$config = $this->barcode_lib->get_barcode_config();

		$data['barcode_config'] = $config;

		foreach($result as &$item)
		{
			$item = $this->xss_clean($item);

			if(empty($item['item_number']) && $this->config->item('barcode_generate_if_empty'))
			{
				$barcode_instance = Barcode_lib::barcode_instance($item, $config);
				$item['item_number'] = $barcode_instance->getData();
				$save_item = array('item_number' => $item['item_number']);

				$this->Item->save($save_item, $item['item_id']);
			}
		}
		$data['items'] = $result;

		$this->load->view('barcodes/barcode_sheet', $data);
	}

	public function attributes($item_id = NEW_ITEM)
	{
		$data['item_id'] = $item_id;
		$definition_ids = json_decode($this->input->post('definition_ids'), TRUE);
		$data['definition_values'] = $this->Attribute->get_attributes_by_item($item_id) + $this->Attribute->get_values_by_definitions($definition_ids);
		$data['definition_names'] = $this->Attribute->get_definition_names();

		foreach($data['definition_values'] as $definition_id => $definition_value)
		{
			$attribute_value = $this->Attribute->get_attribute_value($item_id, $definition_id);
			$attribute_id = (empty($attribute_value) || empty($attribute_value->attribute_id)) ? NULL : $attribute_value->attribute_id;
			$values = &$data['definition_values'][$definition_id];
			$values['attribute_id'] = $attribute_id;
			$values['attribute_value'] = $attribute_value;
			$values['selected_value'] = '';

			if ($definition_value['definition_type'] === DROPDOWN)
			{
				$values['values'] = $this->Attribute->get_definition_values($definition_id);
				$link_value = $this->Attribute->get_link_value($item_id, $definition_id);
				$values['selected_value'] = (empty($link_value)) ? '' : $link_value->attribute_id;
			}

			if (!empty($definition_ids[$definition_id]))
			{
				$values['selected_value'] = $definition_ids[$definition_id];
			}

			unset($data['definition_names'][$definition_id]);
		}

		$this->load->view('attributes/item', $data);
	}

	public function bulk_edit()
	{
		$suppliers = array('' => $this->lang->line('items_none'));

		foreach($this->Supplier->get_all()->result_array() as $row)
		{
			$row = $this->xss_clean($row);
			$suppliers[$row['person_id']] = $row['company_name'];
		}

		$data['suppliers'] = $suppliers;
		$data['allow_alt_description_choices'] = array(
			'' => $this->lang->line('items_do_nothing'),
			1  => $this->lang->line('items_change_all_to_allow_alt_desc'),
			0  => $this->lang->line('items_change_all_to_not_allow_allow_desc'));

		$data['serialization_choices'] = array(
			'' => $this->lang->line('items_do_nothing'),
			1  => $this->lang->line('items_change_all_to_serialized'),
			0  => $this->lang->line('items_change_all_to_unserialized'));

		$this->load->view('items/form_bulk', $data);
	}

	public function save($item_id = NEW_ITEM)
	{
		$upload_success = $this->handle_image_upload();
		$upload_data = $this->upload->data();
		$receiving_quantity = parse_quantity($this->input->post('receiving_quantity'));
		$item_type = $this->input->post('item_type') === NULL ? ITEM : $this->input->post('item_type');

		if($receiving_quantity === 0 && $item_type !== ITEM_TEMP)
		{
			$receiving_quantity = 1;
		}

		$default_pack_name = $this->lang->line('items_default_pack_name');

		//Save item data
		$item_data = array(
			'name' => $this->input->post('name'),
			'description' => $this->input->post('description'),
			'category' => $this->input->post('category'),
			'item_type' => $item_type,
			'stock_type' => $this->input->post('stock_type') === NULL ? HAS_STOCK : intval($this->input->post('stock_type')),
			'supplier_id' => empty($this->input->post('supplier_id')) ? NULL : intval($this->input->post('supplier_id')),
			'item_number' => empty($this->input->post('item_number')) ? NULL : $this->input->post('item_number'),
			'cost_price' => parse_decimals($this->input->post('cost_price')),
			'unit_price' => parse_decimals($this->input->post('unit_price')),
			'reorder_level' => parse_quantity($this->input->post('reorder_level')),
			'receiving_quantity' => $receiving_quantity,
			'allow_alt_description' => $this->input->post('allow_alt_description') !== NULL,
			'is_serialized' => $this->input->post('is_serialized') !== NULL,
			'qty_per_pack' => $this->input->post('qty_per_pack') === NULL ? 1 : $this->input->post('qty_per_pack'),
			'pack_name' => $this->input->post('pack_name') === NULL ? $default_pack_name : $this->input->post('pack_name'),
			'low_sell_item_id' => $this->input->post('low_sell_item_id') === NULL ? $item_id : $this->input->post('low_sell_item_id'),
			'deleted' => $this->input->post('is_deleted') !== NULL,
			'hsn_code' => $this->input->post('hsn_code') === NULL ? '' : $this->input->post('hsn_code')
		);

		if($item_data['item_type'] == ITEM_TEMP)
		{
			$item_data['stock_type'] = HAS_NO_STOCK;
			$item_data['receiving_quantity'] = 0;
			$item_data['reorder_level'] = 0;
		}

		$tax_category_id = $this->input->post('tax_category_id');

		if(!isset($tax_category_id))
		{
			$item_data['tax_category_id'] = '';
		}
		else
		{
			$item_data['tax_category_id'] = empty($this->input->post('tax_category_id')) ? NULL : $this->input->post('tax_category_id');
		}

		if(!empty($upload_data['orig_name']))
		{
			if($this->xss_clean($upload_data['orig_name'], TRUE) === TRUE)
			{
				$item_data['pic_filename'] = $upload_data['orig_name'];
			}
		}

		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;

		if($this->Item->save($item_data, $item_id))
		{
			$success = TRUE;
			$new_item = FALSE;

			if($item_id == NEW_ITEM)
			{
				$item_id = $item_data['item_id'];
				$new_item = TRUE;
			}

			$use_destination_based_tax = (boolean)$this->config->item('use_destination_based_tax');

			if(!$use_destination_based_tax)
			{
				$items_taxes_data = [];
				$tax_names = $this->input->post('tax_names');
				$tax_percents = $this->input->post('tax_percents');

				$tax_name_index = 0;

				foreach($tax_percents as $tax_percent)
				{
					$tax_percentage = parse_tax($tax_percent);

					if(is_numeric($tax_percentage))
					{
						$items_taxes_data[] = array('name' => $tax_names[$tax_name_index], 'percent' => $tax_percentage);
					}

					$tax_name_index++;
				}
				$success &= $this->Item_taxes->save($items_taxes_data, $item_id);
			}

			//Save item quantity
			$stock_locations = $this->Stock_location->get_undeleted_all()->result_array();
			foreach($stock_locations as $location)
			{
				$updated_quantity = parse_quantity($this->input->post('quantity_' . $location['location_id']));

				if($item_data['item_type'] == ITEM_TEMP)
				{
					$updated_quantity = 0;
				}

				$location_detail = array(
						'item_id' => $item_id,
						'location_id' => $location['location_id'],
						'quantity' => $updated_quantity);

				$item_quantity = $this->Item_quantity->get_item_quantity($item_id, $location['location_id']);

				if($item_quantity->quantity != $updated_quantity || $new_item)
				{
					$success &= $this->Item_quantity->save($location_detail, $item_id, $location['location_id']);

					$inv_data = array(
						'trans_date' => date('Y-m-d H:i:s'),
						'trans_items' => $item_id,
						'trans_user' => $employee_id,
						'trans_location' => $location['location_id'],
						'trans_comment' => $this->lang->line('items_manually_editing_of_quantity'),
						'trans_inventory' => $updated_quantity - $item_quantity->quantity
					);

					$success &= $this->Inventory->insert($inv_data);
				}
			}

			// Save item attributes
			$attribute_links = $this->input->post('attribute_links') !== NULL ? $this->input->post('attribute_links') : [];
			$attribute_ids = $this->input->post('attribute_ids');

			$this->Attribute->delete_link($item_id);

			foreach($attribute_links as $definition_id => $attribute_id)
			{
				$definition_type = $this->Attribute->get_info($definition_id)->definition_type;

				if($definition_type !== DROPDOWN)
				{
					$attribute_id = $this->Attribute->save_value($attribute_id, $definition_id, $item_id, $attribute_ids[$definition_id], $definition_type);
				}

				$this->Attribute->save_link($item_id, $definition_id, $attribute_id);
			}

			if($success && $upload_success)
			{
				$message = $this->xss_clean($this->lang->line('items_successful_' . ($new_item ? 'adding' : 'updating')) . ' ' . $item_data['name']);

				echo json_encode(array('success' => TRUE, 'message' => $message, 'id' => $item_id));
			}
			else
			{
				$message = $this->xss_clean($upload_success ? $this->lang->line('items_error_adding_updating') . ' ' . $item_data['name'] : strip_tags($this->upload->display_errors()));

				echo json_encode(array('success' => FALSE, 'message' => $message, 'id' => $item_id));
			}
		}
		else
		{
			$message = $this->xss_clean($this->lang->line('items_error_adding_updating') . ' ' . $item_data['name']);

			echo json_encode(array('success' => FALSE, 'message' => $message, 'id' => NEW_ITEM));
		}
	}

	public function check_item_number()
	{
		$exists = $this->Item->item_number_exists($this->input->post('item_number'), $this->input->post('item_id'));
		echo !$exists ? 'true' : 'false';
	}

	/*
	 If adding a new item check to see if an item kit with the same name as the item already exists.
	 */
	public function check_kit_exists()
	{
		if($this->input->post('item_number') === NEW_ITEM)
		{
			$exists = $this->Item_kit->item_kit_exists_for_name($this->input->post('name'));
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
		$config = array('upload_path' => './uploads/item_pics/',
			'allowed_types' => $this->config->item('image_allowed_types'),
			'max_size' => $this->config->item('image_max_size'),
			'max_width' => $this->config->item('image_max_width'),
			'max_height' => $this->config->item('image_max_height'));

		$this->load->library('upload', $config);
		$this->upload->do_upload('item_image');

		return strlen($this->upload->display_errors()) === 0 || !strcmp($this->upload->display_errors(), '<p>' . $this->lang->line('upload_no_file_selected') . '</p>');
	}

	public function remove_logo($item_id)
	{
		$item_data = array('pic_filename' => NULL);
		$result = $this->Item->save($item_data, $item_id);

		echo json_encode(array('success' => $result));
	}

	public function save_inventory($item_id = NEW_ITEM)
	{
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$cur_item_info = $this->Item->get_info($item_id);
		$location_id = $this->input->post('stock_location');
		$inv_data = array(
			'trans_date' => date('Y-m-d H:i:s'),
			'trans_items' => $item_id,
			'trans_user' => $employee_id,
			'trans_location' => $location_id,
			'trans_comment' => $this->input->post('trans_comment'),
			'trans_inventory' => parse_quantity($this->input->post('newquantity'))
		);

		$this->Inventory->insert($inv_data);

	//Update stock quantity
		$item_quantity = $this->Item_quantity->get_item_quantity($item_id, $location_id);
		$item_quantity_data = array(
			'item_id' => $item_id,
			'location_id' => $location_id,
			'quantity' => $item_quantity->quantity + parse_quantity($this->input->post('newquantity'))
		);

		if($this->Item_quantity->save($item_quantity_data, $item_id, $location_id))
		{
			$message = $this->xss_clean($this->lang->line('items_successful_updating') . " $cur_item_info->name");

			echo json_encode(array('success' => TRUE, 'message' => $message, 'id' => $item_id));
		}
		else
		{
			$message = $this->xss_clean($this->lang->line('items_error_adding_updating') . " $cur_item_info->name");

			echo json_encode(array('success' => FALSE, 'message' => $message, 'id' => NEW_ITEM));
		}
	}

	public function bulk_update()
	{
		$items_to_update = $this->input->post('item_ids');
		$item_data = [];

		foreach($_POST as $key => $value)
		{
			//This field is nullable, so treat it differently
			if($key === 'supplier_id' && $value !== '')
			{
				$item_data[$key] = $value;
			}
			elseif($value !== '' && !(in_array($key, array('item_ids', 'tax_names', 'tax_percents'))))
			{
				$item_data[$key] = $value;
			}
		}

		//Item data could be empty if tax information is being updated
		if(empty($item_data) || $this->Item->update_multiple($item_data, $items_to_update))
		{
			$items_taxes_data = [];
			$tax_names = $this->input->post('tax_names');
			$tax_percents = $this->input->post('tax_percents');
			$tax_updated = FALSE;

			foreach($tax_percents as $tax_percent)
			{
				if(!empty($tax_names[$tax_percent]) && is_numeric($tax_percents[$tax_percent]))
				{
					$tax_updated = TRUE;
					$items_taxes_data[] = array('name' => $tax_names[$tax_percent], 'percent' => $tax_percents[$tax_percent]);
				}
			}

			if($tax_updated)
			{
				$this->Item_taxes->save_multiple($items_taxes_data, $items_to_update);
			}

			echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('items_successful_bulk_edit'), 'id' => $this->xss_clean($items_to_update)));
		}
		else
		{
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('items_error_updating_multiple')));
		}
	}

	public function delete()
	{
		$items_to_delete = $this->input->post('ids');

		if($this->Item->delete_list($items_to_delete))
		{
			$message = $this->lang->line('items_successful_deleted') . ' ' . count($items_to_delete) . ' ' . $this->lang->line('items_one_or_multiple');
			echo json_encode(array('success' => TRUE, 'message' => $message));
		}
		else
		{
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('items_cannot_be_deleted')));
		}
	}

	public function generate_csv_file()
	{
		$name = 'import_items.csv';
		$allowed_locations = $this->Stock_location->get_allowed_locations();
		$allowed_attributes = $this->Attribute->get_definition_names(FALSE);
		$data = generate_import_items_csv($allowed_locations, $allowed_attributes);

		force_download($name, $data, TRUE);
	}

	public function csv_import()
	{
		$this->load->view('items/form_csv_import', NULL);
	}

	/**
	 * Imports items from CSV formatted file.
	 */
	public function import_csv_file()
	{
		if($_FILES['file_path']['error'] !== UPLOAD_ERR_OK)
		{
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('items_csv_import_failed')));
		}
		else
		{
			if(file_exists($_FILES['file_path']['tmp_name']))
			{
				set_time_limit(240);

				$failCodes = [];
				$csv_rows = get_csv_file($_FILES['file_path']['tmp_name']);
				$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
				$allowed_stock_locations = $this->Stock_location->get_allowed_locations();
				$attribute_definition_names	= $this->Attribute->get_definition_names();

				unset($attribute_definition_names[-1]);	//Removes the common_none_selected_text from the array

				foreach($attribute_definition_names as $definition_name)
				{
					$attribute_data[$definition_name] = $this->Attribute->get_definition_by_name($definition_name)[0];

					if($attribute_data[$definition_name]['definition_type'] === DROPDOWN)
					{
						$attribute_data[$definition_name]['dropdown_values'] = $this->Attribute->get_definition_values($attribute_data[$definition_name]['definition_id']);
					}
				}

				$this->db->trans_begin();

				foreach($csv_rows as $key => $row)
				{
					$is_failed_row = FALSE;
					$item_id = $row['Id'];
					$is_update = !empty($item_id);
					$item_data = array(
						'item_id' => $item_id,
						'name' => $row['Item Name'],
						'description' => $row['Description'],
						'category' => $row['Category'],
						'cost_price' => $row['Cost Price'],
						'unit_price' => $row['Unit Price'],
						'reorder_level' => $row['Reorder Level'],
						'deleted' => FALSE,
						'hsn_code' => $row['HSN'],
						'pic_filename' => $row['Image']);

					if(!empty($row['Supplier ID']))
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
						$is_failed_row = $this->Item->item_number_exists($item_data['item_number']);
					}

					if(!$is_failed_row)
					{
						$is_failed_row = $this->data_error_check($row, $item_data, $allowed_stock_locations, $attribute_definition_names, $attribute_data);
					}

					//Remove FALSE, NULL, '' and empty strings but keep 0
					$item_data = array_filter($item_data, 'strlen');

					if(!$is_failed_row && $this->Item->save($item_data, $item_id))
					{
						$this->save_tax_data($row, $item_data);
						$this->save_inventory_quantities($row, $item_data, $allowed_stock_locations, $employee_id);
						$is_failed_row = $this->save_attribute_data($row, $item_data, $attribute_data);

						if($is_update)
						{
							$item_data = array_merge($item_data, get_object_vars($this->Item->get_info_by_id_or_number($item_id)));
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
					$message = $this->lang->line('items_csv_import_partially_failed', count($failCodes), implode(', ', $failCodes));
					$this->db->trans_rollback();
					echo json_encode(array('success' => FALSE, 'message' => $message));
				}
				else
				{
					$this->db->trans_commit();

					echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('items_csv_import_success')));
				}
			}
			else
			{
				echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('items_csv_import_nodata_wrongformat')));
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
		$check_for_empty = array(
			'name' => $item_data['name'],
			'category' => $item_data['category'],
			'unit_price' => $item_data['unit_price']);

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
			if(!$this->Item->exists($item_id))
			{
				log_message('Error',"non-existent item_id: '$item_id' when either existing item_id or no item_id is required.");
				return TRUE;
			}
		}

		//Build array of fields to check for numerics
		$check_for_numeric_values = array(
			'cost_price' => $item_data['cost_price'],
			'unit_price' => $item_data['unit_price'],
			'reorder_level' => $item_data['reorder_level'],
			'supplier_id' => $item_data['supplier_id'],
			'Tax 1 Percent' => $row['Tax 1 Percent'],
			'Tax 2 Percent' => $row['Tax 2 Percent']);

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
		$attribute_id = $this->Attribute->value_exists($value, $attribute_data['definition_type']);

		$this->Attribute->delete_link($item_id, $attribute_data['definition_id']);

		if($attribute_id === FALSE)
		{
			$attribute_id = $this->Attribute->save_value($value, $attribute_data['definition_id'], $item_id, FALSE, $attribute_data['definition_type']);
		}
		else if($this->Attribute->save_link($item_id, $attribute_data['definition_id'], $attribute_id) === FALSE)
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
		$comment = $this->lang->line('items_inventory_CSV_import_quantity');
		$is_update = $row['Id'] ? TRUE : FALSE;

		foreach($allowed_locations as $location_id => $location_name)
		{
			$item_quantity_data = array(
				'item_id' => $item_data['item_id'],
				'location_id' => $location_id);

			$csv_data = array(
				'trans_items' => $item_data['item_id'],
				'trans_user' => $employee_id,
				'trans_comment' => $comment,
				'trans_location' => $location_id);

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
			$items_taxes_data[] = array('name' => $row['Tax 1 Name'], 'percent' => $row['Tax 1 Percent']);
		}

		if(is_numeric($row['Tax 2 Percent']) && $row['Tax 2 Name'] !== '')
		{
			$items_taxes_data[] = array('name' => $row['Tax 2 Name'], 'percent' => $row['Tax 2 Percent']);
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
					$item_data = array('pic_filename' => $new_pic_filename);
					$this->Item->save($item_data, $item->item_id);
				}
			}
		}
	}
}
?>
