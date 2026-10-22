<?php

namespace Tests\Controllers;

use CodeIgniter\Database\Config;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use App\Models\Customer;
use App\Models\Employee;

class CustomersCsvImportTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $migrateOnce = true;
    protected $refresh = false;
    protected $namespace = null;

    protected Customer $customer;
    protected Employee $employee;

    private static bool $doneBootstrap = false;

    protected function setUp(): void
    {
        // Reset the test database to a clean schema on the first test in this
        // class so leftover customers with the same emails from prior runs
        // cannot be rejected as duplicates by the import (check_email_exists).
        if (self::$doneBootstrap === false) {
            Config::seeder($this->DBGroup)->call('App\Database\Seeds\TestDatabaseBootstrapSeeder');
            Config::connect($this->DBGroup)->close();

            self::$doneBootstrap = true;
        }

        parent::setUp();
        // Reset any stale transaction state left on the shared in-process DB
        // connection by a previous test.
        $this->db->resetTransStatus();

        $this->customer = model(Customer::class);
        $this->employee = model(Employee::class);

        helper('test');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    protected function loginAsEmployee(): void
    {
        $this->withSession(['person_id' => 1, 'menu_group' => 'office']);
    }

    protected function createCsvFile(array $rows): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'csv_test_');
        
        $handle = fopen($tempFile, 'w');
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);
        
        return $tempFile;
    }

    protected function findCustomerByEmail(string $email): ?array
    {
        return $this->customer
            ->select('customers.*, people.*')
            ->join('people', 'people.person_id = customers.person_id')
            ->where('people.email', $email)
            ->first();
    }

    protected function findCustomersByEmailLike(string $needle): ?array
    {
        return $this->customer
            ->select('customers.*, people.*')
            ->join('people', 'people.person_id = customers.person_id')
            ->where('people.email LIKE', '%' . $needle . '%')
            ->first();
    }

    public function testValidEmailIsAccepted(): void
    {
        $this->loginAsEmployee();

        $csvContent = [
            ['First Name', 'Last Name', 'Gender', 'Consent', 'Email', 'Phone', 'Address 1', 'Address 2', 'City', 'State', 'Zip', 'Country', 'Comments', 'Company', 'Account Number', 'Discount', 'Discount Type', 'Taxable'],
            ['John', 'Doe', '1', '1', 'john.doe@example.com', '555-1234', '123 Main St', '', 'Springfield', 'IL', '62701', 'US', '', '', '', '', '', '']
        ];

        $tempFile = $this->createCsvFile($csvContent);

        $_FILES['file_path'] = [
            'name' => 'test.csv',
            'type' => 'text/csv',
            'tmp_name' => $tempFile,
            'error' => UPLOAD_ERR_OK,
            'size' => filesize($tempFile)
        ];

        $result = $this->post('/customers/importCsvFile');

        $result->assertOK();
        $resultBody = json_decode($result->getJSON(), true);
        $this->assertTrue($resultBody['success'], 'Import should fully succeed');

        $importedCustomer = $this->findCustomerByEmail('john.doe@example.com');
        $this->assertNotNull($importedCustomer);

        unlink($tempFile);
    }

    public function testInvalidEmailIsRejected(): void
    {
        $this->loginAsEmployee();

        $csvContent = [
            ['First Name', 'Last Name', 'Gender', 'Consent', 'Email', 'Phone', 'Address 1', 'Address 2', 'City', 'State', 'Zip', 'Country', 'Comments', 'Company', 'Account Number', 'Discount', 'Discount Type', 'Taxable'],
            ['John', 'Doe', '1', '1', 'not-an-email', '555-1234', '123 Main St', '', 'Springfield', 'IL', '62701', 'US', '', '', '', '', '', '']
        ];

        $tempFile = $this->createCsvFile($csvContent);

        $_FILES['file_path'] = [
            'name' => 'test.csv',
            'type' => 'text/csv',
            'tmp_name' => $tempFile,
            'error' => UPLOAD_ERR_OK,
            'size' => filesize($tempFile)
        ];

        $result = $this->post('/customers/importCsvFile');

        $result->assertOK();
        
        $resultBody = json_decode($result->getJSON(), true);
        $this->assertFalse($resultBody['success'], 'Import should fail for invalid email');
        $this->assertStringContainsString('Row 1', $resultBody['message'], 'Error message should reference failing row');
        $this->assertStringContainsString('Invalid email format', $resultBody['message'], 'Error message should mention email validation');

        $importedCustomer = $this->findCustomerByEmail('not-an-email');
        $this->assertNull($importedCustomer, 'Customer with invalid email should not be imported');

        unlink($tempFile);
    }

    public function testXssPayloadInEmailIsSanitized(): void
    {
        $this->loginAsEmployee();

        $maliciousEmail = '<script>alert("xss")</script>@example.com';

        $csvContent = [
            ['First Name', 'Last Name', 'Gender', 'Consent', 'Email', 'Phone', 'Address 1', 'Address 2', 'City', 'State', 'Zip', 'Country', 'Comments', 'Company', 'Account Number', 'Discount', 'Discount Type', 'Taxable'],
            ['John', 'Doe', '1', '1', $maliciousEmail, '555-1234', '123 Main St', '', 'Springfield', 'IL', '62701', 'US', '', '', '', '', '', '']
        ];

        $tempFile = $this->createCsvFile($csvContent);

        $_FILES['file_path'] = [
            'name' => 'test.csv',
            'type' => 'text/csv',
            'tmp_name' => $tempFile,
            'error' => UPLOAD_ERR_OK,
            'size' => filesize($tempFile)
        ];

        $result = $this->post('/customers/importCsvFile');

        $result->assertOK();

        $importedCustomer = $this->findCustomersByEmailLike('example.com');
        
        $this->assertNotNull($importedCustomer, 'Customer should be imported after sanitization');
        $this->assertStringNotContainsString('<script>', $importedCustomer['email'], 'Script tags should be removed');
        $this->assertStringNotContainsString('</script>', $importedCustomer['email'], 'Script tags should be removed');

        unlink($tempFile);
    }

    public function testMixedValidAndInvalidEmails(): void
    {
        $this->loginAsEmployee();

        $csvContent = [
            ['First Name', 'Last Name', 'Gender', 'Consent', 'Email', 'Phone', 'Address 1', 'Address 2', 'City', 'State', 'Zip', 'Country', 'Comments', 'Company', 'Account Number', 'Discount', 'Discount Type', 'Taxable'],
            ['Valid', 'User', '1', '1', 'valid@example.com', '555-1111', '123 Main St', '', 'City1', 'ST', '12345', 'US', '', '', '', '', '', ''],
            ['Invalid', 'User', '1', '1', 'invalid-email', '555-2222', '456 Oak Ave', '', 'City2', 'ST', '23456', 'US', '', '', '', '', '', ''],
            ['Another', 'Valid', '1', '1', 'another@example.com', '555-3333', '789 Pine Rd', '', 'City3', 'ST', '34567', 'US', '', '', '', '', '', '']
        ];

        $tempFile = $this->createCsvFile($csvContent);

        $_FILES['file_path'] = [
            'name' => 'test.csv',
            'type' => 'text/csv',
            'tmp_name' => $tempFile,
            'error' => UPLOAD_ERR_OK,
            'size' => filesize($tempFile)
        ];

        $result = $this->post('/customers/importCsvFile');

        $result->assertOK();

        $validCustomer1 = $this->findCustomerByEmail('valid@example.com');
        $this->assertNotNull($validCustomer1, 'Valid customer should be imported');

        $validCustomer2 = $this->findCustomerByEmail('another@example.com');
        $this->assertNotNull($validCustomer2, 'Another valid customer should be imported');

        $invalidCustomer = $this->findCustomerByEmail('invalid-email');
        $this->assertNull($invalidCustomer, 'Invalid email customer should not be imported');

        unlink($tempFile);
    }

    public function testEmailWithSpecialCharactersIsSanitized(): void
    {
        $this->loginAsEmployee();

        $emailWithSpecialChars = 'test"user@example.com';
        $csvContent = [
            ['First Name', 'Last Name', 'Gender', 'Consent', 'Email', 'Phone', 'Address 1', 'Address 2', 'City', 'State', 'Zip', 'Country', 'Comments', 'Company', 'Account Number', 'Discount', 'Discount Type', 'Taxable'],
            ['Test', 'User', '1', '1', $emailWithSpecialChars, '555-1234', '123 Main St', '', 'Springfield', 'IL', '62701', 'US', '', '', '', '', '', '']
        ];

        $tempFile = $this->createCsvFile($csvContent);

        $_FILES['file_path'] = [
            'name' => 'test.csv',
            'type' => 'text/csv',
            'tmp_name' => $tempFile,
            'error' => UPLOAD_ERR_OK,
            'size' => filesize($tempFile)
        ];

        $result = $this->post('/customers/importCsvFile');

        $result->assertOK();

        $importedCustomer = $this->findCustomersByEmailLike('example.com');
        
        $this->assertNotNull($importedCustomer, 'Sanitized email should be imported');
        $this->assertStringNotContainsString('"', $importedCustomer['email'], 'Quote characters should be sanitized');

        unlink($tempFile);
    }

    public function testEmptyEmailIsAccepted(): void
    {
        $this->loginAsEmployee();

        // Empty email should be allowed - customers may not have email addresses
        $csvContent = [
            ['First Name', 'Last Name', 'Gender', 'Consent', 'Email', 'Phone', 'Address 1', 'Address 2', 'City', 'State', 'Zip', 'Country', 'Comments', 'Company', 'Account Number', 'Discount', 'Discount Type', 'Taxable'],
            ['Empty', 'Mail', '1', '1', '', '555-1234', '123 Main St', '', 'Springfield', 'IL', '62701', 'US', '', '', '', '', '', '']
        ];

        $tempFile = $this->createCsvFile($csvContent);

        $_FILES['file_path'] = [
            'name' => 'test.csv',
            'type' => 'text/csv',
            'tmp_name' => $tempFile,
            'error' => UPLOAD_ERR_OK,
            'size' => filesize($tempFile)
        ];

        $result = $this->post('/customers/importCsvFile');

        $result->assertOK();

        $resultBody = json_decode($result->getJSON(), true);
        $this->assertTrue($resultBody['success'], 'Import should succeed with empty email');

        // Find customer by name since email is empty
        $importedCustomer = $this->customer->select('customers.*, people.*')
            ->join('people', 'people.person_id = customers.person_id')
            ->where('first_name', 'Empty')
            ->where('last_name', 'Mail')
            ->first();
        
        $this->assertNotNull($importedCustomer, 'Customer with empty email should be imported');
        $this->assertEquals('', $importedCustomer['email'], 'Email should be empty string');

        unlink($tempFile);
    }
}