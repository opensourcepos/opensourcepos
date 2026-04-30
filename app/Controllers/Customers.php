<?php

namespace App\Controllers;

use App\Models\Customer;
use App\Models\Customer_rewards;
use App\Models\Tax_code;
use CodeIgniter\Events\Events;
use CodeIgniter\HTTP\DownloadResponse;
use CodeIgniter\HTTP\ResponseInterface;
use Config\OSPOS;
use Config\Services;
use stdClass;

class Customers extends Persons
{
    private Customer_rewards $customer_rewards;
    private Customer $customer;
    private Tax_code $tax_code;
    private array $config;

    public function __construct()
    {
        parent::__construct('customers');

        $this->customer_rewards = model(Customer_rewards::class);
        $this->customer = model(Customer::class);
        $this->tax_code = model(Tax_code::class);
        $this->config = config(OSPOS::class)->settings;
    }

    /**
     * @return string
     */
    public function getIndex(): string
    {
        $data['table_headers'] = get_customer_manage_table_headers();

        return view('people/manage', $data);
    }

    /**
     * Gets one row for a customer manage table. This is called using AJAX to update one row.
     * @param int $row_id
     * @return ResponseInterface
     */
    public function getRow(int $row_id): ResponseInterface
    {
        $person = $this->customer->get_info($row_id);

        // Retrieve the total amount the customer spent so far together with min, max and average values
        $stats = $this->customer->get_stats($person->person_id);    // TODO: This and the next 11 lines are duplicated in search().  Extract a method.

        if (empty($stats)) {
            // Create object with empty properties.
            $stats = new stdClass();
            $stats->total = 0;
            $stats->min = 0;
            $stats->max = 0;
            $stats->average = 0;
            $stats->avg_discount = 0;
            $stats->quantity = 0;
        }

        $data_row = get_customer_data_row($person, $stats);

        return $this->response->setJSON($data_row);
    }


