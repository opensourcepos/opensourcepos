<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Secure_Controller.php");

class Taxes extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('taxes');

		$this->load->model('enums/Rounding_mode');
		$this->load->library('tax_lib');
	}

//	public function index()
//	{
//		$data['table_headers'] = $this->xss_clean(get_taxes_manage_table_headers());
//
//		$this->load->view('taxes/manage', $data);
//	}

	public function index()
	{
		$data['tax_codes'] = $this->Tax_code->get_all()->result_array();
		$data['tax_categories'] = $this->Tax_category->get_all()->result_array();
		$data['tax_jurisdictions'] = $this->Tax_jurisdiction->get_all()->result_array();
		$data['tax_rate_table_headers'] = $this->xss_clean(get_tax_rates_manage_table_headers());
		$data['tax_categories_table_headers'] = $this->xss_clean(get_tax_categories_table_headers());
		$data['tax_types'] = $this->tax_lib->get_tax_types();

		if($this->config->item('tax_included') == '1')
		{
			$data['default_tax_type'] = Tax_lib::TAX_TYPE_INCLUDED;
		}
		else
		{
			$data['default_tax_type'] = Tax_lib::TAX_TYPE_EXCLUDED;
		}

		$data['tax_type_options'] = $this->tax_lib->get_tax_type_options($data['default_tax_type']);

		$this->load->view("taxes/manage", $data);
	}


	/*
	Returns tax_codes table data rows. This will be called with AJAX.
	*/
	public function search()
	{
		$search = $this->input->get('search');
		$limit = $this->input->get('limit');
		$offset = $this->input->get('offset');
		$sort = $this->input->get('sort');
		$order = $this->input->get('order');

		$tax_rates = $this->Tax->search($search, $limit, $offset, $sort, $order);

		$total_rows = $this->Tax->get_found_rows($search);

		$data_rows = array();
		foreach($tax_rates->result() as $tax_rate_row)
		{
			$data_rows[] = $this->xss_clean(get_tax_rates_data_row($tax_rate_row));
		}

		echo json_encode(array('total' => $total_rows, 'rows' => $data_rows));
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	public function suggest_search()
	{
		$suggestions = $this->xss_clean($this->Tax->get_search_suggestions($this->input->post('term')));

		echo json_encode($suggestions);
	}

	/*
	Provides list of tax categories to select from
	*/
	public function suggest_tax_categories()
	{
		$suggestions = $this->xss_clean($this->Tax_category->get_tax_category_suggestions($this->input->post('term')));

		echo json_encode($suggestions);
	}


	public function get_row($row_id)
	{
		$data_row = $this->xss_clean(get_tax_rates_data_row($this->Tax->get_info($row_id), $this));

		echo json_encode($data_row);
	}

	public function view_tax_codes($tax_code = -1)
	{
		$tax_code_info = $this->Tax->get_info($tax_code);

		$default_tax_category_id = 1; // Tax category id is always the default tax category
		$default_tax_category = $this->Tax->get_tax_category($default_tax_category_id);

		$tax_rate_info = $this->Tax->get_rate_info($tax_code, $default_tax_category_id);

		$data['rounding_options'] = Rounding_mode::get_rounding_options();
		$data['html_rounding_options'] = $this->get_html_rounding_options();

		if($tax_code == -1)
		{
			$data['tax_code'] = '';
			$data['tax_code_name'] = '';
			$data['tax_code_type'] = '0';
			$data['city'] = '';
			$data['state'] = '';
			$data['tax_rate'] = '0.0000';
			$data['rate_tax_code'] = '';
			$data['rate_tax_category_id'] = 1;
			$data['tax_category'] = '';
			$data['add_tax_category'] = '';
			$data['rounding_code'] = '0';
		}
		else
		{
			$data['tax_code'] = $tax_code;
			$data['tax_code_name'] = $tax_code_info->tax_code_name;
			$data['tax_code_type'] = $tax_code_info->tax_code_type;
			$data['city'] = $tax_code_info->city;
			$data['state'] = $tax_code_info->state;
			$data['rate_tax_code'] = $tax_code_info->rate_tax_code;
			$data['rate_tax_category_id'] = $tax_code_info->rate_tax_category_id;
			$data['tax_category'] = $tax_code_info->tax_category;
			$data['add_tax_category'] = '';
			$data['tax_rate'] = $tax_rate_info->tax_rate;
			$data['rounding_code'] = $tax_rate_info->rounding_code;
		}

		$data = $this->xss_clean($data);

		$tax_rates = array();
		foreach($this->Tax->get_tax_code_rate_exceptions($tax_code) as $tax_code_rate)
		{
			$tax_rate_row = array();
			$tax_rate_row['rate_tax_category_id'] = $this->xss_clean($tax_code_rate['rate_tax_category_id']);
			$tax_rate_row['tax_category'] = $this->xss_clean($tax_code_rate['tax_category']);
			$tax_rate_row['tax_rate'] = $this->xss_clean($tax_code_rate['tax_rate']);
			$tax_rate_row['rounding_code'] = $this->xss_clean($tax_code_rate['rounding_code']);

			$tax_rates[] = $tax_rate_row;
		}

		$data['tax_rates'] = $tax_rates;

		$this->load->view("taxes/tax_code_form", $data);
	}


	public function view($tax_rate_id = -1)
	{

		$tax_rate_info = $this->Tax->get_info($tax_rate_id);

		$data['tax_rate_id'] = $tax_rate_id;
		$data['rounding_options'] = Rounding_mode::get_rounding_options();

		$data['tax_code_options'] = $this->tax_lib->get_tax_code_options();
		$data['tax_category_options'] = $this->tax_lib->get_tax_category_options();
		$data['tax_jurisdiction_options'] = $this->tax_lib->get_tax_jurisdiction_options();

		if($tax_rate_id == -1)
		{
			$data['rate_tax_code_id'] = $this->config->item('default_tax_code');
			$data['rate_tax_category_id'] = $this->config->item('default_tax_category');
			$data['rate_jurisdiction_id'] = $this->config->item('default_tax_jurisdiction');
			$data['tax_rounding_code'] = Rounding_mode::HALF_UP;
			$data['tax_rate'] = 0.0000;
		}
		else
		{
			$data['rate_tax_code_id'] = $tax_rate_info->rate_tax_code_id;
			$data['rate_tax_code'] = $tax_rate_info->tax_code;
			$data['rate_tax_category_id'] = $tax_rate_info->rate_tax_category_id;
			$data['rate_jurisdiction_id'] = $tax_rate_info->rate_jurisdiction_id;
			$data['tax_rounding_code'] = $tax_rate_info->tax_rounding_code;
			$data['tax_rate'] = $tax_rate_info->tax_rate;
		}

		$data = $this->xss_clean($data);

		$this->load->view("taxes/tax_rates_form", $data);
	}



	public function view_tax_categories($tax_code = -1)
	{
		$tax_code_info = $this->Tax->get_info($tax_code);

		$default_tax_category_id = 1; // Tax category id is always the default tax category
		$default_tax_category = $this->Tax->get_tax_category($default_tax_category_id);

		$tax_rate_info = $this->Tax->get_rate_info($tax_code, $default_tax_category_id);

		$data['rounding_options'] = Rounding_mode::get_rounding_options();
		$data['html_rounding_options'] = $this->get_html_rounding_options();

		if($tax_code == -1)
		{
			$data['tax_code'] = '';
			$data['tax_code_name'] = '';
			$data['tax_code_type'] = '0';
			$data['city'] = '';
			$data['state'] = '';
			$data['tax_rate'] = '0.0000';
			$data['rate_tax_code'] = '';
			$data['rate_tax_category_id'] = 1;
			$data['tax_category'] = '';
			$data['add_tax_category'] = '';
			$data['rounding_code'] = '0';
		}
		else
		{
			$data['tax_code'] = $tax_code;
			$data['tax_code_name'] = $tax_code_info->tax_code_name;
			$data['tax_code_type'] = $tax_code_info->tax_code_type;
			$data['city'] = $tax_code_info->city;
			$data['state'] = $tax_code_info->state;
			$data['rate_tax_code'] = $tax_code_info->rate_tax_code;
			$data['rate_tax_category_id'] = $tax_code_info->rate_tax_category_id;
			$data['tax_category'] = $tax_code_info->tax_category;
			$data['add_tax_category'] = '';
			$data['tax_rate'] = $tax_rate_info->tax_rate;
			$data['rounding_code'] = $tax_rate_info->rounding_code;
		}

		$data = $this->xss_clean($data);

		$tax_rates = array();
		foreach($this->Tax->get_tax_code_rate_exceptions($tax_code) as $tax_code_rate)
		{
			$tax_rate_row = array();
			$tax_rate_row['rate_tax_category_id'] = $this->xss_clean($tax_code_rate['rate_tax_category_id']);
			$tax_rate_row['tax_category'] = $this->xss_clean($tax_code_rate['tax_category']);
			$tax_rate_row['tax_rate'] = $this->xss_clean($tax_code_rate['tax_rate']);
			$tax_rate_row['rounding_code'] = $this->xss_clean($tax_code_rate['rounding_code']);

			$tax_rates[] = $tax_rate_row;
		}

		$data['tax_rates'] = $tax_rates;

		$this->load->view("taxes/tax_category_form", $data);
	}

	public function view_tax_jurisdictions($tax_code = -1)
	{
		$tax_code_info = $this->Tax->get_info($tax_code);

		$default_tax_category_id = 1; // Tax category id is always the default tax category
		$default_tax_category = $this->Tax->get_tax_category($default_tax_category_id);

		$tax_rate_info = $this->Tax->get_rate_info($tax_code, $default_tax_category_id);

		$data['rounding_options'] = Rounding_mode::get_rounding_options();
		$data['html_rounding_options'] = $this->get_html_rounding_options();

		if($tax_code == -1)
		{
			$data['tax_code'] = '';
			$data['tax_code_name'] = '';
			$data['tax_code_type'] = '0';
			$data['city'] = '';
			$data['state'] = '';
			$data['tax_rate'] = '0.0000';
			$data['rate_tax_code'] = '';
			$data['rate_tax_category_id'] = 1;
			$data['tax_category'] = '';
			$data['add_tax_category'] = '';
			$data['rounding_code'] = '0';
		}
		else
		{
			$data['tax_code'] = $tax_code;
			$data['tax_code_name'] = $tax_code_info->tax_code_name;
			$data['tax_code_type'] = $tax_code_info->tax_code_type;
			$data['city'] = $tax_code_info->city;
			$data['state'] = $tax_code_info->state;
			$data['rate_tax_code'] = $tax_code_info->rate_tax_code;
			$data['rate_tax_category_id'] = $tax_code_info->rate_tax_category_id;
			$data['tax_category'] = $tax_code_info->tax_category;
			$data['add_tax_category'] = '';
			$data['tax_rate'] = $tax_rate_info->tax_rate;
			$data['rounding_code'] = $tax_rate_info->rounding_code;
		}

		$data = $this->xss_clean($data);

		$tax_rates = array();
		foreach($this->Tax->get_tax_code_rate_exceptions($tax_code) as $tax_code_rate)
		{
			$tax_rate_row = array();
			$tax_rate_row['rate_tax_category_id'] = $this->xss_clean($tax_code_rate['rate_tax_category_id']);
			$tax_rate_row['tax_category'] = $this->xss_clean($tax_code_rate['tax_category']);
			$tax_rate_row['tax_rate'] = $this->xss_clean($tax_code_rate['tax_rate']);
			$tax_rate_row['rounding_code'] = $this->xss_clean($tax_code_rate['rounding_code']);

			$tax_rates[] = $tax_rate_row;
		}

		$data['tax_rates'] = $tax_rates;

		$this->load->view("taxes/tax_jurisdiction_form", $data);
	}

	public static function get_html_rounding_options()
	{
		return Rounding_mode::get_html_rounding_options();
	}

	public function save($tax_rate_id = -1)
	{
		$tax_category_id = $this->input->post('rate_tax_category_id');
		$tax_rate = parse_decimals($this->input->post('tax_rate'));

		if ($tax_rate == 0) {
			$tax_category_info = $this->Tax_category->get_info($tax_category_id);
			$tax_rate = $tax_category_info->default_tax_rate;
		}

		$tax_rate_data = array(
			'rate_tax_code_id' => $this->input->post('rate_tax_code_id'),
			'rate_tax_category_id' => $this->input->post('rate_tax_category_id'),
			'rate_jurisdiction_id' => $this->input->post('rate_jurisdiction_id'),
			'tax_rate' => $tax_rate,
			'tax_rounding_code' => $this->input->post('tax_rounding_code')
		);

		if($this->Tax->save($tax_rate_data, $tax_rate_id))
		{
			if($tax_rate_id == -1)
			{
				echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('taxes_tax_rate_successfully_added')));
			}
			else //Existing tax_code
			{
				echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('taxes_tax_rate_successful_updated')));
			}
		}
		else
		{
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('taxes_tax_rate_error_adding_updating')));
		}
	}

	public function delete()
	{
		$tax_codes_to_delete = $this->xss_clean($this->input->post('ids'));

		if($this->Tax->delete_list($tax_codes_to_delete))
		{
			echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('taxes_tax_code_successful_deleted')));
		} else
		{
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('taxes_tax_code_cannot_be_deleted')));
		}
	}

	public function suggest_tax_codes()
	{
		$suggestions = $this->xss_clean($this->Tax_code->get_tax_codes_search_suggestions($this->input->post_get('term')));

		echo json_encode($suggestions);
	}


	public function save_tax_codes()
	{
		$this->db->trans_start();

		$not_to_delete = array();
		foreach($this->input->post() as $key => $value)
		{

			if(!(strstr($key, 'tax_code_name_')=== FALSE))
			{
				$tax_code_id = preg_replace("/.*?_(\d+)$/", "$1", $key);
				$array_save[$tax_code_id]['tax_code_name'] = $value;
			}
			elseif(!(strstr($key, 'tax_code_id_') === FALSE))
			{
				$tax_code_id = preg_replace("/.*?_(\d+)$/", "$1", $key);
				$value = isset($value) ? $value : '';
				$array_save[$tax_code_id]['tax_code_id'] = strtoupper($value);
			}
			elseif(!(strstr($key, 'tax_code_') === FALSE))
			{
				$tax_code_id = preg_replace("/.*?_(\d+)$/", "$1", $key);
				$value = isset($value) ? $value : '';
				$array_save[$tax_code_id]['tax_code'] = strtoupper($value);
			}
			elseif(!(strstr($key, 'city_') === FALSE))
			{
				$tax_code_id = preg_replace("/.*?_(\d+)$/", "$1", $key);
				$value = isset($value) ? $value : '';
				$array_save[$tax_code_id]['city'] = $value;
			}
			elseif(!(strstr($key, 'state_') === FALSE))
			{
				$tax_code_id = preg_replace("/.*?_(\d+)$/", "$1", $key);
				$value = isset($value) ? $value : '';
				$array_save[$tax_code_id]['state'] = $value;
			}
		}

		$not_to_delete = array();

		if(!empty($array_save))
		{
			foreach($array_save as $key => $value)
			{
				// save or update
				$tax_code_data = array('tax_code' => $value['tax_code'], 'tax_code_name' => $value['tax_code_name'], 'city' => $value['city'], 'state' => $value['state'], 'deleted' => '0');
				$this->Tax_code->save($tax_code_data);
				$not_to_delete[] = $tax_code_data['tax_code'];
			}
		}

		// all entries not available in post will be deleted now
		$deleted_tax_codes = $this->Tax_code->get_all()->result_array();

		foreach($deleted_tax_codes as $key => $tax_code_data)
		{
			if(!in_array($tax_code_data['tax_code'], $not_to_delete))
			{
				$this->Tax_code->delete($tax_code_data['tax_code']);
			}
		}

		$this->db->trans_complete();

		$success = $this->db->trans_status();

		echo json_encode(array(
			'success' => $success,
			'message' => $this->lang->line('taxes_tax_codes_saved_' . ($success ? '' : 'un') . 'successfully')
		));
	}

	public function save_tax_jurisdictions()
	{
		$this->db->trans_start();

		$not_to_delete = array();
		foreach($this->input->post() as $key => $value)
		{

			if(!(strstr($key, 'jurisdiction_id_')=== FALSE))
			{
				$id = preg_replace("/.*?_(\d+)$/", "$1", $key);
				$array_save[$id]['jurisdiction_id'] = $value;
			}
			elseif(!(strstr($key, 'jurisdiction_name_') === FALSE))
			{
				$id = preg_replace("/.*?_(\d+)$/", "$1", $key);
				$value = isset($value) ? $value : '';
				$array_save[$id]['jurisdiction_name'] = $value;
			}
			elseif(!(strstr($key, 'tax_type_') === FALSE))
			{
				$id = preg_replace("/.*?_(\d+)$/", "$1", $key);
				$value = isset($value) ? $value : '';
				$array_save[$id]['tax_type'] = $value;
			}
			elseif(!(strstr($key, 'reporting_authority_') === FALSE))
			{
				$id = preg_replace("/.*?_(\d+)$/", "$1", $key);
				$value = isset($value) ? $value : '';
				$array_save[$id]['reporting_authority'] = $value;
			}
			elseif(!(strstr($key, 'tax_group_sequence_') === FALSE))
			{
				$id = preg_replace("/.*?_(\d+)$/", "$1", $key);
				$value = isset($value) ? $value : '';
				$array_save[$id]['tax_group_sequence'] = $value;
			}
			elseif(!(strstr($key, 'cascade_sequence_') === FALSE))
			{
				$id = preg_replace("/.*?_(\d+)$/", "$1", $key);
				$value = isset($value) ? $value : '';
				$array_save[$id]['cascade_sequence'] = $value;
			}
		}

		$not_to_delete = array();

		if(!empty($array_save))
		{
			foreach($array_save as $key => $value)
			{
				// save or update
				$tax_jurisdiction_data = array('jurisdiction_name' => $value['jurisdiction_name'], 'tax_type' => $value['tax_type'], 'reporting_authority' => $value['reporting_authority'], 'tax_group_sequence' => $value['tax_group_sequence'], 'cascade_sequence' => $value['cascade_sequence'], 'deleted' => '0');
				$this->Tax_jurisdiction->save($tax_jurisdiction_data, $value['jurisdiction_id']);
				if($value['jurisdiction_id'] == -1)
				{
					$not_to_delete[] = $tax_jurisdiction_data['jurisdiction_id'];
				}
				else
				{
					$not_to_delete[] = $value['jurisdiction_id'];
				}
			}
		}

		// all entries not available in post will be deleted now
		$deleted_tax_jurisdictions = $this->Tax_jurisdiction->get_all()->result_array();

		foreach($deleted_tax_jurisdictions as $key => $tax_jurisdiction_data)
		{
			if(!in_array($tax_jurisdiction_data['jurisdiction_id'], $not_to_delete))
			{
				$this->Tax_jurisdiction->delete($tax_jurisdiction_data['jurisdiction_id']);
			}
		}

		$this->db->trans_complete();

		$success = $this->db->trans_status();

		echo json_encode(array(
			'success' => $success,
			'message' => $this->lang->line('taxes_tax_jurisdictions_saved_' . ($success ? '' : 'un') . 'successfully')
		));
	}

	public function save_tax_categories()
	{
		$this->db->trans_start();

		$not_to_delete = array();
		foreach($this->input->post() as $key => $value)
		{
			// Note that for proper select tax_category_id and tax_category_code must precede tax_category
			if(!(strstr($key, 'tax_category_id_')=== FALSE))
			{
				$id = preg_replace("/.*?_(\d+)$/", "$1", $key);
				$array_save[$id]['tax_category_id'] = $value;
			}
			elseif(!(strstr($key, 'tax_category_') === FALSE))
			{
				$id = preg_replace("/.*?_(\d+)$/", "$1", $key);
				$value = isset($value) ? $value : '';
				$array_save[$id]['tax_category'] = $value;
			}
			elseif(!(strstr($key, 'default_tax_rate_') === FALSE))
			{
				$id = preg_replace("/.*?_(\d+)$/", "$1", $key);
				$value = isset($value) ? $value : '';
				$array_save[$id]['default_tax_rate'] = $value;
			}
			elseif(!(strstr($key, 'tax_group_sequence_') === FALSE))
			{
				$id = preg_replace("/.*?_(\d+)$/", "$1", $key);
				$value = isset($value) ? $value : '';
				$array_save[$id]['tax_group_sequence'] = $value;
			}
		}

		$not_to_delete = array();

		if(!empty($array_save))
		{
			foreach($array_save as $key => $value)
			{
				// save or update
				$tax_category_data = array('tax_category' => $value['tax_category'], 'default_tax_rate' => $value['default_tax_rate'], 'tax_group_sequence' => $value['tax_group_sequence'], 'deleted' => '0');
				$this->Tax_category->save($tax_category_data, $value['tax_category_id']);
				if($value['tax_category_id'] == -1)
				{
					$not_to_delete[] = $tax_category_data['tax_category_id'];
				}
				else
				{
					$not_to_delete[] = $value['tax_category_id'];
				}
			}
		}

		// all entries not available in post will be deleted now
		$deleted_tax_categories = $this->Tax_category->get_all()->result_array();

		foreach($deleted_tax_categories as $key => $tax_category_data)
		{
			if(!in_array($tax_category_data['tax_category_id'], $not_to_delete))
			{
				$this->Tax_category->delete($tax_category_data['tax_category_id']);
			}
		}

		$this->db->trans_complete();

		$success = $this->db->trans_status();

		echo json_encode(array(
			'success' => $success,
			'message' => $this->lang->line('taxes_tax_categories_saved_' . ($success ? '' : 'un') . 'successfully')
		));
	}

}
?>
