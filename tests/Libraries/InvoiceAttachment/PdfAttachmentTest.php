<?php

namespace Tests\Libraries\InvoiceAttachment;

use CodeIgniter\Test\CIUnitTestCase;
use App\Libraries\InvoiceAttachment\PdfAttachment;

class PdfAttachmentTest extends CIUnitTestCase
{
    private PdfAttachment $attachment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->attachment = new PdfAttachment();
    }

    public function testGetFileExtensionReturnsPdf(): void
    {
        $this->assertEquals('pdf', $this->attachment->getFileExtension());
    }

    public function testGetEnabledConfigValuesReturnsCorrectArray(): void
    {
        $values = $this->attachment->getEnabledConfigValues();
        
        $this->assertIsArray($values);
        $this->assertContains('pdf_only', $values);
        $this->assertContains('both', $values);
        $this->assertCount(2, $values);
    }

    public function testIsApplicableForTypeReturnsTrueForInvoice(): void
    {
        $this->assertTrue($this->attachment->isApplicableForType('invoice', []));
    }

    public function testIsApplicableForTypeReturnsTrueForTaxInvoice(): void
    {
        $this->assertTrue($this->attachment->isApplicableForType('tax_invoice', []));
    }

    public function testIsApplicableForTypeReturnsTrueForQuote(): void
    {
        $this->assertTrue($this->attachment->isApplicableForType('quote', []));
    }

    public function testIsApplicableForTypeReturnsTrueForWorkOrder(): void
    {
        $this->assertTrue($this->attachment->isApplicableForType('work_order', []));
    }

    public function testIsApplicableForTypeReturnsTrueForReceipt(): void
    {
        $this->assertTrue($this->attachment->isApplicableForType('receipt', []));
    }

    public function testIsApplicableForTypeReturnsTrueForAnyType(): void
    {
        // PDF should work for any document type
        $this->assertTrue($this->attachment->isApplicableForType('random_type', []));
    }

    public function testIsApplicableForTypeIgnoresSaleData(): void
    {
        // PDF attachment doesn't depend on invoice_number
        $this->assertTrue($this->attachment->isApplicableForType('invoice', ['invoice_number' => null]));
        $this->assertTrue($this->attachment->isApplicableForType('invoice', ['invoice_number' => 'INV-001']));
    }
}