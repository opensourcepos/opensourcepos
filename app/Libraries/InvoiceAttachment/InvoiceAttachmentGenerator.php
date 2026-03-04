<?php

namespace App\Libraries\InvoiceAttachment;

class InvoiceAttachmentGenerator
{
    /** @var InvoiceAttachment[] */
    private array $attachments = [];

    /**
     * Register an attachment generator.
     */
    public function register(InvoiceAttachment $attachment): self
    {
        $this->attachments[] = $attachment;
        return $this;
    }

    /**
     * Create generator with attachments based on config.
     * Factory method that instantiates the right attachments.
     *
     * @param string $invoiceFormat Config value: 'pdf_only', 'ubl_only', or 'both'
     * @return self
     */
    public static function createFromConfig(string $invoiceFormat): self
    {
        $generator = new self();

        if (in_array($invoiceFormat, ['pdf_only', 'both'], true)) {
            $generator->register(new PdfAttachment());
        }

        if (in_array($invoiceFormat, ['ubl_only', 'both'], true)) {
            $generator->register(new UblAttachment());
        }

        return $generator;
    }

    /**
     * Generate all applicable attachments for a sale.
     *
     * @param array $saleData The sale data
     * @param string $type The document type
     * @return string[] Array of file paths to generated attachments
     */
    public function generateAttachments(array $saleData, string $type): array
    {
        $files = [];

        foreach ($this->attachments as $attachment) {
            if ($attachment->isApplicableForType($type, $saleData)) {
                $filepath = $attachment->generate($saleData, $type);
                if ($filepath !== null) {
                    $files[] = $filepath;
                }
            }
        }

        return $files;
    }

    /**
     * Clean up temporary attachment files.
     *
     * @param string[] $files
     */
    public static function cleanup(array $files): void
    {
        foreach ($files as $file) {
            @unlink($file);
        }
    }
}