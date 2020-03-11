<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Secure_Controller.php");

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

		// filters that will be loaded in the multiselect dropdown
		$data['filters'] = array('empty_upc' => $this->lang->line('items_empty_upc_items'),
			'low_inventory' => $this->lang->line('items_low_inventory_items'),
			'is_serialized' => $this->lang->line('items_serialized_items'),
			'no_description' => $this->lang->line('items_no_description_items'),
			'search_custom' => $this->lang->line('items_search_attributes'),
			'is_deleted' => $this->lang->line('items_is_deleted'),
			'temporary' => $this->lang->line('items_temp'));

		$this->load->view('items/manage', $data);
	}

	/*
	 Returns Items table data rows. This will be called with AJAX.
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

		$filters = array('start_date' => $this->input->get('start_date'),
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

		// check if any filter is set in the multiselect dropdown
		$filledup = array_fill_keys($this->input->get('filters'), TRUE);
		$filters = array_merge($filters, $filledup);

		$items = $this->Item->search($search, $filters, $limit, $offset, $sort, $order);

		$total_rows = $this->Item->get_found_rows($search, $filters);

		$data_rows = array();
		foreach($items->result() as $item)
		{
			$data_rows[] = $this->xss_clean(get_item_data_row($item));
			if($item->pic_filename!='')
			{
				$this->_update_pic_filename($item);
			}
		}

		echo json_encode(array('total' => $total_rows, 'rows' => $data_rows));
	}

	public function pic_thumb($pic_filename)
	{
		$this->load->helper('file');
		$this->load->library('image_lib');

		// in this context, $pic_filename always has .ext
		$ext = pathinfo($pic_filename, PATHINFO_EXTENSION);
		$images = glob('./uploads/item_pics/' . $pic_filename);

		// make sure we pick only the file name, without extension
		$base_path = './uploads/item_pics/' . pathinfo($pic_filename, PATHINFO_FILENAME);
		if(sizeof($images) > 0)
		{
			$image_path = $images[0];
			$thumb_path = $base_path . $this->image_lib->thumb_marker . '.' . $ext;
			if(sizeof($images) < 2)
			{
				$config['image_library'] = 'gd2';
				$config['source_image']  = $image_path;
				$config['maintain_ratio'] = TRUE;
				$config['create_thumb'] = TRUE;
				$config['width'] = 52;
				$config['height'] = 32;
				$this->image_lib->initialize($config);
				$image = $this->image_lib->resize();
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
		$suggestions = $this->xss_clean($this->Item->get_search_suggestions($this->input->post_get('term'),
			array('search_custom' => $this->input->post('search_custom'), 'is_deleted' => $this->input->post('is_deleted') != NULL), FALSE));

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
		$item_infos = $this->Item->get_multiple_info(explode(":", $item_ids), $this->item_lib->get_item_location());

		$result = array();
		foreach($item_infos->result() as $item_info)
		{
			$result[$item_info->item_id] = $this->xss_clean(get_item_data_row($item_info));
		}

		echo json_encode($result);
	}

	public function view($item_id = -1)
	{
		if($item_id == -1)
		{
			$data = array();
		}

		// allow_temp_items is set in the index function of items.php or sales.php
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

		if($data['allow_temp_item'] == 1)
		{
			if($item_id != -1)
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
		$data['include_hsn'] = $this->config->item('include_hsn') == '1';

		if($item_id == -1)
		{
			$data['default_tax_1_rate'] = $this->config->item('default_tax_1_rate');
			$data['default_tax_2_rate'] = $this->config->item('default_tax_2_rate');

			$item_info->receiving_quantity = 1;
			$item_info->reorder_level = 1;
			$item_info->item_type = ITEM; // standard
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

		$data['standard_item_locked'] = ($data['item_kit_disabled'] && $item_info->item_type == ITEM_KIT
										&& !$data['allow_temp_item']
										&& !($this->config->item('derive_sale_quantity') == '1'));

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
			$tax_categories = array();
			foreach($this->Tax_category->get_all()->result_array() as $row)
			{
				$tax_categories[$this->xss_clean($row['tax_category_id'])] = $this->xss_clean($row['tax_category']);
			}
			$tax_category = "";
			if ($item_info->tax_category_id != NULL)
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
			$data['tax_categories'] = array();
			$data['tax_category'] = '';
		}

		$data['logo_exists'] = $item_info->pic_filename != '';
		$ext = pathinfo($item_info->pic_filename, PATHINFO_EXTENSION);
		if($ext == '')
		{
			// if file extension is not found guess it (legacy)
			$images = glob('./uploads/item_pics/' . $item_info->pic_filename . '.*');
		}
		else
		{
			// else just pick that file
			$images = glob('./uploads/item_pics/' . $item_info->pic_filename);
		}
		$data['image_path'] = sizeof($images) > 0 ? base_url($images[0]) : '';
		$stock_locations = $this->Stock_location->get_undeleted_all()->result_array();
		foreach($stock_locations as $location)
		{
			$location = $this->xss_clean($location);

			$quantity = $this->xss_clean($this->Item_quantity->get_item_quantity($item_id, $location['location_id'])->quantity);
			$quantity = ($item_id == -1) ? 0 : $quantity;
			$location_array[$location['location_id']] = array('location_name' => $location['location_name'], 'quantity' => $quantity);
			$data['stock_locations'] = $location_array;
		}

		$data['selected_low_sell_item_id'] = $item_info->low_sell_item_id;

		if($item_id != -1 && $item_info->item_id != $item_info->low_sell_item_id)
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

	public function inventory($item_id = -1)
	{
		$item_info = $this->Item->get_info($item_id);
		foreach(get_object_vars($item_info) as $property => $value)
		{
			$item_info->$property = $this->xss_clean($value);
		}
		$data['item_info'] = $item_info;

		$data['stock_locations'] = array();
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

	public function count_details($item_id = -1)
	{
		$item_info = $this->Item->get_info($item_id);
		foreach(get_object_vars($item_info) as $property => $value)
		{
			$item_info->$property = $this->xss_clean($value);
		}
		$data['item_info'] = $item_info;

		$data['stock_locations'] = array();
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

		// check the list of items to see if any item_number field is empty
		foreach($result as &$item)
		{
			$item = $this->xss_clean($item);

			// update the barcode field if empty / NULL with the newly generated barcode
			if(empty($item['item_number']) && $this->config->item('barcode_generate_if_empty'))
			{
				// get the newly generated barcode
				$barcode_instance = Barcode_lib::barcode_instance($item, $config);
				$item['item_number'] = $barcode_instance->getData();

				$save_item = array('item_number' => $item['item_number']);

				// update the item in the database in order to save the barcode field
				$this->Item->save($save_item, $item['item_id']);
			}
		}
		$data['items'] = $result;

		// display barcodes
		$this->load->view('barcodes/barcode_sheet', $data);
	}

	public function attributes($item_id)
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

			if ($definition_value['definition_type'] == DROPDOWN)
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

	public function save($item_id = -1)
	{
		$upload_success = $this->_handle_image_upload();
		$upload_data = $this->upload->data();

		$receiving_quantity = parse_quantity($this->input->post('receiving_quantity'));
		$item_type = $this->input->post('item_type') == NULL ? ITEM : $this->input->post('item_type');

		if($receiving_quantity == '0' && $item_type!= ITEM_TEMP)
		{
			$receiving_quantity = '1';
		}
		$default_pack_name = $this->lang->line('items_default_pack_name');

		//Save item data
		$item_data = array(
			'name' => $this->input->post('name'),
			'description' => $this->input->post('description'),
			'category' => $this->input->post('category'),
			'item_type' => $item_type,
			'stock_type' => $this->input->post('stock_type') == NULL ? HAS_STOCK : $this->input->post('stock_type'),
			'supplier_id' => $this->input->post('supplier_id') == '' ? NULL : $this->input->post('supplier_id'),
			'item_number' => $this->input->post('item_number') == '' ? NULL : $this->input->post('item_number'),
			'cost_price' => parse_decimals($this->input->post('cost_price')),
			'unit_price' => parse_decimals($this->input->post('unit_price')),
			'reorder_level' => parse_quantity($this->input->post('reorder_level')),
			'receiving_quantity' => $receiving_quantity,
			'allow_alt_description' => $this->input->post('allow_alt_description') != NULL,
			'is_serialized' => $this->input->post('is_serialized') != NULL,
			'qty_per_pack' => $this->input->post('qty_per_pack') == NULL ? 1 : $this->input->post('qty_per_pack'),
			'pack_name' => $this->input->post('pack_name') == NULL ? $default_pack_name : $this->input->post('pack_name'),
			'low_sell_item_id' => $this->input->post('low_sell_item_id') == NULL ? $item_id : $this->input->post('low_sell_item_id'),
			'deleted' => $this->input->post('is_deleted') != NULL,
			'hsn_code' => $this->input->post('hsn_code') == NULL ? '' : $this->input->post('hsn_code')
		);

		if($item_data['item_type'] == ITEM_TEMP)
		{
			$item_data['stock_type'] = HAS_NO_STOCK;
			$item_data['receiving_quantity'] = 0;
			$item_data['reorder_level'] = 0;
		}

		$x = $this->input->post('tax_category_id');
		if(!isset($x))
		{
			$item_data['tax_category_id'] = '';
		}
		else
		{
			$item_data['tax_category_id'] = $this->input->post('tax_category_id') == '' ? NULL : $this->input->post('tax_category_id');
		}

		if(!empty($upload_data['orig_name']))
		{
			// XSS file image sanity check
			if($this->xss_clean($upload_data['raw_name'], TRUE) === TRUE)
			{
				$item_data['pic_filename'] = $upload_data['raw_name'];
			}
		}

		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;

		if($this->Item->save($item_data, $item_id))
		{
			$success = TRUE;
			$new_item = FALSE;
			//New item
			if($item_id == -1)
			{
				$item_id = $item_data['item_id'];
				$new_item = TRUE;
			}

			$use_destination_based_tax = (boolean)$this->config->item('use_destination_based_tax');

			if(!$use_destination_based_tax)
			{
				$items_taxes_data = array();
				$tax_names = $this->input->post('tax_names');
				$tax_percents = $this->input->post('tax_percents');
				$count = count($tax_percents);
				for ($k = 0; $k < $count; ++$k)
				{
					$tax_percentage = parse_tax($tax_percents[$k]);
					if(is_numeric($tax_percentage))
					{
						$items_taxes_data[] = array('name' => $tax_names[$k], 'percent' => $tax_percentage);
					}
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
				$location_detail = array('item_id' => $item_id,
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
			$attribute_links = $this->input->post('attribute_links') != NULL ? $this->input->post('attribute_links') : array();
			$attribute_ids = $this->input->post('attribute_ids');
			$this->Attribute->delete_link($item_id);
			foreach($attribute_links as $definition_id => $attribute_id)
			{
				$definition_type = $this->Attribute->get_info($definition_id)->definition_type;
				if($definition_type != DROPDOWN)
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
		else // failure
		{
			$message = $this->xss_clean($this->lang->line('items_error_adding_updating') . ' ' . $item_data['name']);

			echo json_encode(array('success' => FALSE, 'message' => $message, 'id' => -1));
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
		if($this->input->post('item_number') === -1)
		{
			$exists = $this->Item_kit->item_kit_exists_for_name($this->input->post('name'));
		}
		else
		{
			$exists = FALSE;
		}
		echo !$exists ? 'true' : 'false';
	}

	private function _handle_image_upload()
	{
		/* Let files be uploaded with their original name */

		// load upload library
		$config = array('upload_path' => './uploads/item_pics/',
			'allowed_types' => 'gif|jpg|png',
			'max_size' => '100',
			'max_width' => '640',
			'max_height' => '480'
		);
		$this->load->library('upload', $config);
		$this->upload->do_upload('item_image');

		return strlen($this->upload->display_errors()) == 0 || !strcmp($this->upload->display_errors(), '<p>'.$this->lang->line('upload_no_file_selected').'</p>');
	}

	public function remove_logo($item_id)
	{
		$item_data = array('pic_filename' => NULL);
		$result = $this->Item->save($item_data, $item_id);

		echo json_encode(array('success' => $result));
	}

	public function save_inventory($item_id = -1)
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
			$message = $this->xss_clean($this->lang->line('items_successful_updating') . ' ' . $cur_item_info->name);

			echo json_encode(array('success' => TRUE, 'message' => $message, 'id' => $item_id));
		}
		else//failure
		{
			$message = $this->xss_clean($this->lang->line('items_error_adding_updating') . ' ' . $cur_item_info->name);

			echo json_encode(array('success' => FALSE, 'message' => $message, 'id' => -1));
		}
	}

	public function bulk_update()
	{
		$items_to_update = $this->input->post('item_ids');
		$item_data = array();

		foreach($_POST as $key => $value)
		{
			//This field is nullable, so treat it differently
			if($key == 'supplier_id' && $value != '')
			{
				$item_data["$key"] = $value;
			}
			elseif($value != '' && !(in_array($key, array('item_ids', 'tax_names', 'tax_percents'))))
			{
				$item_data["$key"] = $value;
			}
		}

		//Item data could be empty if tax information is being updated
		if(empty($item_data) || $this->Item->update_multiple($item_data, $items_to_update))
		{
			$items_taxes_data = array();
			$tax_names = $this->input->post('tax_names');
			$tax_percents = $this->input->post('tax_percents');
			$tax_updated = FALSE;
			$count = count($tax_percents);
			for($k = 0; $k < $count; ++$k)
			{
				if(!empty($tax_names[$k]) && is_numeric($tax_percents[$k]))
				{
					$tax_updated = TRUE;

					$items_taxes_data[] = array('name' => $tax_names[$k], 'percent' => $tax_percents[$k]);
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

	/*
	 Items import from csv spreadsheet
	 */
	public function csv()
	{
		$name = 'import_items.csv';
		$allowed_locations = $this->Stock_location->get_allowed_locations();
		$allowed_attributes = $this->Attribute->get_definition_names(FALSE);
		$data = generate_import_items_csv($allowed_locations,$allowed_attributes);
		force_download($name, $data, TRUE);
	}

	public function csv_import()
	{
		$this->load->view('items/form_csv_import', NULL);
	}

	/**
	 * Imports items from CSV formatted file.
	 */
	public function do_csv_import()
	{
		if($_FILES['file_path']['error'] != UPLOAD_ERR_OK)
		{
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('items_csv_import_failed')));
		}
		else
		{
			if(file_exists($_FILES['file_path']['tmp_name']))
			{
				$line_array	= get_csv_file($_FILES['file_path']['tmp_name']);
				$failCodes	= array();
				$keys		= $line_array[0];

				$this->db->trans_begin();
				for($i = 1; $i < count($line_array); $i++)
				{
					$invalidated	= FALSE;
					$line 			= array_combine($keys,$this->xss_clean($line_array[$i]));	//Build a XSS-cleaned associative array with the row to use to assign values

					if(!empty($line))
					{
						$item_data = array(
							'name'					=> $line['Item Name'],
							'description'			=> $line['Description'],
							'category'				=> $line['Category'],
							'cost_price'			=> $line['Cost Price'],
							'unit_price'			=> $line['Unit Price'],
							'reorder_level'			=> $line['Reorder Level'],
							'supplier_id'			=> $this->Supplier->exists($line['Supplier ID']) ? $line['Supplier ID'] : NULL,
							'allow_alt_description'	=> $line['Allow Alt Description'] != '' ? '1' : '0',
							'is_serialized'			=> $line['Item has Serial Number'] != '' ? '1' : '0',
							'hsn_code'				=> $line['HSN'],
							'pic_filename'			=> $line['item_image']
						);

						$item_number 				= $line['Barcode'];

						if($item_number != '')
						{
							$item_data['item_number'] = $item_number;
							$invalidated = $this->Item->item_number_exists($item_number);
						}

						//Sanity check of data
						if(!$invalidated)
						{
							$invalidated = $this->data_error_check($line, $item_data);
						}
					}
					else
					{
						$invalidated = TRUE;
					}

					//Save to database
					if(!$invalidated && $this->Item->save($item_data))
					{
						$this->save_tax_data($line, $item_data);
						$this->save_inventory_quantities($line, $item_data);
						$this->save_attribute_data($line, $item_data);
					}
					else //insert or update item failure
					{
						$failed_row = $i+1;
						$failCodes[] = $failed_row;
						log_message("ERROR","CSV Item import failed on line ". $failed_row .". This item was not imported.");
					}
				}

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
	 * Checks the entire line of data for errors
	 *
	 * @param	array	$line
	 * @param 	array	$item_data
	 *
	 * @return	bool	Returns FALSE if all data checks out and TRUE when there is an error in the data
	 */
	private function data_error_check($line, $item_data)
	{
		//Check for empty required fields
		$check_for_empty = array(
			$item_data['name'],
			$item_data['category'],
			$item_data['cost_price'],
			$item_data['unit_price']
		);

		if(in_array('',$check_for_empty,true))
		{
			log_message("ERROR","Empty required value");
			return TRUE;	//Return fail on empty required fields
		}

		//Build array of fields to check for numerics
		$check_for_numeric_values = array(
			$item_data['cost_price'],
			$item_data['unit_price'],
			$item_data['reorder_level'],
			$item_data['supplier_id'],
			$line['Tax 1 Percent'],
			$line['Tax 2 Percent']
		);

		//Add in Stock Location values to check for numeric
		$allowed_locations	= $this->Stock_location->get_allowed_locations();

		foreach($allowed_locations as $location_id => $location_name)
		{
			$check_for_numeric_values[] = $line['location_'. $location_name];
		}

		//Check for non-numeric values which require numeric
		foreach($check_for_numeric_values as $value)
		{
			if(!is_numeric($value) && $value != '')
			{
				log_message("ERROR","non-numeric: '$value' when numeric is required");
				return TRUE;
			}
		}

		//Check Attribute Data
		$definition_names = $this->Attribute->get_definition_names();

		foreach($definition_names as $definition_name)
		{
			if(!empty($line['attribute_' . $definition_name]))
			{
				$attribute_data 	= $this->Attribute->get_definition_by_name($definition_name)[0];
				$attribute_type		= $attribute_data['definition_type'];
				$attribute_value 	= $line['attribute_' . $definition_name];

				if($attribute_type == 'DROPDOWN')
				{
					$dropdown_values 	= $this->Attribute->get_definition_values($attribute_data['definition_id']);
					$dropdown_values[] 	= '';

					if(in_array($attribute_value, $dropdown_values) === FALSE)
					{
						log_message("ERROR","Value: '$attribute_value' is not an acceptable DROPDOWN value");
						return TRUE;
					}
				}
				else if($attribute_type == 'DECIMAL')
				{
					if(!is_numeric($attribute_value) && $attribute_value != '')
					{
						log_message("ERROR","Decimal required: '$attribute_value' is not an acceptable DECIMAL value");
						return TRUE;
					}
				}
				else if($attribute_type == 'DATETIME')
				{
					if(strtotime($attribute_value) === FALSE)
					{
						log_message("ERROR","Datetime required: '$attribute_value' is not an acceptable DATETIME value");
						return TRUE;
					}
				}
			}
		}

		return FALSE;
	}

	/**
	 * @param line
	 * @param failCodes
	 * @param attribute_data
	 */
	private function save_attribute_data($line, $item_data)
	{
		$definition_names = $this->Attribute->get_definition_names();

		foreach($definition_names as $definition_name)
		{
			if(!empty($line['attribute_' . $definition_name]))
			{
				//Create attribute value
				$attribute_data = $this->Attribute->get_definition_by_name($definition_name)[0];
				$status = $this->Attribute->save_value($line['attribute_' . $definition_name], $attribute_data['definition_id'], $item_data['item_id'], FALSE, $attribute_data['definition_type']);

				if($status === FALSE)
				{
					return FALSE;
				}
			}
		}
	}

	/**
	 * Saves inventory quantities for the row in the appropriate stock locations.
	 *
	 * @param	array	line
	 * @param			item_data
	 */
	private function save_inventory_quantities($line, $item_data)
	{
		//Quantities & Inventory Section
		$employee_id		= $this->Employee->get_logged_in_employee_info()->person_id;
		$emp_info			= $this->Employee->get_info($employee_id);
		$comment			= $this->lang->line('items_inventory_CSV_import_quantity');
		$allowed_locations	= $this->Stock_location->get_allowed_locations();

		foreach($allowed_locations as $location_id => $location_name)
		{
			$item_quantity_data = array(
				'item_id' => $item_data['item_id'],
				'location_id' => $location_id
			);

			$csv_data = array(
				'trans_items' => $item_data['item_id'],
				'trans_user' => $employee_id,
				'trans_comment' => $comment,
				'trans_location' => $location_id,
			);

			if(!empty($line['location_' . $location_name]))
			{
				$item_quantity_data['quantity'] = $line['location_' . $location_name];
				$this->Item_quantity->save($item_quantity_data, $item_data['item_id'], $location_id);

				$csv_data['trans_inventory'] = $line['location_' . $location_name];
				$this->Inventory->insert($csv_data);
			}
			else
			{
				$item_quantity_data['quantity'] = 0;
				$this->Item_quantity->save($item_quantity_data, $item_data['item_id'], $line[$col]);

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
	private function save_tax_data($line, $item_data)
	{
		$items_taxes_data = array();

		if(is_numeric($line['Tax 1 Percent']) && $line['Tax 1 Name'] != '')
		{
			$items_taxes_data[] = array('name' => $line['Tax 1 Name'], 'percent' => $line['Tax 1 Percent'] );
		}

		if(is_numeric($line['Tax 2 Percent']) && $line['Tax 2 Name'] != '')
		{
			$items_taxes_data[] = array('name' => $line['Tax 2 Name'], 'percent' => $line['Tax 2 Percent'] );
		}

		if(count($items_taxes_data) > 0)
		{
			$this->Item_taxes->save($items_taxes_data, $item_data['item_id']);
		}
	}


	/**
	 * Guess whether file extension is not in the table field, if it isn't, then it's an old-format (formerly pic_id) field, so we guess the right filename and update the table
	 *
	 * @param $item the item to update
	 */
	private function _update_pic_filename($item)
	{
		$filename = pathinfo($item->pic_filename, PATHINFO_FILENAME);

		// if the field is empty there's nothing to check
		if(!empty($filename))
		{
			$ext = pathinfo($item->pic_filename, PATHINFO_EXTENSION);
			if(empty($ext))
			{
				$images = glob('./uploads/item_pics/' . $item->pic_filename . '.*');
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
