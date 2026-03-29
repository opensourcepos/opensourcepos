<?php

namespace App\Libraries\InvoiceAttachment;

interface InvoiceAttachment
{
    /**
     * Generate the attachment content and write to a temp file.
     *
     * @param array $saleData The sale data from _load_sale_data()
     * @param string $type The document type (invoice, tax_invoice, quote, work_order, receipt)
     * @return string|null Absolute path to generated file, or null on failure
     */
    public function generate(array $saleData, string $type): ?string;

    /**
     * Check if this attachment type is applicable for the document type.
     * E.g., UBL only works for invoice/tax_invoice
     *
     * @param string $type The document type
     * @param array $saleData The sale data (to check invoice_number existence)
     * @return bool
     */
    public function isApplicableForType(string $type, array $saleData): bool;

    /**
     * Get the file extension for this attachment.
     *
     * @return string E.g., 'pdf', 'xml'
     */
    public function getFileExtension(): string;

    /**
     * Get the config values that enable this attachment.
     * Returns array of config values that should generate this attachment.
     *
     * @return array E.g., ['pdf_only', 'both'] for PDF
     */
    public function getEnabledConfigValues(): array;
}