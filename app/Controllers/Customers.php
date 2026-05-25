<?php

namespace App\Controllers;

use App\Libraries\Mailchimp_lib;
use App\Models\Attribute;
use App\Models\Customer;
use App\Models\Customer_rewards;
use App\Models\Tax_code;
use CodeIgniter\HTTP\DownloadResponse;
use CodeIgniter\HTTP\ResponseInterface;
use Config\OSPOS;
use Config\Services;
use stdClass;

class Customers extends Persons
{
    private string $listId;
    private Mailchimp_lib $mailchimpLib;
    private Customer_rewards $customerRewards;
    private Customer $customer;
    private Tax_code $taxCode;
    private array $appConfig;

    public function __construct()
    {
        parent::__construct('customers');
        $this->mailchimpLib = new Mailchimp_lib();
        $this->customerRewards = model(Customer_rewards::class);
        $this->customer = model(Customer::class);
        $this->taxCode = model(Tax_code::class);
        $this->appConfig = config(OSPOS::class)->settings;

        $encrypter = Services::encrypter();

        if (!empty($this->appConfig['mailchimp_list_id'])) {
            $this->listId = $encrypter->decrypt($this->appConfig['mailchimp_list_id']);
        } else {
            $this->listId = '';
        }
    }

    public function getIndex(): string
    {
        $data['table_headers'] = get_customer_manage_table_headers();

        return view('people/manage', $data);
    }

    public function getRow(int $rowId): ResponseInterface
    {
        $person = $this->customer->get_info($rowId);

        $stats = $this->customer->get_stats($person->person_id);

        if (empty($stats)) {
            $stats = new stdClass();
            $stats->total = 0;
            $stats->min = 0;
            $stats->max = 0;
            $stats->average = 0;
            $stats->avg_discount = 0;
            $stats->quantity = 0;
        }

        $dataRow = get_customer_data_row($person, $stats);

        return $this->response->setJSON($dataRow);
    }