    /**
     * Returns customer table data rows. This will be called with AJAX.
     *
     * @return void
     */
    public function getSearch(): ResponseInterface
    {
        $search = $this->request->getGet('search');
        $limit = $this->request->getGet('limit', FILTER_SANITIZE_NUMBER_INT);
        $offset = $this->request->getGet('offset', FILTER_SANITIZE_NUMBER_INT);
        $sort = $this->sanitizeSortColumn(customer_headers(), $this->request->getGet('sort', FILTER_SANITIZE_FULL_SPECIAL_CHARS), 'people.person_id');
        $order = $this->request->getGet('order', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $customers = $this->customer->search($search, $limit, $offset, $sort, $order);
        $total_rows = $this->customer->get_found_rows($search);

        $data_rows = [];

        foreach ($customers->getResult() as $person) {
            // Retrieve the total amount the customer spent so far together with min, max and average values
            $stats = $this->customer->get_stats($person->person_id);    // TODO: duplicated... see above
            if (empty($stats)) {
                // Create object with empty properties.
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

        return $this->response->setJSON(['total' => $total_rows, 'rows' => $data_rows]);
    }

    /**
     * Gives search suggestions based on what is being searched for
     * @return ResponseInterface
     */
    public function getSuggest(): ResponseInterface
    {
        $search = $this->request->getGet('term');
        $suggestions = $this->customer->get_search_suggestions($search);

        return $this->response->setJSON($suggestions);
    }

    /**
     * @return ResponseInterface
     */
    public function suggest_search(): ResponseInterface
    {
        $search = $this->request->getGet('term');
        $suggestions = $this->customer->get_search_suggestions($search, 25, false);

        return $this->response->setJSON($suggestions);
    }

    /**
     * Loads the customer edit form
     * @param int $customerId
     * @return string
     */
    public function getView(int $customerId = NEW_ENTRY): string
    {
        if ($customerId == null) {
            $customerId = NEW_ENTRY;
        }

        $info = $this->customer->get_info($customerId);
        foreach (get_object_vars($info) as $property => $value) {
            $info->$property = $value;
        }
        $data['person_info'] = $info;

        if (empty($info->person_id) || empty($info->date) || empty($info->employee_id)) {
            $data['person_info']->date = date('Y-m-d H:i:s');
            $data['person_info']->employee_id = $this->employee->get_logged_in_employee_info()->person_id;
        }

        $employee_info = $this->employee->get_info($info->employee_id);
        $data['employee'] = $employee_info->first_name . ' ' . $employee_info->last_name;

        $tax_code_info = $this->tax_code->get_info($info->sales_tax_code_id);

        if ($tax_code_info->tax_code != null) {
            $data['sales_tax_code_label'] = $tax_code_info->tax_code . ' ' . $tax_code_info->tax_code_name;
        } else {
            $data['sales_tax_code_label'] = '';
        }

        $packages = ['' => lang('Items.none')];
        foreach ($this->customer_rewards->get_all()->getResultArray() as $row) {
            $packages[$row['package_id']] = $row['package_name'];
        }
        $data['packages'] = $packages;
        $data['selected_package'] = $info->package_id;

        $data['use_destination_based_tax'] = $this->config['use_destination_based_tax'];

        // Retrieve the total amount the customer spent so far together with min, max and average values
        $stats = $this->customer->get_stats($customerId);
        if (!empty($stats)) {
            foreach (get_object_vars($stats) as $property => $value) {
                $info->$property = $value;
            }
            $data['stats'] = $stats;
        }

        Events::trigger('customer_loaded', $info);

        return view("customers/form", $data);
    }

    /**
     * Inserts/updates a customer
     * @param int $customerId
     * @return ResponseInterface
     */
    public function postSave(int $customerId = NEW_ENTRY): ResponseInterface
    {
        $firstName = $this->request->getPost('first_name');
        $lastName = $this->request->getPost('last_name');
        $email = strtolower($this->request->getPost('email', FILTER_SANITIZE_EMAIL));

        // Format first and last name properly
        $firstName = $this->nameize($firstName);
        $lastName = $this->nameize($lastName);

        $personData = [
            'first_name'   => $firstName,
            'last_name'    => $lastName,
            'gender'       => $this->request->getPost('gender', FILTER_SANITIZE_NUMBER_INT),
            'email'        => $email,
            'phone_number' => $this->request->getPost('phone_number'),
            'address_1'    => $this->request->getPost('address_1'),
            'address_2'    => $this->request->getPost('address_2'),
            'city'         => $this->request->getPost('city'),
            'state'        => $this->request->getPost('state'),
            'zip'          => $this->request->getPost('zip'),
            'country'      => $this->request->getPost('country'),
            'comments'     => $this->request->getPost('comments')
        ];

        $dateFormatter = date_create_from_format($this->config['dateformat'] . ' ' . $this->config['timeformat'], $this->request->getPost('date'));

        $customerData = [
            'consent'           => $this->request->getPost('consent') != null,
            'account_number'    => $this->request->getPost('account_number') == '' ? null : $this->request->getPost('account_number'),
            'tax_id'            => $this->request->getPost('tax_id'),
            'company_name'      => $this->request->getPost('company_name') == '' ? null : $this->request->getPost('company_name'),
            'discount'          => $this->request->getPost('discount') == '' ? 0.00 : parse_decimals($this->request->getPost('discount')),
            'discount_type'     => $this->request->getPost('discount_type') == null ? PERCENT : $this->request->getPost('discount_type', FILTER_SANITIZE_NUMBER_INT),
            'package_id'        => $this->request->getPost('package_id') == '' ? null : $this->request->getPost('package_id'),
            'taxable'           => $this->request->getPost('taxable') != null,
            'date'              => $dateFormatter->format('Y-m-d H:i:s'),
            'employee_id'       => $this->request->getPost('employee_id', FILTER_SANITIZE_NUMBER_INT),
            'sales_tax_code_id' => $this->request->getPost('sales_tax_code_id') == '' ? null : $this->request->getPost('sales_tax_code_id', FILTER_SANITIZE_NUMBER_INT)
        ];

        if ($this->customer->save_customer($personData, $customerData, $customerId)) {
            Events::trigger('customer_saved', $customerData);

            // New customer
            if ($customerId == NEW_ENTRY) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => lang('Customers.successful_adding') . " $firstName $lastName",
                    'id'      => $customerData['person_id']
                ]);
            } else { // Existing customer
                return $this->response->setJSON([
                    'success' => true,
                    'message' => lang('Customers.successful_updating') . " $firstName $lastName",
                    'id'      => $customerId
                ]);
            }
        } else { // Failure
            return $this->response->setJSON([
                'success' => false,
                'message' => lang('Customers.error_adding_updating') . " $firstName $lastName",
                'id'      => NEW_ENTRY
            ]);
        }
    }

