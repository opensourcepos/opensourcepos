<?php

namespace App\Controllers;

use App\Libraries\Mailchimp_lib;

use App\Models\Customer;
use App\Models\Customer_rewards;
use App\Models\Tax_code;

use CodeIgniter\Encryption\Encryption;
use CodeIgniter\Encryption\EncrypterInterface;
use Config\Services;
use stdClass;

/**
 *
 *
 * @property mailchimp_lib mailchimp_lib
 *
 * @property customer customer
 * @property customer_rewards customer_rewards
 * @property tax_code tax_code
 *
 * @property encryption encryption
 * @property encrypterinterface encrypter
 * @property array config
 *
 */
class Customers extends Persons
{
	private $_list_id;

	public function __construct()
	{
		parent::__construct('customers');
		$this->mailchimp_lib = new Mailchimp_lib();
		$this->customer_rewards = model('Customer_rewards');
		$this->customer = model('Customer');
		$this->tax_code = model('Tax_code');
		$this->config = config('OSPOS')->settings;

		$encrypter = Services::encrypter();

		$mailchimp_list_id = $this->config['mailchimp_list_id'];

		if(!empty($mailchimp_list_id))
		{
			$this->_list_id = $encrypter->decrypt($this->config['mailchimp_list_id']);
		}
		else
		{
			$this->_list_id = '';
		}
	}

	public function getIndex(): void
	{
		$data['table_headers'] = get_customer_manage_table_headers();

		echo view('people/manage', $data);
	}

	/**
	 * Gets one row for a customer manage table. This is called using AJAX to update one row.
	 */
	public function getRow(int $row_id): void
	{
		$person = $this->customer->get_info($row_id);

		// retrieve the total amount the customer spent so far together with min, max and average values
		$stats = $this->customer->get_stats($person->person_id);	//TODO: This and the next 11 lines are duplicated in search().  Extract a method.

		if(empty($stats))
		{
			//create object with empty properties.
			$stats = new stdClass();
			$stats->total = 0;
			$stats->min = 0;
			$stats->max = 0;
			$stats->average = 0;
			$stats->avg_discount = 0;
			$stats->quantity = 0;
		}

		$data_row = get_customer_data_row($person, $stats);

		echo json_encode($data_row);
	}

	/*
	Returns customer table data rows. This will be called with AJAX.
	*/
	public function getSearch()
	{
		$search = $this->request->getGet('search');
		$limit  = $this->request->getGet('limit', FILTER_SANITIZE_NUMBER_INT);
		$offset = $this->request->getGet('offset', FILTER_SANITIZE_NUMBER_INT);
		$sort   = $this->request->getGet('sort');
		$order  = $this->request->getGet('order');

		$customers = $this->customer->search($search, $limit, $offset, $sort, $order);
		$total_rows = $this->customer->get_found_rows($search);

		$data_rows = [];

		foreach($customers->getResult() as $person)
		{
			// retrieve the total amount the customer spent so far together with min, max and average values
			$stats = $this->customer->get_stats($person->person_id);	//TODO: duplicated... see above
			if(empty($stats))
			{
				//create object with empty properties.
				$stats = new stdClass();
				$stats->total = 0;
				$stats->min = 0;
				$stats->max = 0;
				$stats->average = 0;
				$stats->avg_discount = 0;
				$stats->quantity = 0;
			}

			$data_rows[] = get_customer_data_row($person, $stats);
		}

		echo json_encode (['total' => $total_rows, 'rows' => $data_rows]);
	}

	/**
	 * Gives search suggestions based on what is being searched for
	 */
	public function getSuggest(): void
	{
		$suggestions = $this->customer->get_search_suggestions($this->request->getVar('term'), 25,TRUE);

		echo json_encode($suggestions);
	}

	public function suggest_search(): void
	{
		$suggestions = $this->customer->get_search_suggestions($this->request->getPost('term'), 25, FALSE);

		echo json_encode($suggestions);
	}

