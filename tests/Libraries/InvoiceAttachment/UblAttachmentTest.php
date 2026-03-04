<?php

namespace Tests\Libraries\InvoiceAttachment;

use CodeIgniter\Test\CIUnitTestCase;
use App\Libraries\InvoiceAttachment\UblAttachment;

class UblAttachmentTest extends CIUnitTestCase
{
    private UblAttachment $attachment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->attachment = new UblAttachment();
    }

    public function testGetFileExtensionReturnsXml(): void
    {
        $this->assertEquals('xml', $this->attachment->getFileExtension());
    }

    public function testGetEnabledConfigValuesReturnsCorrectArray(): void
    {
        $values = $this->attachment->getEnabledConfigValues();
        
        $this->assertIsArray($values);
        $this->assertContains('ubl_only', $values);
        $this->assertContains('both', $values);
        $this->assertCount(2, $values);
    }

    public function testIsApplicableForTypeReturnsTrueForInvoiceWithInvoiceNumber(): void
    {
        $saleData = ['invoice_number' => 'INV-001'];
        
        $this->assertTrue($this->attachment->isApplicableForType('invoice', $saleData));
    }

    public function testIsApplicableForTypeReturnsTrueForTaxInvoiceWithInvoiceNumber(): void
    {
        $saleData = ['invoice_number' => 'INV-001'];
        
        $this->assertTrue($this->attachment->isApplicableForType('tax_invoice', $saleData));
    }

    public function testIsApplicableForTypeReturnsFalseForInvoiceWithoutInvoiceNumber(): void
    {
        $saleData = ['invoice_number' => null];
        
        $this->assertFalse($this->attachment->isApplicableForType('invoice', $saleData));
    }

    public function testIsApplicableForTypeReturnsFalseForInvoiceWithEmptyInvoiceNumber(): void
    {
        $saleData = ['invoice_number' => ''];
        
        $this->assertFalse($this->attachment->isApplicableForType('invoice', $saleData));
    }

    public function testIsApplicableForTypeReturnsFalseForInvoiceWithoutInvoiceNumberKey(): void
    {
        $saleData = [];
        
        $this->assertFalse($this->attachment->isApplicableForType('invoice', $saleData));
    }

    public function testIsApplicableForTypeReturnsFalseForQuoteEvenWithInvoiceNumber(): void
    {
        $saleData = ['invoice_number' => 'INV-001'];
        
        $this->assertFalse($this->attachment->isApplicableForType('quote', $saleData));
    }

    public function testIsApplicableForTypeReturnsFalseForWorkOrderEvenWithInvoiceNumber(): void
    {
        $saleData = ['invoice_number' => 'INV-001'];
        
        $this->assertFalse($this->attachment->isApplicableForType('work_order', $saleData));
    }

    public function testIsApplicableForTypeReturnsFalseForReceiptEvenWithInvoiceNumber(): void
    {
        $saleData = ['invoice_number' => 'INV-001'];
        
        $this->assertFalse($this->attachment->isApplicableForType('receipt', $saleData));
    }

    public function testIsApplicableForTypeReturnsFalseForUnknownType(): void
    {
        $saleData = ['invoice_number' => 'INV-001'];
        
        $this->assertFalse($this->attachment->isApplicableForType('unknown_type', $saleData));
    }

    public function testGenerateReturnsNullForMissingConfig(): void
    {
        // Without proper sale_data, generate should fail gracefully
        $result = $this->attachment->generate([], 'invoice');
        
        $this->assertNull($result);
    }
}