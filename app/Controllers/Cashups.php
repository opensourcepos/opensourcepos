<?php

namespace App\Controllers;

use App\Models\Cashup;
use App\Models\Expense;
use App\Models\Reports\Summary_payments;
use CodeIgniter\Model;
use Config\OSPOS;

class Cashups extends Secure_Controller
{
	 private Cashup $cashup;
	 private Expense $expense;
	 private Summary_payments $summary_payments;
	 private array $config;

	public function __construct()
	{
		parent::__construct('cashups');

		$this->cashup = model(Cashup::class);
		$this->expense = model(Expense::class);
		$this->summary_payments = model(Summary_payments::class);
		$this->config = config(OSPOS::class)->settings;
	}

	/**
	 * @return void
	 */
	public function getIndex(): void
	{
		$data['table_headers'] = get_cashups_manage_table_headers();

		// filters that will be loaded in the multiselect dropdown
		$data['filters'] = ['is_deleted' => lang('Cashups.is_deleted')];

		echo view('cashups/manage', $data);
	}

	/**
	 * @return void
	 */
	public function getSearch(): void
	{
		$search = $this->request->getGet('search', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$limit = $this->request->getGet('limit', FILTER_SANITIZE_NUMBER_INT);
		$offset = $this->request->getGet('offset', FILTER_SANITIZE_NUMBER_INT);
		$sort = $this->request->getGet('sort', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$order = $this->request->getGet('order', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$filters = [
			 'start_date' => $this->request->getGet('start_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS),	//TODO: Is this the best way to filter dates
			 'end_date' => $this->request->getGet('end_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
			 'is_deleted' => false
		];

		// check if any filter is set in the multiselect dropdown
		$request_filters = array_fill_keys($this->request->getGet('filters', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? [], true);
		$filters = array_merge($filters, $request_filters);
		$cash_ups = $this->cashup->search($search, $filters, $limit, $offset, $sort, $order);
		$total_rows = $this->cashup->get_found_rows($search, $filters);
		$data_rows = [];
		foreach($cash_ups->getResult() as $cash_up)
		{
			$data_rows[] = get_cash_up_data_row($cash_up);
		}

		echo json_encode(['total' => $total_rows, 'rows' => $data_rows]);
	}

	/**
	 * @param int $cashup_id
	 * @return void
	 */
	public function getView(int $cashup_id = NEW_ENTRY): void
	{
		$data = [];

		$data['employees'] = [];
		foreach($this->employee->get_all()->getResult() as $employee)
		{
			foreach(get_object_vars($employee) as $property => $value)
			{
				$employee->$property = $value;
			}

			$data['employees'][$employee->person_id] = $employee->first_name . ' ' . $employee->last_name;
		}

		$cash_ups_info = $this->cashup->get_info($cashup_id);

		foreach(get_object_vars($cash_ups_info) as $property => $value)
		{
			$cash_ups_info->$property = $value;
		}

		// open cashup
		if($cash_ups_info->cashup_id == NEW_ENTRY)
		{
			$cash_ups_info->open_date = date('Y-m-d H:i:s');
			$cash_ups_info->close_date = $cash_ups_info->open_date;
			$cash_ups_info->open_employee_id = $this->employee->get_logged_in_employee_info()->person_id;
			$cash_ups_info->close_employee_id = $this->employee->get_logged_in_employee_info()->person_id;
		}
		// if all the amounts are null or 0 that means it's a close cashup
		elseif(floatval($cash_ups_info->closed_amount_cash) == 0
			&& floatval($cash_ups_info->closed_amount_due) == 0
			&& floatval($cash_ups_info->closed_amount_card) == 0
			&& floatval($cash_ups_info->closed_amount_check) == 0)
		{
			// set the close date and time to the actual as this is a close session
			$cash_ups_info->close_date = date('Y-m-d H:i:s');

			// the closed amount starts with the open amount -/+ any trasferred amount
			$cash_ups_info->closed_amount_cash = $cash_ups_info->open_amount_cash + $cash_ups_info->transfer_amount_cash;

			// if it's date mode only and not date & time truncate the open and end date to date only
			if(empty($this->config['date_or_time_format']))
			{
				if($cash_ups_info->open_date != null)
				{
					$start_date = substr($cash_ups_info->open_date, 0, 10);
				}
				else
				{
					$start_date = null;
				}
				if($cash_ups_info->close_date != null)
				{
					$end_date = substr($cash_ups_info->close_date, 0, 10);
				}
				else
				{
					$end_date = null;
				}
				// search for all the payments given the time range
				$inputs = [
					'start_date' => $start_date,
					'end_date' => $end_date,
					'sale_type' => 'complete',
					'location_id' => 'all'
				];
			}
			else
			{
				// search for all the payments given the time range
				$inputs = [
					'start_date' => $cash_ups_info->open_date,
					'end_date' => $cash_ups_info->close_date,
					'sale_type' => 'complete',
					'location_id' => 'all'
				];
			}

			// get all the transactions payment summaries
			$reports_data = $this->summary_payments->getData($inputs);

			foreach($reports_data as $row)
			{
				if($row['trans_group'] == lang('Reports.trans_payments'))
				{
					if($row['trans_type'] == lang('Sales.cash'))
					{
						$cash_ups_info->closed_amount_cash += $row['trans_amount'];
					}
					elseif($row['trans_type'] == lang('Sales.due'))
					{
						$cash_ups_info->closed_amount_due += $row['trans_amount'];
					}
					elseif($row['trans_type'] == lang('Sales.debit') ||
						$row['trans_type'] == lang('Sales.credit'))
					{
						$cash_ups_info->closed_amount_card += $row['trans_amount'];
					}
					elseif($row['trans_type'] == lang('Sales.check'))
					{
						$cash_ups_info->closed_amount_check += $row['trans_amount'];
					}
				}
			}

			// lookup expenses paid in cash
			$filters = [
						 'only_cash' => true,
						 'only_due' => false,
						 'only_check' => false,
						 'only_credit' => false,
						 'only_debit' => false,
						 'is_deleted' => false
			];

			$payments = $this->expense->get_payments_summary('', array_merge($inputs, $filters));

			foreach($payments as $row)
			{
				$cash_ups_info->closed_amount_cash -= $row['amount'];
			}

			$cash_ups_info->closed_amount_total = $this->_calculate_total($cash_ups_info->open_amount_cash, $cash_ups_info->transfer_amount_cash, $cash_ups_info->closed_amount_cash, $cash_ups_info->closed_amount_due, $cash_ups_info->closed_amount_card, $cash_ups_info->closed_amount_check);
		}

		$data['cash_ups_info'] = $cash_ups_info;

		echo view("cashups/form", $data);
	}

	/**
	 * @param int $row_id
	 * @return void
	 */
	public function getRow(int $row_id): void
	{
		$cash_ups_info = $this->cashup->get_info($row_id);
		$data_row = get_cash_up_data_row($cash_ups_info);

		echo json_encode($data_row);
	}

	/**
	 * @param int $cashup_id
	 * @return void
	 */
	public function postSave(int $cashup_id = NEW_ENTRY): void
	{
		$open_date = $this->request->getPost('open_date');
		$open_date_formatter = date_create_from_format($this->config['dateformat'] . ' ' . $this->config['timeformat'], $open_date);

		$close_date = $this->request->getPost('close_date');
		$close_date_formatter = date_create_from_format($this->config['dateformat'] . ' ' . $this->config['timeformat'], $close_date);

		$open_amount_cash = prepare_decimal($this->request->getPost('open_amount_cash'));
		$transfer_amount_cash = prepare_decimal($this->request->getPost('transfer_amount_cash'));
		$closed_amount_cash = prepare_decimal($this->request->getPost('closed_amount_cash'));
		$closed_amount_due = prepare_decimal($this->request->getPost('closed_amount_due'));
		$closed_amount_card = prepare_decimal($this->request->getPost('closed_amount_card'));
		$closed_amount_check = prepare_decimal($this->request->getPost('closed_amount_check'));
		$closed_amount_total = prepare_decimal($this->request->getPost('closed_amount_total'));

		$cash_up_data = [
			'open_date' => $open_date_formatter->format('Y-m-d H:i:s'),
			'close_date' => $close_date_formatter->format('Y-m-d H:i:s'),
			'open_amount_cash' => parse_decimals(filter_var($open_amount_cash, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)),
			'transfer_amount_cash' => parse_decimals(filter_var($transfer_amount_cash, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)),
			'closed_amount_cash' => parse_decimals(filter_var($closed_amount_cash, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)),
			'closed_amount_due' => parse_decimals(filter_var($closed_amount_due, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)),
			'closed_amount_card' => parse_decimals(filter_var($closed_amount_card, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)),
			'closed_amount_check' => parse_decimals(filter_var($closed_amount_check, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)),
			'closed_amount_total' => parse_decimals(filter_var($closed_amount_total, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)),
			'note' => $this->request->getPost('note') != null,
			'description' => $this->request->getPost('description', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
			'open_employee_id' => $this->request->getPost('open_employee_id', FILTER_SANITIZE_NUMBER_INT),
			'close_employee_id' => $this->request->getPost('close_employee_id', FILTER_SANITIZE_NUMBER_INT),
			'deleted' => $this->request->getPost('deleted') != null
		];

		if($this->cashup->save_value($cash_up_data, $cashup_id))
		{
			//New cashup_id
			if($cashup_id == NEW_ENTRY)
			{
				echo json_encode(['success' => true, 'message' => lang('Cashups.successful_adding'), 'id' => $cash_up_data['cashup_id']]);
			}
			else // Existing Cashup
			{
				echo json_encode(['success' => true, 'message' => lang('Cashups.successful_updating'), 'id' => $cashup_id]);
			}
		}
		else//failure
		{
			echo json_encode(['success' => false, 'message' => lang('Cashups.error_adding_updating'), 'id' => NEW_ENTRY]);
		}
	}

	/**
	 * @return void
	 */
	public function postDelete(): void
	{
		$cash_ups_to_delete = $this->request->getPost('ids', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		if($this->cashup->delete_list($cash_ups_to_delete))
		{
			echo json_encode(['success' => true, 'message' => lang('Cashups.successful_deleted') . ' ' . count($cash_ups_to_delete) . ' ' . lang('Cashups.one_or_multiple'), 'ids' => $cash_ups_to_delete]);
		}
		else
		{
			echo json_encode(['success' => false, 'message' => lang('Cashups.cannot_be_deleted'), 'ids' => $cash_ups_to_delete]);
		}
	}

	/**
	 * AJAX call from cashup input form to calculate the total. Called in the view.
	 */
	public function ajax_cashup_total(): void
	{
		$raw_open_amount_cash = $this->request->getPost('open_amount_cash');
		$raw_transfer_amount_cash = $this->request->getPost('transfer_amount_cash');
		$raw_closed_amount_cash = $this->request->getPost('closed_amount_cash');
		$raw_closed_amount_due = $this->request->getPost('closed_amount_due');
		$raw_closed_amount_card = $this->request->getPost('closed_amount_card');
		$raw_closed_amount_check = $this->request->getPost('closed_amount_check');

		$open_amount_cash = parse_decimals(filter_var(prepare_decimal($raw_open_amount_cash), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
		$transfer_amount_cash = parse_decimals(filter_var(prepare_decimal($raw_transfer_amount_cash), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
		$closed_amount_cash = parse_decimals(filter_var(prepare_decimal($raw_closed_amount_cash), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
		$closed_amount_due = parse_decimals(filter_var(prepare_decimal($raw_closed_amount_due), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
		$closed_amount_card = parse_decimals(filter_var(prepare_decimal($raw_closed_amount_card), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
		$closed_amount_check = parse_decimals(filter_var(prepare_decimal($raw_closed_amount_check), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));

		$total = $this->_calculate_total($open_amount_cash, $transfer_amount_cash, $closed_amount_due, $closed_amount_cash, $closed_amount_card, $closed_amount_check);	//TODO: hungarian notation

		echo json_encode(['total' => to_currency_no_money($total)]);
	}

	/**
	* Calculate total
	*/
	private function _calculate_total(float $open_amount_cash, float $transfer_amount_cash, float $closed_amount_due, float $closed_amount_cash, float $closed_amount_card, $closed_amount_check): float	//TODO: need to get rid of hungarian notation here. Also, the signature is pretty long.  Perhaps they need to go into an object or array?
	{
		return ($closed_amount_cash - $open_amount_cash - $transfer_amount_cash + $closed_amount_due + $closed_amount_card + $closed_amount_check);
	}
}
