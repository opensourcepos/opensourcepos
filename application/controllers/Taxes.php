<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Secure_Controller.php");

class Taxes extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('taxes');

		$this->load->model('enums/Rounding_mode');

	}

	public function index()
	{
		$data['table_headers'] = $this->xss_clean(get_taxes_manage_table_headers());

		$this->load->view('taxes/manage', $data);
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

		$tax_codes = $this->Tax->search($search, $limit, $offset, $sort, $order);

		$total_rows = $this->Tax->get_found_rows($search);

		$data_rows = array();
		foreach($tax_codes->result() as $tax_code_row)
		{
			$data_rows[] = $this->xss_clean(get_tax_data_row($tax_code_row));
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
		$suggestions = $this->xss_clean($this->Tax->get_tax_category_suggestions($this->input->post('term')));

		echo json_encode($suggestions);
	}


	public function get_row($row_id)
	{
		$data_row = $this->xss_clean(get_tax_codes_data_row($this->Tax->get_info($row_id), $this));

		echo json_encode($data_row);
	}

	public function view($tax_code = -1)
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

		$tax_code_rates = array();
		foreach($this->Tax->get_tax_code_rate_exceptions($tax_code) as $tax_code_rate)
		{
			$tax_code_row = array();
			$tax_code_row['rate_tax_category_id'] = $this->xss_clean($tax_code_rate['rate_tax_category_id']);
			$tax_code_row['tax_category'] = $this->xss_clean($tax_code_rate['tax_category']);
			$tax_code_row['tax_rate'] = $this->xss_clean($tax_code_rate['tax_rate']);
			$tax_code_row['rounding_code'] = $this->xss_clean($tax_code_rate['rounding_code']);

			$tax_code_rates[] = $tax_code_row;
		}

		$data['tax_code_rates'] = $tax_code_rates;

		$this->load->view("taxes/form", $data);
	}

	public static function get_html_rounding_options()
	{
		return Rounding_mode::get_html_rounding_options();
	}

	public function save($tax_code = -1)
	{
		$entered_tax_code = $this->xss_clean($this->input->post('tax_code'));
		$tax_code_data = array(
			'tax_code' => strtoupper($this->input->post('tax_code')),
			'tax_code_name' => $this->input->post('tax_code_name'),
			'tax_code_type' => $this->input->post('tax_code_type'),
			'city' => $this->input->post('city'),
			'state' => $this->input->post('state'));

		$tax_rate_data = array(
			'rate_tax_code' => $this->input->post('tax_code'),
			'rate_tax_category_id' => 1,
			'tax_rate' => parse_decimals($this->input->post('tax_rate')),
			'rounding_code' => $this->input->post('rounding_code')
		);

		if($this->Tax->save($tax_code_data, $tax_rate_data, $tax_code))
		{
			$tax_code_rate_exceptions = array();
			if(!empty($this->input->post('exception_tax_rate')))
			{
				foreach($this->input->post('exception_tax_rate') as $tax_category_id => $exception_tax_rate)
				{
					$exception_rounding_code = $this->input->post('exception_rounding_code[' . $tax_category_id . ']');
					$tax_code_rate_exceptions[] = array(
						'rate_tax_code' => $entered_tax_code,
						'rate_tax_category_id' => $tax_category_id,
						'tax_rate' => $exception_tax_rate,
						'rounding_code' => $exception_rounding_code
					);
				}
			}

			if(!empty($tax_code_rate_exceptions))
			{
				$success = $this->Tax->save_tax_rate_exceptions($tax_code_rate_exceptions, $entered_tax_code);
			}

			$tax_code_data = $this->xss_clean($tax_code_data);

			//New tax_code record
			if($tax_code == -1)
			{
				echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('taxes_tax_code_successfully_added') . ' ' . $entered_tax_code));
			}
			else //Existing tax_code
			{
				echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('taxes_tax_code_successful_updated') . ' ' . $entered_tax_code));
			}
		}
		else //failure
		{
			$tax_code_data = $this->xss_clean($tax_code_data);

			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('taxes_tax_code_error_adding_updating') . ' ' .
				$entered_tax_code));
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

	public function suggest_sales_tax_codes()
	{
		$suggestions = $this->xss_clean($this->Tax->get_sales_tax_codes_search_suggestions($this->input->post_get('term')));

		echo json_encode($suggestions);
	}

}
?>
