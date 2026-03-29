<?php

namespace Tests\Libraries\InvoiceAttachment;

use CodeIgniter\Test\CIUnitTestCase;
use App\Libraries\InvoiceAttachment\InvoiceAttachmentGenerator;
use App\Libraries\InvoiceAttachment\InvoiceAttachment;
use App\Libraries\InvoiceAttachment\PdfAttachment;
use App\Libraries\InvoiceAttachment\UblAttachment;

class InvoiceAttachmentGeneratorTest extends CIUnitTestCase
{
    public function testCreateFromConfigPdfOnly(): void
    {
        $generator = InvoiceAttachmentGenerator::createFromConfig('pdf_only');
        $this->assertInstanceOf(InvoiceAttachmentGenerator::class, $generator);
    }

    public function testCreateFromConfigUblOnly(): void
    {
        $generator = InvoiceAttachmentGenerator::createFromConfig('ubl_only');
        $this->assertInstanceOf(InvoiceAttachmentGenerator::class, $generator);
    }

    public function testCreateFromConfigBoth(): void
    {
        $generator = InvoiceAttachmentGenerator::createFromConfig('both');
        $this->assertInstanceOf(InvoiceAttachmentGenerator::class, $generator);
    }

    public function testCreateFromConfigPdfOnlyRegistersPdfAttachment(): void
    {
        $generator = InvoiceAttachmentGenerator::createFromConfig('pdf_only');
        $attachments = $this->getPrivateProperty($generator, 'attachments');
        
        $this->assertCount(1, $attachments);
        $this->assertInstanceOf(PdfAttachment::class, $attachments[0]);
    }

    public function testCreateFromConfigUblOnlyRegistersUblAttachment(): void
    {
        $generator = InvoiceAttachmentGenerator::createFromConfig('ubl_only');
        $attachments = $this->getPrivateProperty($generator, 'attachments');
        
        $this->assertCount(1, $attachments);
        $this->assertInstanceOf(UblAttachment::class, $attachments[0]);
    }

    public function testCreateFromConfigBothRegistersBothAttachments(): void
    {
        $generator = InvoiceAttachmentGenerator::createFromConfig('both');
        $attachments = $this->getPrivateProperty($generator, 'attachments');
        
        $this->assertCount(2, $attachments);
        $this->assertInstanceOf(PdfAttachment::class, $attachments[0]);
        $this->assertInstanceOf(UblAttachment::class, $attachments[1]);
    }

    public function testRegisterAddsAttachment(): void
    {
        $generator = new InvoiceAttachmentGenerator();
        $mockAttachment = new class implements InvoiceAttachment {
            public function generate(array $saleData, string $type): ?string { return null; }
            public function isApplicableForType(string $type, array $saleData): bool { return true; }
            public function getFileExtension(): string { return 'test'; }
            public function getEnabledConfigValues(): array { return ['test']; }
        };
        
        $result = $generator->register($mockAttachment);
        
        $this->assertSame($generator, $result);
        $attachments = $this->getPrivateProperty($generator, 'attachments');
        $this->assertCount(1, $attachments);
    }

    public function testRegisterIsChainable(): void
    {
        $generator = new InvoiceAttachmentGenerator();
        $mockAttachment = new class implements InvoiceAttachment {
            public function generate(array $saleData, string $type): ?string { return null; }
            public function isApplicableForType(string $type, array $saleData): bool { return true; }
            public function getFileExtension(): string { return 'test'; }
            public function getEnabledConfigValues(): array { return ['test']; }
        };
        
        $result = $generator->register($mockAttachment)->register($mockAttachment);
        
        $attachments = $this->getPrivateProperty($result, 'attachments');
        $this->assertCount(2, $attachments);
    }

    public function testGenerateAttachmentsReturnsEmptyArrayWhenNoAttachmentsRegistered(): void
    {
        $generator = new InvoiceAttachmentGenerator();
        $result = $generator->generateAttachments([], 'invoice');
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testCleanupRemovesFiles(): void
    {
        $tempFile1 = tempnam(sys_get_temp_dir(), 'test_');
        $tempFile2 = tempnam(sys_get_temp_dir(), 'test_');
        
        $this->assertFileExists($tempFile1);
        $this->assertFileExists($tempFile2);
        
        InvoiceAttachmentGenerator::cleanup([$tempFile1, $tempFile2]);
        
        $this->assertFileDoesNotExist($tempFile1);
        $this->assertFileDoesNotExist($tempFile2);
    }

    public function testCleanupHandlesNonExistentFiles(): void
    {
        // Should not throw an exception
        InvoiceAttachmentGenerator::cleanup(['/non/existent/file1', '/non/existent/file2']);
        $this->assertTrue(true);
    }
}