	/**
	 * Loads the customer edit form
	 */
	public function getView(int $customer_id = NEW_ENTRY): void
	{
		// Set default values
		if($customer_id == null) $customer_id = NEW_ENTRY;

		$info = $this->customer->get_info($customer_id);
		foreach(get_object_vars($info) as $property => $value)
		{
			$info->$property = $value;
		}
		$data['person_info'] = $info;

		if(empty($info->person_id) || empty($info->date) || empty($info->employee_id))
		{
			$data['person_info']->date = date('Y-m-d H:i:s');
			$data['person_info']->employee_id = $this->employee->get_logged_in_employee_info()->person_id;
		}

		$employee_info = $this->employee->get_info($info->employee_id);
		$data['employee'] = $employee_info->first_name . ' ' . $employee_info->last_name;

		$tax_code_info = $this->tax_code->get_info($info->sales_tax_code_id);

		if($tax_code_info->tax_code != NULL)
		{
			$data['sales_tax_code_label'] = $tax_code_info->tax_code . ' ' . $tax_code_info->tax_code_name;
		}
		else
		{
			$data['sales_tax_code_label'] = '';
		}

		$packages = ['' => lang('Items.none')];
		foreach($this->customer_rewards->get_all()->getResultArray() as $row)
		{
			$packages[$row['package_id']] = $row['package_name'];
		}
		$data['packages'] = $packages;
		$data['selected_package'] = $info->package_id;

		if($this->config['use_destination_based_tax'])	//TODO: This can be shortened for ternary notation
		{
			$data['use_destination_based_tax'] = TRUE;
		}
		else
		{
			$data['use_destination_based_tax'] = FALSE;
		}

		// retrieve the total amount the customer spent so far together with min, max and average values
		$stats = $this->customer->get_stats($customer_id);
		if(!empty($stats))
		{
			foreach(get_object_vars($stats) as $property => $value)
			{
				$info->$property = $value;
			}
			$data['stats'] = $stats;
		}

		// retrieve the info from Mailchimp only if there is an email address assigned
		if(!empty($info->email))
		{
			// collect mailchimp customer info
			if(($mailchimp_info = $this->mailchimp_lib->getMemberInfo($this->_list_id, $info->email)) !== FALSE)
			{
				$data['mailchimp_info'] = $mailchimp_info;

				// collect customer mailchimp emails activities (stats)
				if(($activities = $this->mailchimp_lib->getMemberActivity($this->_list_id, $info->email)) !== FALSE)
				{
					if(array_key_exists('activity', $activities))
					{
						$open = 0;
						$unopen = 0;
						$click = 0;
						$total = 0;
						$lastopen = '';

						foreach($activities['activity'] as $activity)
						{
							if($activity['action'] == 'sent')
							{
								++$unopen;
							}
							elseif($activity['action'] == 'open')
							{
								if(empty($lastopen))
								{
									$lastopen = substr($activity['timestamp'], 0, 10);
								}
								++$open;
							}
							elseif($activity['action'] == 'click')
							{
								if(empty($lastopen))
								{
									$lastopen = substr($activity['timestamp'], 0, 10);
								}
								++$click;
							}

							++$total;
						}

						$data['mailchimp_activity']['total'] = $total;
						$data['mailchimp_activity']['open'] = $open;
						$data['mailchimp_activity']['unopen'] = $unopen;
						$data['mailchimp_activity']['click'] = $click;
						$data['mailchimp_activity']['lastopen'] = $lastopen;
					}
				}
			}
		}

		echo view("customers/form", $data);
	}

