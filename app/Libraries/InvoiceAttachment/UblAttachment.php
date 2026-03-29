<?php

namespace App\Libraries\InvoiceAttachment;

use App\Libraries\UBLGenerator;

class UblAttachment implements InvoiceAttachment
{
    /**
     * @inheritDoc
     */
    public function generate(array $saleData, string $type): ?string
    {
        require_once ROOTPATH . 'vendor/autoload.php';

        try {
            $generator = new UBLGenerator();
            $xml = $generator->generateUblInvoice($saleData);

            $tempPath = tempnam(sys_get_temp_dir(), 'ospos_ubl_');
            if ($tempPath === false) {
                log_message('error', 'UBL attachment: failed to create temp file');
                return null;
            }

            $filename = $tempPath . '.xml';
            rename($tempPath, $filename);

            if (file_put_contents($filename, $xml) === false) {
                log_message('error', 'UBL attachment: failed to write content');
                return null;
            }

            return $filename;
        } catch (\Exception $e) {
            log_message('error', 'UBL attachment generation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function isApplicableForType(string $type, array $saleData): bool
    {
        return in_array($type, ['invoice', 'tax_invoice'], true)
            && !empty($saleData['invoice_number']);
    }

    /**
     * @inheritDoc
     */
    public function getFileExtension(): string
    {
        return 'xml';
    }

    /**
     * @inheritDoc
     */
    public function getEnabledConfigValues(): array
    {
        return ['ubl_only', 'both'];
    }
}