    public function getSearch(): ResponseInterface
    {
        $search = $this->request->getGet('search');
        $limit = $this->request->getGet('limit', FILTER_SANITIZE_NUMBER_INT);
        $offset = $this->request->getGet('offset', FILTER_SANITIZE_NUMBER_INT);
        $sort = $this->sanitizeSortColumn(customer_headers(), $this->request->getGet('sort', FILTER_SANITIZE_FULL_SPECIAL_CHARS), 'people.person_id');
        $order = $this->request->getGet('order', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $customers = $this->customer->search($search, $limit, $offset, $sort, $order);
        $totalRows = $this->customer->get_found_rows($search);

        $dataRows = [];

        foreach ($customers->getResult() as $person) {
            $stats = $this->customer->get_stats($person->person_id);
            if (empty($stats)) {
                $stats = new stdClass();
                $stats->total = 0;
                $stats->min = 0;
                $stats->max = 0;
                $stats->average = 0;
                $stats->avg_discount = 0;
                $stats->quantity = 0;
            }

            $dataRows[] = get_customer_data_row($person, $stats);
        }

        return $this->response->setJSON(['total' => $totalRows, 'rows' => $dataRows]);
    }

    public function getSuggest(): ResponseInterface
    {
        $search = $this->request->getGet('term');
        $suggestions = $this->customer->get_search_suggestions($search);

        return $this->response->setJSON($suggestions);
    }

    public function suggestSearch(): ResponseInterface
    {
        $search = $this->request->getGet('term');
        $suggestions = $this->customer->get_search_suggestions($search, 25, false);

        return $this->response->setJSON($suggestions);
    }

    public function getView(int $customerId = NEW_ENTRY): string
    {
        if ($customerId == null) $customerId = NEW_ENTRY;

        $info = $this->customer->get_info($customerId);
        foreach (get_object_vars($info) as $property => $value) {
            $info->$property = $value;
        }
        $data['person_info'] = $info;

        if (empty($info->person_id) || empty($info->date) || empty($info->employee_id)) {
            $data['person_info']->date = date('Y-m-d H:i:s');
            $data['person_info']->employee_id = $this->employee->get_logged_in_employee_info()->person_id;
        }

        $employeeInfo = $this->employee->get_info($info->employee_id);
        $data['employee'] = $employeeInfo->first_name . ' ' . $employeeInfo->last_name;

        $taxCodeInfo = $this->taxCode->get_info($info->sales_tax_code_id);

        if ($taxCodeInfo->tax_code != null) {
            $data['sales_tax_code_label'] = $taxCodeInfo->tax_code . ' ' . $taxCodeInfo->tax_code_name;
        } else {
            $data['sales_tax_code_label'] = '';
        }

        $packages = ['' => lang('Items.none')];
        foreach ($this->customerRewards->get_all()->getResultArray() as $row) {
            $packages[$row['package_id']] = $row['package_name'];
        }
        $data['packages'] = $packages;
        $data['selected_package'] = $info->package_id;

        $data['use_destination_based_tax'] = $this->appConfig['use_destination_based_tax'];

        $stats = $this->customer->get_stats($customerId);
        if (!empty($stats)) {
            foreach (get_object_vars($stats) as $property => $value) {
                $info->$property = $value;
            }
            $data['stats'] = $stats;
        }

        if (!empty($info->email)) {
            if (($mailchimpInfo = $this->mailchimpLib->getMemberInfo($this->listId, $info->email)) !== false) {
                $data['mailchimp_info'] = $mailchimpInfo;

                if (($activities = $this->mailchimpLib->getMemberActivity($this->listId, $info->email)) !== false) {
                    if (array_key_exists('activity', $activities)) {
                        $open = 0;
                        $unopen = 0;
                        $click = 0;
                        $total = 0;
                        $lastopen = '';

                        foreach ($activities['activity'] as $activity) {
                            if ($activity['action'] == 'sent') {
                                ++$unopen;
                            } elseif ($activity['action'] == 'open') {
                                if (empty($lastopen)) {
                                    $lastopen = substr($activity['timestamp'], 0, 10);
                                }
                                ++$open;
                            } elseif ($activity['action'] == 'click') {
                                if (empty($lastopen)) {
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

        return view("customers/form", $data);
    }

    /**
     * Gets person attributes for a customer (AJAX)
     */
    public function getAttributes(int $customerId = NEW_ENTRY): string
    {
        return $this->getPersonAttributes($customerId, Attribute::SHOW_IN_CUSTOMERS);
    }

    public function postSave(int $customerId = NEW_ENTRY): ResponseInterface
    {
        $firstName = $this->request->getPost('first_name');
        $lastName = $this->request->getPost('last_name');
        $email = strtolower($this->request->getPost('email', FILTER_SANITIZE_EMAIL));

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

        $dateFormatter = date_create_from_format($this->appConfig['dateformat'] . ' ' . $this->appConfig['timeformat'], $this->request->getPost('date'));

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
            $personId = $customerId == NEW_ENTRY ? $customerData['person_id'] : $customerId;
            $this->savePersonAttributes($personId, Attribute::SHOW_IN_CUSTOMERS);

            $mailchimpStatus = $this->request->getPost('mailchimp_status');
            $this->mailchimpLib->addOrUpdateMember(
                $this->listId,
                $email,
                $firstName,
                $lastName,
                $mailchimpStatus == null ? "" : $mailchimpStatus,
                ['vip' => $this->request->getPost('mailchimp_vip') != null]
            );

            if ($customerId == NEW_ENTRY) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => lang('Customers.successful_adding') . ' ' . $firstName . ' ' . $lastName,
                    'id'      => $customerData['person_id']
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => lang('Customers.successful_updating') . ' ' . $firstName . ' ' . $lastName,
                    'id'      => $customerId
                ]);
            }
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => lang('Customers.error_adding_updating') . ' ' . $firstName . ' ' . $lastName,
                'id'      => NEW_ENTRY
            ]);
        }
    }

    public function postCheckEmail(): ResponseInterface
    {
        $email = strtolower($this->request->getPost('email', FILTER_SANITIZE_EMAIL));
        $personId = $this->request->getPost('person_id', FILTER_SANITIZE_NUMBER_INT);

        $exists = $this->customer->check_email_exists($email, $personId);

        return $this->response->setJSON(!$exists ? 'true' : 'false');
    }

    public function postCheckAccountNumber(): ResponseInterface
    {
        $exists = $this->customer->check_account_number_exists($this->request->getPost('account_number'), $this->request->getPost('person_id', FILTER_SANITIZE_NUMBER_INT));

        return $this->response->setJSON(!$exists ? 'true' : 'false');
    }

    public function postDelete(): ResponseInterface
    {
        $customersToDelete = $this->request->getPost('ids');
        $customersInfo = $this->customer->get_multiple_info($customersToDelete);

        $count = 0;

        foreach ($customersInfo->getResult() as $info) {
            if ($this->customer->delete($info->person_id)) {
                $this->mailchimpLib->removeMember($this->listId, $info->email);

                $count++;
            }
        }

        if ($count == count($customersToDelete)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => lang('Customers.successful_deleted') . ' ' . $count . ' ' . lang('Customers.one_or_multiple')
            ]);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => lang('Customers.cannot_be_deleted')]);
        }
    }

    public function getCsv(): DownloadResponse
    {
        $name = 'importCustomers.csv';
        $data = file_get_contents(WRITEPATH . "uploads/$name");
        return $this->response->download($name, $data);
    }

    public function getCsvImport(): string
    {
        return view('customers/form_csv_import');
    }

    public function postImportCsvFile(): ResponseInterface
    {
        if ($_FILES['file_path']['error'] != UPLOAD_ERR_OK) {
            return $this->response->setJSON(['success' => false, 'message' => lang('Customers.csv_import_failed')]);
        } else {
            if (($handle = fopen($_FILES['file_path']['tmp_name'], 'r')) !== false) {
                fgetcsv($handle);
                $i = 1;

                $failCodes = [];

                while (($data = fgetcsv($handle)) !== false) {
                    $consent = $data[3] == '' ? 0 : 1;

                    if (sizeof($data) >= 16 && $consent) {
                        $email = strtolower($data[4]);
                        $personData = [
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

                        $customerData = [
                            'consent'       => $consent,
                            'company_name'  => $data[13],
                            'discount'      => $data[15],
                            'discount_type' => $data[16],
                            'taxable'       => $data[17] == '' ? 0 : 1,
                            'date'          => date('Y-m-d H:i:s'),
                            'employee_id'   => $this->employee->get_logged_in_employee_info()->person_id
                        ];
                        $accountNumber = $data[14];

                        $invalidated = $this->customer->check_email_exists($email);

                        if ($accountNumber != '') {
                            $customerData['account_number'] = $accountNumber;
                            $invalidated &= $this->customer->check_account_number_exists($accountNumber);
                        }
                    } else {
                        $invalidated = true;
                    }

                    if ($invalidated) {
                        $failCodes[] = $i;
                        log_message('error', "Row $i was not imported: Either email or account number already exist or data was invalid.");
                    } elseif ($this->customer->save_customer($personData, $customerData)) {
                        $this->mailchimpLib->addOrUpdateMember($this->listId, $personData['email'], $personData['first_name'], '', $personData['last_name']);
                    } else {
                        $failCodes[] = $i;
                    }

                    ++$i;
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