	/**
	 * Inserts/updates a customer
	 */
	public function postSave(int $customer_id = NEW_ENTRY): void
	{
		$first_name = $this->request->getPost('first_name');
		$last_name = $this->request->getPost('last_name');
		$email = strtolower($this->request->getPost('email', FILTER_SANITIZE_EMAIL));

		// format first and last name properly
		$first_name = $this->nameize($first_name);
		$last_name = $this->nameize($last_name);

		$person_data = [
			'first_name' => $first_name,
			'last_name' => $last_name,
			'gender' => $this->request->getPost('gender', FILTER_SANITIZE_NUMBER_INT),
			'email' => $email,
			'phone_number' => $this->request->getPost('phone_number'),
			'address_1' => $this->request->getPost('address_1'),
			'address_2' => $this->request->getPost('address_2'),
			'city' => $this->request->getPost('city'),
			'state' => $this->request->getPost('state'),
			'zip' => $this->request->getPost('zip'),
			'country' => $this->request->getPost('country'),
			'comments' => $this->request->getPost('comments')
		];

		$date_formatter = date_create_from_format($this->config['dateformat'] . ' ' . $this->config['timeformat'], $this->request->getPost('date'));

		$customer_data = [
			'consent' => $this->request->getPost('consent') != NULL,
			'account_number' => $this->request->getPost('account_number') == '' ? NULL : $this->request->getPost('account_number'),
			'tax_id' => $this->request->getPost('tax_id'),
			'company_name' => $this->request->getPost('company_name') == '' ? NULL : $this->request->getPost('company_name'),
			'discount' => $this->request->getPost('discount') == '' ? 0.00 : $this->request->getPost('discount', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
			'discount_type' => $this->request->getPost('discount_type') == NULL ? PERCENT : $this->request->getPost('discount_type', FILTER_SANITIZE_NUMBER_INT),
			'package_id' => $this->request->getPost('package_id') == '' ? NULL : $this->request->getPost('package_id'),
			'taxable' => $this->request->getPost('taxable') != NULL,
			'date' => $date_formatter->format('Y-m-d H:i:s'),
			'employee_id' => $this->request->getPost('employee_id', FILTER_SANITIZE_NUMBER_INT),
			'sales_tax_code_id' => $this->request->getPost('sales_tax_code_id') == '' ? NULL : $this->request->getPost('sales_tax_code_id', FILTER_SANITIZE_NUMBER_INT)
		];

		if($this->customer->save_customer($person_data, $customer_data, $customer_id))
		{
			// save customer to Mailchimp selected list	//TODO: addOrUpdateMember should be refactored... potentially pass an array or object instead of 6 parameters.
			$mailchimp_status = $this->request->getPost('mailchimp_status');
			$this->mailchimp_lib->addOrUpdateMember(
				$this->_list_id,
				$email,
				$first_name,
				$last_name,
				$mailchimp_status == null ? "" : $mailchimp_status,
				['vip' => $this->request->getPost('mailchimp_vip') != NULL]
			);

			// New customer
			if($customer_id == NEW_ENTRY)
			{
				echo json_encode ([
					'success' => TRUE,
					'message' => lang('Customers.successful_adding') . ' ' . $first_name . ' ' . $last_name,
					'id' => $customer_data['person_id']
				]);
			}
			else // Existing customer
			{
				echo json_encode ([
					'success' => TRUE,
					'message' => lang('Customers.successful_updating') . ' ' . $first_name . ' ' . $last_name,
					'id' => $customer_id
				]);
			}
		}
		else // Failure
		{
			echo json_encode ([
				'success' => FALSE,
				'message' => lang('Customers.error_adding_updating') . ' ' . $first_name . ' ' . $last_name,
				'id' => NEW_ENTRY
			]);
		}
	}

	/**
	 * AJAX call to verify if an email address already exists
	 */
	public function postCheckEmail(): void
	{
		$exists = $this->customer->check_email_exists(strtolower($this->request->getPost('email')), $this->request->getPost('person_id', FILTER_SANITIZE_NUMBER_INT));

		echo !$exists ? 'true' : 'false';
	}

	/**
	 * AJAX call to verify if an account number already exists
	 */
	public function postCheckAccountNumber(): void
	{
		$exists = $this->customer->check_account_number_exists($this->request->getPost('account_number'), $this->request->getPost('person_id', FILTER_SANITIZE_NUMBER_INT));

		echo !$exists ? 'true' : 'false';
	}

	/**
	 * This deletes customers from the customers table
	 */
	public function postDelete(): void
	{
		$customers_to_delete = $this->request->getPost('ids');
		$customers_info = $this->customer->get_multiple_info($customers_to_delete);

		$count = 0;

		foreach($customers_info->getResult() as $info)
		{
			if($this->customer->delete($info->person_id))
			{
				// remove customer from Mailchimp selected list
				$this->mailchimp_lib->removeMember($this->_list_id, $info->email);

				$count++;
			}
		}

		if($count == count($customers_to_delete))
		{
			echo json_encode (['success' => TRUE,
				'message' => lang('Customers.successful_deleted') . ' ' . $count . ' ' . lang('Customers.one_or_multiple')]);
		}
		else
		{
			echo json_encode (['success' => FALSE, 'message' => lang('Customers.cannot_be_deleted')]);
		}
	}

	/**
	 * Customers import from csv spreadsheet
	 */
	public function csv(): void
	{
		$name = 'import_customers.csv';
		$data = file_get_contents('../' . $name);
		force_download($name, $data);
	}

	public function getCsvImport(): void
	{
		echo view('customers/form_csv_import');
	}

	public function do_csv_import(): void
	{
		if($_FILES['file_path']['error'] != UPLOAD_ERR_OK)
		{
			echo json_encode (['success' => FALSE, 'message' => lang('Customers.csv_import_failed')]);
		}
		else
		{
			if(($handle = fopen($_FILES['file_path']['tmp_name'], 'r')) !== FALSE)
			{
				// Skip the first row as it's the table description
				fgetcsv($handle);
				$i = 1;

				$failCodes = [];

				while(($data = fgetcsv($handle)) !== FALSE)
				{
					$consent = $data[3] == '' ? 0 : 1;

					if(sizeof($data) >= 16 && $consent)
					{
						$email = strtolower($data[4]);
						$person_data = [
							'first_name' => $data[0],
							'last_name' => $data[1],
							'gender' => $data[2],
							'email' => $email,
							'phone_number' => $data[5],
							'address_1' => $data[6],
							'address_2' => $data[7],
							'city' => $data[8],
							'state' => $data[9],
							'zip' => $data[10],
							'country' => $data[11],
							'comments' => $data[12]
						];

						$customer_data = [
							'consent' => $consent,
							'company_name' => $data[13],
							'discount' => $data[15],
							'discount_type' => $data[16],
							'taxable' => $data[17] == '' ? 0 : 1,
							'date' => date('Y-m-d H:i:s'),
							'employee_id' => $this->employee->get_logged_in_employee_info()->person_id
						];
						$account_number = $data[14];

						// don't duplicate people with same email
						$invalidated = $this->customer->check_email_exists($email);

						if($account_number != '')
						{
							$customer_data['account_number'] = $account_number;
							$invalidated &= $this->customer->check_account_number_exists($account_number);
						}
					}
					else
					{
						$invalidated = TRUE;
					}

					if($invalidated)
					{
						$failCodes[] = $i;
					}
					elseif($this->customer->save_customer($person_data, $customer_data))
					{
						// save customer to Mailchimp selected list
						$this->mailchimp_lib->addOrUpdateMember($this->_list_id, $person_data['email'], $person_data['first_name'], '', $person_data['last_name']);
					}
					else
					{
						$failCodes[] = $i;
					}

					++$i;
				}

				if(count($failCodes) > 0)
				{
					$message = lang('Customers.csv_import_partially_failed', [count($failCodes), implode(', ', $failCodes)]);

					echo json_encode (['success' => FALSE, 'message' => $message]);
				}
				else
				{
					echo json_encode (['success' => TRUE, 'message' => lang('Customers.csv_import_success')]);
				}
			}
			else
			{
				echo json_encode (['success' => FALSE, 'message' => lang('Customers.csv_import_nodata_wrongformat')]);
			}
		}
	}
}
