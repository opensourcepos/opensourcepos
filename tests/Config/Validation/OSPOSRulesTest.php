<?php

namespace Tests\Config\Validation;

use App\Config\Validation\OSPOSRules;
use App\Models\Employee;
use CodeIgniter\Config\Factories;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\Test\CIUnitTestCase;
use Config\OSPOS;
use Config\Services;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Regression tests for the login validation flow in OSPOSRules::login_check().
 *
 * Guards the fix from commit 5dea748b0: gcaptcha must be validated before
 * Employee::login() is attempted, and the error contract must be preserved.
 */
class OSPOSRulesTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        Factories::reset();
        Services::reset();

        parent::tearDown();
    }

    public function testFailedCaptchaReturnsWithoutCallingLogin(): void
    {
        $this->injectSettings(['gcaptcha_enable' => '1', 'gcaptcha_secret_key' => 'test-key']);
        $this->injectRequest();
        $employee = $this->injectEmployeeMock();
        $employee->expects($this->never())->method('login');

        $rules = $this->rulesWithCaptchaResult(false);
        $error = null;

        $result = $rules->login_check('admin', 'username,password', ['password' => 'secret'], $error);

        $this->assertFalse($result);
        $this->assertSame(lang('Login.invalid_gcaptcha'), $error);
    }

    public function testSuccessfulCaptchaWithInvalidCredentialsReturnsLoginError(): void
    {
        $this->injectSettings(['gcaptcha_enable' => '1', 'gcaptcha_secret_key' => 'test-key']);
        $this->injectRequest();
        $employee = $this->injectEmployeeMock();
        $employee->expects($this->once())
            ->method('login')
            ->with('admin', 'wrong-password')
            ->willReturn(false);

        $rules = $this->rulesWithCaptchaResult(true);
        $error = null;

        $result = $rules->login_check('admin', 'username,password', ['password' => 'wrong-password'], $error);

        $this->assertFalse($result);
        $this->assertSame(lang('Login.invalid_username_and_password'), $error);
    }

    public function testCaptchaDisabledWithInvalidCredentialsReturnsLoginError(): void
    {
        $this->injectSettings([]);
        $this->injectRequest();
        $employee = $this->injectEmployeeMock();
        $employee->expects($this->once())
            ->method('login')
            ->with('admin', 'wrong-password')
            ->willReturn(false);

        $rules = new OSPOSRules();
        $error = null;

        $result = $rules->login_check('admin', 'username,password', ['password' => 'wrong-password'], $error);

        $this->assertFalse($result);
        $this->assertSame(lang('Login.invalid_username_and_password'), $error);
    }

    public function testSuccessfulCaptchaWithValidCredentialsPasses(): void
    {
        $this->injectSettings(['gcaptcha_enable' => '1', 'gcaptcha_secret_key' => 'test-key']);
        $this->injectRequest();
        $employee = $this->injectEmployeeMock();
        $employee->expects($this->once())
            ->method('login')
            ->with('admin', 'correct-password')
            ->willReturn(true);

        $rules = $this->rulesWithCaptchaResult(true);
        $error = null;

        $result = $rules->login_check('admin', 'username,password', ['password' => 'correct-password'], $error);

        $this->assertTrue($result);
        $this->assertNull($error);
    }

    private function injectSettings(array $settings): void
    {
        $ospos = new OSPOS();
        $ospos->settings = $settings;
        Factories::injectMock('config', OSPOS::class, $ospos);
    }

    private function injectRequest(): void
    {
        $request = $this->getMockBuilder(IncomingRequest::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPost', 'getIPAddress', 'getLocale'])
            ->getMock();
        $request->method('getPost')->willReturn('dummy-captcha-response');
        $request->method('getLocale')->willReturn('en');
        $request->method('getIPAddress')->willReturn('127.0.0.1');
        Services::injectMock('request', $request);
    }

    private function injectEmployeeMock(): MockObject
    {
        $employee = $this->getMockBuilder(Employee::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['login'])
            ->getMock();
        Factories::injectMock('models', Employee::class, $employee);

        return $employee;
    }

    /**
     * Builds an OSPOSRules instance whose gcaptcha_check() returns a fixed
     * result, so the captcha outcome can be simulated without network access.
     */
    private function rulesWithCaptchaResult(bool $captcha_result): OSPOSRules
    {
        return new class ($captcha_result) extends OSPOSRules {
            private bool $captcha_result;

            public function __construct(bool $captcha_result)
            {
                $this->captcha_result = $captcha_result;
            }

            protected function gcaptcha_check($response): bool
            {
                return $this->captcha_result;
            }
        };
    }
}
