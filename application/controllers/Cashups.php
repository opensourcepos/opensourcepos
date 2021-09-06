<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Secure_Controller.php");

class Cashups extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('cashups');
	}

	public function index()
	{
		$data['table_headers'] = $this->xss_clean(get_cashups_manage_table_headers());

		// filters that will be loaded in the multiselect dropdown
		$data['filters'] = array('is_deleted' => $this->lang->line('cashups_is_deleted'));

		$this->load->view('cashups/manage', $data);
	}

	public function search()
	{
		$cash_up = 0;
		$search   = $this->input->get('search');
		$limit    = $this->input->get('limit');
		$offset   = $this->input->get('offset');
		$sort     = $this->input->get('sort');
		$order    = $this->input->get('order');
		$filters  = array(
					 'start_date' => $this->input->get('start_date'),
					 'end_date' => $this->input->get('end_date'),
					 'is_deleted' => FALSE);

		// check if any filter is set in the multiselect dropdown
		$filledup = array_fill_keys($this->input->get('filters'), TRUE);
		$filters = array_merge($filters, $filledup);
		$cash_ups = $this->Cashup->search($search, $filters, $limit, $offset, $sort, $order);
		$total_rows = $this->Cashup->get_found_rows($search, $filters);
		$data_rows = array();
		foreach($cash_ups->result() as $cash_up)
		{
			$data_rows[] = $this->xss_clean(get_cash_up_data_row($cash_up));
		}

		echo json_encode(array('total' => $total_rows, 'rows' => $data_rows));
	}

	public function view($cashup_id = -1)
	{
		$data = array();

		$data['employees'] = array();
		foreach($this->Employee->get_all()->result() as $employee)
		{
			foreach(get_object_vars($employee) as $property => $value)
			{
				$employee->$property = $this->xss_clean($value);
			}

			$data['employees'][$employee->person_id] = $employee->first_name . ' ' . $employee->last_name;
		}

		$cash_ups_info = $this->Cashup->get_info($cashup_id);

		foreach(get_object_vars($cash_ups_info) as $property => $value)
		{
			$cash_ups_info->$property = $this->xss_clean($value);
		}

		// open cashup
		if(empty($cash_ups_info->cashup_id))
		{
			$cash_ups_info->open_date = date('Y-m-d H:i:s');
			$cash_ups_info->close_date = $cash_ups_info->open_date;
			$cash_ups_info->open_employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
			$cash_ups_info->close_employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		}
		// if all the amounts are null or 0 that means it's a close cashup
		elseif(floatval($cash_ups_info->closed_amount_cash) == 0 &&
			floatval($cash_ups_info->closed_amount_due) == 0 &&
			floatval($cash_ups_info->closed_amount_card) == 0 &&
			floatval($cash_ups_info->closed_amount_check) == 0)
		{
			// set the close date and time to the actual as this is a close session
			$cash_ups_info->close_date = date('Y-m-d H:i:s');

			// the closed amount starts with the open amount -/+ any trasferred amount
			$cash_ups_info->closed_amount_cash = $cash_ups_info->open_amount_cash + $cash_ups_info->transfer_amount_cash;

			// if it's date mode only and not date & time truncate the open and end date to date only
			if(empty($this->config->item('date_or_time_format')))
			{
				// search for all the payments given the time range
				$inputs = array('start_date' => substr($cash_ups_info->open_date, 0, 10), 'end_date' => substr($cash_ups_info->close_date, 0, 10), 'sale_type' => 'complete', 'location_id' => 'all');
			}
			else
			{
				// search for all the payments given the time range
				$inputs = array('start_date' => $cash_ups_info->open_date, 'end_date' => $cash_ups_info->close_date, 'sale_type' => 'complete', 'location_id' => 'all');
			}

			// get all the transactions payment summaries
			$this->load->model('reports/Summary_payments');
			$reports_data = $this->Summary_payments->getData($inputs);

			foreach($reports_data as $row)
			{
				if($row['trans_group'] == $this->lang->line('reports_trans_payments'))
				{
					if($row['trans_type'] == $this->lang->line('sales_cash'))
					{
						$cash_ups_info->closed_amount_cash += $this->xss_clean($row['trans_amount']);
					}
					elseif($row['trans_type'] == $this->lang->line('sales_due'))
					{
						$cash_ups_info->closed_amount_due += $this->xss_clean($row['trans_amount']);
					}
					elseif($row['trans_type'] == $this->lang->line('sales_debit') ||
						$row['trans_type'] == $this->lang->line('sales_credit'))
					{
						$cash_ups_info->closed_amount_card += $this->xss_clean($row['trans_amount']);
					}
					elseif($row['trans_type'] == $this->lang->line('sales_check'))
					{
						$cash_ups_info->closed_amount_check += $this->xss_clean($row['trans_amount']);
					}
				}
			}

			// lookup expenses paid in cash
			$filters = array(
						 'only_cash' => TRUE,
						 'only_due' => FALSE,
						 'only_check' => FALSE,
						 'only_credit' => FALSE,
						 'only_debit' => FALSE,
						 'is_deleted' => FALSE);
			$payments = $this->Expense->get_payments_summary('', array_merge($inputs, $filters));

			foreach($payments as $row)
			{
				$cash_ups_info->closed_amount_cash -= $this->xss_clean($row['amount']);
			}

			$cash_ups_info->closed_amount_total = $this->_calculate_total($cash_ups_info->open_amount_cash, $cash_ups_info->transfer_amount_cash, $cash_ups_info->closed_amount_cash, $cash_ups_info->closed_amount_due, $cash_ups_info->closed_amount_card, $cash_ups_info->closed_amount_check);
		}

		$data['cash_ups_info'] = $cash_ups_info;

		$this->load->view("cashups/form", $data);
	}

	public function get_row($row_id)
	{
		$cash_ups_info = $this->Cashup->get_info($row_id);
		$data_row = $this->xss_clean(get_cash_up_data_row($cash_ups_info));

		echo json_encode($data_row);
	}

	public function save($cashup_id = -1)
	{
		$open_date = $this->input->post('open_date');
		$open_date_formatter = date_create_from_format($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), $open_date);

		$close_date = $this->input->post('close_date');
		$close_date_formatter = date_create_from_format($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), $close_date);

		$cash_up_data = array(
			'open_date' => $open_date_formatter->format('Y-m-d H:i:s'),
			'close_date' => $close_date_formatter->format('Y-m-d H:i:s'),
			'open_amount_cash' => parse_decimals($this->input->post('open_amount_cash')),
			'transfer_amount_cash' => parse_decimals($this->input->post('transfer_amount_cash')),
			'closed_amount_cash' => parse_decimals($this->input->post('closed_amount_cash')),
			'closed_amount_due' => parse_decimals($this->input->post('closed_amount_due')),
			'closed_amount_card' => parse_decimals($this->input->post('closed_amount_card')),
			'closed_amount_check' => parse_decimals($this->input->post('closed_amount_check')),
			'closed_amount_total' => parse_decimals($this->input->post('closed_amount_total')),
			'note' => $this->input->post('note') != NULL,
			'description' => $this->input->post('description'),
			'open_employee_id' => $this->input->post('open_employee_id'),
			'close_employee_id' => $this->input->post('close_employee_id'),
			'deleted' => $this->input->post('deleted') != NULL
		);

		if($this->Cashup->save($cash_up_data, $cashup_id))
		{
			$cash_up_data = $this->xss_clean($cash_up_data);

			//New cashup_id
			if($cashup_id == -1)
			{
				echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('cashups_successful_adding'), 'id' => $cash_up_data['cashup_id']));
			}
			else // Existing Cashup
			{
				echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('cashups_successful_updating'), 'id' => $cashup_id));
			}
		}
		else//failure
		{
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('cashups_error_adding_updating'), 'id' => -1));
		}
	}

	public function delete()
	{
		$cash_ups_to_delete = $this->input->post('ids');

		if($this->Cashup->delete_list($cash_ups_to_delete))
		{
			echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('cashups_successful_deleted') . ' ' . count($cash_ups_to_delete) . ' ' . $this->lang->line('cashups_one_or_multiple'), 'ids' => $cash_ups_to_delete));
		}
		else
		{
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('cashups_cannot_be_deleted'), 'ids' => $cash_ups_to_delete));
		}
	}

	/*
	AJAX call from cashup input form to calculate the total
	*/
	public function ajax_cashup_total()
	{
		$open_amount_cash = parse_decimals($this->input->post('open_amount_cash'));
		$transfer_amount_cash = parse_decimals($this->input->post('transfer_amount_cash'));
		$closed_amount_cash = parse_decimals($this->input->post('closed_amount_cash'));
		$closed_amount_due = parse_decimals($this->input->post('closed_amount_due'));
		$closed_amount_card = parse_decimals($this->input->post('closed_amount_card'));
		$closed_amount_check = parse_decimals($this->input->post('closed_amount_check'));

		$total = $this->_calculate_total($open_amount_cash, $transfer_amount_cash, $closed_amount_due, $closed_amount_cash, $closed_amount_card, $closed_amount_check);

		echo json_encode(array('total' => to_currency_no_money($total)));
	}

	/*
	Calculate total
	*/
	private function _calculate_total($open_amount_cash, $transfer_amount_cash, $closed_amount_due, $closed_amount_cash, $closed_amount_card, $closed_amount_check)
	{
		return ($closed_amount_cash - $open_amount_cash - $transfer_amount_cash + $closed_amount_due + $closed_amount_card + $closed_amount_check);
	}
}
?>
