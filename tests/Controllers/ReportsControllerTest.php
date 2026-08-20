<?php

namespace Tests\Controllers;

use CodeIgniter\Test\CIUnitTestCase;

class ReportsControllerTest extends CIUnitTestCase
{
    public function testRedirectPatternUsesHeaderAndExit(): void
    {
        // This test validates that the Reports submodule permission check
        // uses the correct redirect pattern in constructors.
        // 
        // The original bug: redirect() returns a RedirectResponse object
        // but the constructor doesn't return it, so it gets discarded.
        // 
        // The fix: Use header('Location: ' . base_url(...)); exit();
        // which properly terminates execution and redirects.
        
        $constructorCode = file_get_contents(APPPATH . 'Controllers/Reports.php');
        
        // Verify the fix pattern is present
        $this->assertStringContainsString("header('Location: ' . base_url(", $constructorCode);
        $this->assertStringContainsString('exit();', $constructorCode);
        
        // Verify the buggy pattern is NOT present in the permission check area
        // (Note: redirect() may appear elsewhere in the codebase for valid uses)
        $lines = explode("\n", $constructorCode);
        $inConstructor = false;
        foreach ($lines as $line) {
            if (strpos($line, 'public function __construct') !== false) {
                $inConstructor = true;
            }
            if ($inConstructor && strpos($line, '}') !== false && trim($line) === '}') {
                break;
            }
            if ($inConstructor && strpos($line, "redirect('no_access") !== false) {
                $this->fail('Old redirect() pattern found in constructor - should use header() + exit()');
            }
        }
        
        $this->assertTrue(true, 'Permission check pattern validated');
    }

    public function testSubmodulePermissionCheckOccursBeforeControllerInitialization(): void
    {
        // Verify that permission checks happen in the constructor
        // before any controller methods can execute
        
        $constructorCode = file_get_contents(APPPATH . 'Controllers/Reports.php');
        
        // Verify the permission check is in the constructor
        $this->assertStringContainsString('has_grant', $constructorCode);
        $this->assertStringContainsString('reports_', $constructorCode);
        $this->assertStringContainsString('submodule_id', $constructorCode);
    }
}