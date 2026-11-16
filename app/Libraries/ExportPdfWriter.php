<?php

namespace App\Libraries;

class ExportPdfWriter extends \tFPDF
{
    /** @var float[] */
    public array $exportColumnWidths = [];

    /** @var string[] */
    public array $exportColumnAlignments = [];

    /** @var string[] */
    public array $exportHeaderValues = [];

    public function Header(): void
    {
        $this->SetFont('DejaVuSans', 'B', EXPORT_PDF_FONT_SIZE);

        foreach ($this->exportHeaderValues as $index => $value) {
            $width = $this->exportColumnWidths[$index];
            $text = exportPdfTruncateCellText($this, $value, $width);
            $this->Cell($width, EXPORT_PDF_ROW_HEIGHT, $text, 1, 0, 'C');
        }
        $this->Ln();

        $this->SetFont('DejaVuSans', '', EXPORT_PDF_FONT_SIZE);
    }
}
