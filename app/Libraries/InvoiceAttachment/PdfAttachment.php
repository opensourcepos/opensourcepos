<?php

namespace App\Libraries\InvoiceAttachment;

use CodeIgniter\Config\Services;

class PdfAttachment implements InvoiceAttachment
{
    /**
     * @inheritDoc
     */
    public function generate(array $saleData, string $type): ?string
    {
        $view = Services::renderer();
        $html = $view->setData($saleData)->render("sales/{$type}_email");

        helper(['dompdf', 'file']);

        $tempPath = tempnam(sys_get_temp_dir(), 'ospos_pdf_');
        if ($tempPath === false) {
            log_message('error', 'PDF attachment: failed to create temp file');
            return null;
        }

        $filename = $tempPath . '.pdf';
        rename($tempPath, $filename);

        $pdfContent = create_pdf($html);
        if (file_put_contents($filename, $pdfContent) === false) {
            log_message('error', 'PDF attachment: failed to write content');
            @unlink($filename);
            return null;
        }

        return $filename;
    }

    /**
     * @inheritDoc
     */
    public function isApplicableForType(string $type, array $saleData): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getFileExtension(): string
    {
        return 'pdf';
    }

    /**
     * @inheritDoc
     */
    public function getEnabledConfigValues(): array
    {
        return ['pdf_only', 'both'];
    }
}