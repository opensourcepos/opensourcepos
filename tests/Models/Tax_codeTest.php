<?php

namespace Tests\Models;

use App\Models\Tax_code;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;
use Tests\Support\DatabaseBootstrapTrait;

class Tax_codeTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use DatabaseBootstrapTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $seedOnce    = true;
    protected $refresh     = false;
    protected $namespace   = null;

    protected function setUp(): void
    {
        $this->resetDatabaseOnce();

        parent::setUp();
    }

    public function testSaveValue_UpdatesExistingRowWhenTaxCodeChanges(): void
    {
        $taxCodeModel = model(Tax_code::class);
        $initialCode  = 'TC-' . uniqid();
        $updatedCode  = 'TC-UPD-' . uniqid();

        $taxCodeData = [
            'tax_code'      => $initialCode,
            'tax_code_name' => 'Initial Tax Code',
            'city'          => 'Austin',
            'state'         => 'TX',
            'deleted'       => 0,
        ];

        $this->assertTrue($taxCodeModel->save_value($taxCodeData, NEW_ENTRY));
        $taxCodeId = (int) $taxCodeData['tax_code_id'];
        $this->assertGreaterThan(0, $taxCodeId);

        $updateData = [
            'tax_code'      => $updatedCode,
            'tax_code_name' => 'Updated Tax Code',
            'city'          => 'Dallas',
            'state'         => 'TX',
            'deleted'       => 0,
        ];

        $this->assertTrue($taxCodeModel->save_value($updateData, $taxCodeId));

        $savedRow = $taxCodeModel->get_info($taxCodeId);
        $this->assertSame($updatedCode, $savedRow->tax_code);
        $this->assertSame('Updated Tax Code', $savedRow->tax_code_name);
        $this->assertSame('Dallas', $savedRow->city);
        $this->assertFalse($taxCodeModel->exists($initialCode));

        $count = Database::connect()->table('tax_codes')
            ->whereIn('tax_code', [$initialCode, $updatedCode])
            ->countAllResults();
        $this->assertSame(1, $count);
    }

    public function testSaveValue_InsertsWhenUnknownIdProvided(): void
    {
        $taxCodeModel = model(Tax_code::class);
        $code         = 'TC-NEW-' . uniqid();

        $taxCodeData = [
            'tax_code'      => $code,
            'tax_code_name' => 'Inserted For Unknown ID',
            'city'          => 'Houston',
            'state'         => 'TX',
            'deleted'       => 0,
        ];

        $this->assertTrue($taxCodeModel->save_value($taxCodeData, 999999));
        $this->assertGreaterThan(0, (int) $taxCodeData['tax_code_id']);
        $this->assertNotSame(999999, (int) $taxCodeData['tax_code_id']);
        $this->assertTrue($taxCodeModel->exists($code));
    }
}
