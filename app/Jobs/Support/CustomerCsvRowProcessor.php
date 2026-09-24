<?php

namespace App\Jobs\Support;

use App\Libraries\Mailchimp_lib;
use App\Models\Customer;
use Config\OSPOS;
use Config\Services;

/**
 * Per-row Customers CSV import logic (issue #3833 Phase 3), extracted from
 * Customers::postImportCsvFile() so it can run both synchronously
 * (controller) and from CustomerImportJob (queue worker, no HTTP/session
 * context). Each row commits independently — same partial-success behavior
 * as the original method (no shared transaction across rows).
 *
 * Row shape (18 columns, positional): First Name, Last Name, Gender,
 * Consent, Email, Phone Number, Address 1, Address 2, City, State, Zip,
 * Country, Comments, Company, Account Number, Discount, Discount Type,
 * Taxable — matches writable/uploads/importCustomers.csv.
 */
class CustomerCsvRowProcessor
{
    public const REQUIRED_HEADERS = [
        'First Name', 'Last Name', 'Gender', 'Consent', 'Email', 'Phone Number',
        'Address 1', 'Address2', 'City', 'State', 'Zip', 'Country', 'Comments',
        'Company', 'Account Number', 'Discount', 'Discount_Type', 'Taxable',
    ];

    private Customer $customer;
    private Mailchimp_lib $mailchimpLib;
    private string $listId;

    public function __construct()
    {
        $this->customer = model(Customer::class);
        $this->mailchimpLib = new Mailchimp_lib();

        $config = config(OSPOS::class)->settings;
        $encrypter = Services::encrypter();

        $this->listId = empty($config['mailchimp_list_id']) ? '' : $encrypter->decrypt($config['mailchimp_list_id']);
    }

    /**
     * @param array $data Positional CSV row values (see REQUIRED_HEADERS for column order).
     * @param int $employeeId Employee id of the user who started the import.
     * @return bool True on success, false if the row failed validation or save.
     */
    public function process(array $data, int $employeeId): bool
    {
        $consent = $data[3] == '' ? 0 : 1;

        if (count($data) < 16 || !$consent) {
            return false;
        }

        $email = filter_var(strtolower($data[4]), FILTER_SANITIZE_EMAIL);

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            log_message('error', 'CSV Customer import failed: invalid email format');

            return false;
        }

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
            'comments'     => $data[12],
        ];

        $customerData = [
            'consent'       => $consent,
            'company_name'  => $data[13],
            'discount'      => $data[15],
            'discount_type' => $data[16],
            'taxable'       => $data[17] == '' ? 0 : 1,
            'date'          => date('Y-m-d H:i:s'),
            'employee_id'   => $employeeId,
        ];

        $accountNumber = $data[14];

        $invalidated = $this->customer->check_email_exists($email);

        if ($accountNumber != '') {
            $customerData['account_number'] = $accountNumber;
            $invalidated &= $this->customer->check_account_number_exists($accountNumber);
        }

        if ($invalidated) {
            log_message('error', 'CSV Customer import failed: email or account number already exists, or data was invalid.');

            return false;
        }

        if (!$this->customer->save_customer($personData, $customerData)) {
            return false;
        }

        $this->mailchimpLib->addOrUpdateMember($this->listId, $personData['email'], $personData['first_name'], '', $personData['last_name']);

        return true;
    }
}