    /**
     * Verifies if an email address already exists. Used in app/Views/customers/form.php
     *
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function postCheckEmail(): ResponseInterface
    {
        $email = strtolower($this->request->getPost('email', FILTER_SANITIZE_EMAIL));
        $person_id = $this->request->getPost('person_id', FILTER_SANITIZE_NUMBER_INT);

        $exists = $this->customer->check_email_exists($email, $person_id);

        return $this->response->setJSON(!$exists ? 'true' : 'false');
    }

    /**
     * Verifies if an account number already exists. Used in app/Views/customers/form.php
     *
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function postCheckAccountNumber(): ResponseInterface
    {
        $exists = $this->customer->check_account_number_exists($this->request->getPost('account_number'), $this->request->getPost('person_id', FILTER_SANITIZE_NUMBER_INT));

        return $this->response->setJSON(!$exists ? 'true' : 'false');
    }

    /**
     * This deletes customers from the customers table
     * @return ResponseInterface
     */
    public function postDelete(): ResponseInterface
    {
        $customersToDelete = $this->request->getPost('ids');
        $customers = $this->customer->get_multiple_info($customersToDelete);

        $count = 0;
        foreach ($customers->getResult() as $customer) {
            if ($this->customer->delete($customer->person_id)) {
                Events::trigger('customer_deleted', $customer);
                $count++;
            }
        }

        if ($count === count($customersToDelete)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => lang('Customers.successful_deleted') . ' ' . $count . ' ' . lang('Customers.one_or_multiple')
            ]);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => lang('Customers.cannot_be_deleted')]);
        }
    }

    /**
     * Customers import from csv spreadsheet
     *
     * @return DownloadResponse The template for Customer CSV imports is returned and download forced.
     * @noinspection PhpUnused
     */
    public function getCsv(): DownloadResponse
    {
        $name = 'importCustomers.csv';
        $data = file_get_contents(WRITEPATH . "uploads/$name");
        return $this->response->download($name, $data);
    }

    /**
     * Displays the customer CSV import modal. Used in app/Views/people/manage.php
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function getCsvImport(): string
    {
        return view('customers/form_csv_import');
    }

    /**
     * Imports a CSV file containing customers. Used in app/Views/customers/form_csv_import.php
     *
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function postImportCsvFile(): ResponseInterface
    {
        if ($_FILES['file_path']['error'] != UPLOAD_ERR_OK) {
            return $this->response->setJSON(['success' => false, 'message' => lang('Customers.csv_import_failed')]);
        } else {
            if (($handle = fopen($_FILES['file_path']['tmp_name'], 'r')) !== false) {
                // Skip the first row as it's the table description
                fgetcsv($handle);
                $rowNumber = 1;

                $failCodes = [];

                while (($data = fgetcsv($handle)) !== false) {
                    $consent = $data[3] == '' ? 0 : 1;

                    if (sizeof($data) >= 16 && $consent) {
                        $email = strtolower($data[4]);
                        $person_data = [
                            'first_name'   => $data[0],
                            'last_name'    => $data[1],
                            'gender'       => $data[2],
                            'email'        => $email,
                            'phone_number' => $data[5],
                            'address_1'    => $data[6],
                            'address_2'    => $data[7],
                            'city'         => $data[8],
                            'state'        => $data[9],
                            'zip'          => $data[10],
                            'country'      => $data[11],
                            'comments'     => $data[12]
                        ];

                        $customer_data = [
                            'consent'       => $consent,
                            'company_name'  => $data[13],
                            'discount'      => $data[15],
                            'discount_type' => $data[16],
                            'taxable'       => $data[17] == '' ? 0 : 1,
                            'date'          => date('Y-m-d H:i:s'),
                            'employee_id'   => $this->employee->get_logged_in_employee_info()->person_id
                        ];
                        $account_number = $data[14];

                        // Don't duplicate people with same email
                        $invalidated = $this->customer->check_email_exists($email);

                        if ($account_number != '') {
                            $customer_data['account_number'] = $account_number;
                            $invalidated &= $this->customer->check_account_number_exists($account_number);
                        }
                    } else {
                        $invalidated = true;
                    }

                    if ($invalidated) {
                        $failCodes[] = $rowNumber;
                        log_message('error', "Row $rowNumber was not imported: Either email or account number already exist or data was invalid.");
                    } elseif ($this->customer->save_customer($person_data, $customer_data)) {
                        Events::trigger('customer_saved', $person_data);
                    } else {
                        $failCodes[] = $rowNumber;
                    }

                    ++$rowNumber;
                }

                if (count($failCodes) > 0) {
                    $message = lang('Customers.csv_import_partially_failed', [count($failCodes), implode(', ', $failCodes)]);

                    return $this->response->setJSON(['success' => false, 'message' => $message]);
                } else {
                    return $this->response->setJSON(['success' => true, 'message' => lang('Customers.csv_import_success')]);
                }
            } else {
                return $this->response->setJSON(['success' => false, 'message' => lang('Customers.csv_import_nodata_wrongformat')]);
            }
        }
    }
}
