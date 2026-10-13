<?php

/**
 * Shared server-side export file generation for manage-table "Export all" downloads.
 *
 * @param array $headers Array of single-key [column_key => label] pairs, e.g. item_headers()
 * @param iterable $rows Array/iterable of objects or arrays keyed by the same column_key values
 * @param string $tableName Underlying DB table name, used for the SQL export's INSERT statements
 */
function generateExportFile(string $format, array $headers, iterable $rows, string $baseFilename, string $tableName): array
{
    $writers = [
        'csv'   => fn () => writeExportCsv($headers, $rows, ','),
        'txt'   => fn () => writeExportCsv($headers, $rows, "\t"),
        'json'  => fn () => writeExportJson($headers, $rows),
        'xml'   => fn () => writeExportXml($headers, $rows),
        'sql'   => fn () => writeExportSql($headers, $rows, $tableName),
        'xlsx'  => fn () => writeExportXlsx($headers, $rows),
        'pdf'   => fn () => writeExportPdf($headers, $rows),
    ];

    if (!isset($writers[$format])) {
        throw new InvalidArgumentException("Unsupported export format: $format");
    }

    $extensions = [
        'csv' => 'csv', 'txt' => 'txt', 'json' => 'json', 'xml' => 'xml',
        'sql' => 'sql', 'xlsx' => 'xlsx', 'pdf' => 'pdf',
    ];

    return [
        'filename' => $baseFilename . '.' . $extensions[$format],
        'body'     => $writers[$format](),
    ];
}

const EXPORT_BLANKABLE_COLUMNS = ['tax_percents'];
const EXPORT_INTEGER_COLUMNS = ['items.item_id'];
const EXPORT_QUANTITY_COLUMNS = ['quantity'];
const EXPORT_CURRENCY_COLUMNS = ['cost_price', 'unit_price'];
const EXPORT_NUMERIC_IF_NUMERIC_COLUMNS = ['item_number'];

function exportRowValues(array $headers, object|array $row): array
{
    $values = [];

    foreach ($headers as $header) {
        $key = array_key_first($header);
        $property = str_contains($key, '.') ? substr($key, strrpos($key, '.') + 1) : $key;
        $value = is_array($row) ? ($row[$property] ?? '') : ($row->$property ?? '');

        if (in_array($key, EXPORT_BLANKABLE_COLUMNS, true) && $value === '-') {
            $value = '';
        }

        $values[$key] = $value;
    }

    return $values;
}

function writeExportCsv(array $headers, iterable $rows, string $delimiter): string
{
    $stream = fopen('php://temp', 'r+');

    fputcsv($stream, array_map(static fn ($header) => reset($header), $headers), $delimiter, '"', '\\');

    foreach ($rows as $row) {
        fputcsv($stream, array_values(exportRowValues($headers, $row)), $delimiter, '"', '\\');
    }

    rewind($stream);
    $body = stream_get_contents($stream);
    fclose($stream);

    return "\xEF\xBB\xBF" . $body;
}

function writeExportJson(array $headers, iterable $rows): string
{
    $data = [];

    foreach ($rows as $row) {
        $data[] = exportRowValues($headers, $row);
    }

    return json_encode($data, JSON_PRETTY_PRINT);
}

function writeExportXml(array $headers, iterable $rows): string
{
    $writer = new XMLWriter();
    $writer->openMemory();
    $writer->setIndent(true);
    $writer->startDocument('1.0', 'UTF-8');
    $writer->startElement('rows');

    foreach ($rows as $row) {
        $writer->startElement('row');
        foreach (exportRowValues($headers, $row) as $key => $value) {
            $writer->writeElement(sanitizeExportXmlTag($key), (string) $value);
        }
        $writer->endElement();
    }

    $writer->endElement();
    $writer->endDocument();

    return $writer->outputMemory();
}

function sanitizeExportXmlTag(string $key): string
{
    return preg_replace('/[^a-zA-Z0-9_]/', '_', $key) ?: 'column';
}

function writeExportSql(array $headers, iterable $rows, string $tableName): string
{
    $db = db_connect();
    $columns = array_map(static fn ($header) => array_key_first($header), $headers);
    $quotedColumns = implode(', ', array_map(static fn ($col) => '`' . $col . '`', $columns));

    $lines = [];

    foreach ($rows as $row) {
        $values = array_values(exportRowValues($headers, $row));
        $quotedValues = implode(', ', array_map(static fn ($value) => $db->escape($value), $values));
        $lines[] = "INSERT INTO `$tableName` ($quotedColumns) VALUES ($quotedValues);";
    }

    return implode("\n", $lines);
}

/**
 * Deletes export cache files left behind by a prior export that crashed or timed
 * out before reaching its own cleanup (e.g. memory/timeout kills the request before
 * the `finally` in writeExportXlsx() runs). Only sweeps files older than 10
 * minutes - far longer than any real export should take - so a concurrent,
 * still-in-progress export (different tab/user) is never touched.
 */
function sweepStaleExportCache(): void
{
    $directory = WRITEPATH . 'cache/exports/';

    if (!is_dir($directory)) {
        return;
    }

    $staleBefore = time() - 600;

    foreach (glob($directory . 'xlsx_*') as $file) {
        if (is_file($file) && filemtime($file) < $staleBefore) {
            unlink($file);
        }
    }
}

/**
 * Column index (0-based, matching exportRowValues()'s order) => Excel NumberFormat
 * code to apply to every cell in that column, or null if the column should stay text.
 */
function exportXlsxColumnFormats(array $headers): array
{
    $quantityFormat = '0' . (quantity_decimals() > 0 ? '.' . str_repeat('0', quantity_decimals()) : '');
    $currencyFormat = '0' . (totals_decimals() > 0 ? '.' . str_repeat('0', totals_decimals()) : '');

    $formats = [];

    foreach (array_values($headers) as $index => $header) {
        $key = array_key_first($header);

        if (in_array($key, EXPORT_INTEGER_COLUMNS, true) || in_array($key, EXPORT_NUMERIC_IF_NUMERIC_COLUMNS, true)) {
            $formats[$index] = '0';
        } elseif (in_array($key, EXPORT_QUANTITY_COLUMNS, true)) {
            $formats[$index] = $quantityFormat;
        } elseif (in_array($key, EXPORT_CURRENCY_COLUMNS, true)) {
            $formats[$index] = $currencyFormat;
        } elseif (($header['type'] ?? null) === DECIMAL) {
            $formats[$index] = '0.00';
        } else {
            $formats[$index] = null;
        }
    }

    return $formats;
}

function writeExportXlsx(array $headers, iterable $rows): string
{
    sweepStaleExportCache();

    $tempPath = WRITEPATH . 'cache/exports/xlsx_' . bin2hex(random_bytes(8)) . '.xlsx';

    $columns = array_map(static fn ($header) => array_key_first($header), $headers);
    $columnFormats = exportXlsxColumnFormats($headers);
    $numericIfNumericIndexes = array_keys(array_intersect($columns, EXPORT_NUMERIC_IF_NUMERIC_COLUMNS));

    $columnStyles = [];
    foreach ($columnFormats as $index => $format) {
        if ($format !== null) {
            $columnStyles[$index] = new OpenSpout\Common\Entity\Style\Style(format: $format);
        }
    }

    $writer = new OpenSpout\Writer\XLSX\Writer();

    try {
        $writer->openToFile($tempPath);

        $writer->addRow(OpenSpout\Common\Entity\Row::fromValues(array_map(static fn ($header) => (string) reset($header), $headers)));

        foreach ($rows as $row) {
            $values = array_values(exportRowValues($headers, $row));

            $cellValues = [];
            foreach ($values as $index => $value) {
                $format = $columnFormats[$index] ?? null;

                if ($format !== null && in_array($index, $numericIfNumericIndexes, true) && !is_numeric($value)) {
                    $format = null;
                }

                $cellValues[$index] = ($format !== null && $value !== '' && is_numeric($value)) ? (float) $value : (string) $value;
            }

            $writer->addRow(OpenSpout\Common\Entity\Row::fromValuesWithStyles($cellValues, $columnStyles));
        }

        $writer->close();

        return file_get_contents($tempPath);
    } finally {
        if (is_file($tempPath)) {
            unlink($tempPath);
        }
    }
}

const EXPORT_PDF_PAGE_MARGIN = 10.0;
const EXPORT_PDF_FONT_SIZE = 9;
const EXPORT_PDF_ROW_HEIGHT = 6.0;

const EXPORT_PDF_AVG_CHAR_WIDTH_MM = 0.42 * EXPORT_PDF_FONT_SIZE * 25.4 / 72;

function exportPdfColumnWidths(array $headers, float $usableWidth): array
{
    $labelLengths = array_map(static fn ($header) => max(mb_strlen((string) reset($header)), 6), $headers);
    $totalLength = array_sum($labelLengths);

    return array_map(static fn ($length) => $usableWidth * $length / $totalLength, $labelLengths);
}

function exportPdfTruncateCellText(string $text, float $columnWidth): string
{
    $maxChars = max(1, (int) floor($columnWidth / EXPORT_PDF_AVG_CHAR_WIDTH_MM));

    if (mb_strlen($text) <= $maxChars) {
        return $text;
    }

    return mb_substr($text, 0, max(1, $maxChars - 1)) . '…';
}

function writeExportPdf(array $headers, iterable $rows): string
{
    $buildStart = microtime(true);

    set_time_limit(300);
    define('K_PATH_FONTS', realpath(APPPATH . '../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

    $pdf = new Com\Tecnick\Pdf\Tcpdf(unit: 'mm', subsetfont: true);
    $font = $pdf->font->insert($pdf->pon, 'dejavusans', '', EXPORT_PDF_FONT_SIZE);

    $headerValues = array_values(array_map(static fn ($header) => (string) reset($header), $headers));

    $startNewPage = function () use ($pdf, $font): array {
        $page = $pdf->addPage(['format' => 'A4', 'orientation' => 'L']);
        $pdf->page->addContent($font['out']);

        return $page;
    };

    $page = $startNewPage();
    $pageHeight = $page['height'];
    $usableWidth = $page['width'] - (2 * EXPORT_PDF_PAGE_MARGIN);
    $columnWidths = array_values(exportPdfColumnWidths($headers, $usableWidth));

    $drawRow = function (array $values, float $posY) use ($pdf, $columnWidths): void {
        $posX = EXPORT_PDF_PAGE_MARGIN;
        foreach ($values as $index => $value) {
            $text = exportPdfTruncateCellText((string) $value, $columnWidths[$index]);

            $out = $pdf->getTextCell(
                txt: $text,
                posx: $posX,
                posy: $posY,
                width: $columnWidths[$index],
                height: EXPORT_PDF_ROW_HEIGHT,
                halign: 'L',
                valign: 'T',
            );
            $pdf->page->addContent($out);
            $posX += $columnWidths[$index];
        }
    };

    $drawHeaderRow = function (float $posY) use ($pdf, $font, $headerValues, $drawRow): void {
        $boldFont = $pdf->font->cloneFont($pdf->pon, $font['idx'], 'B');
        $pdf->page->addContent($boldFont['out']);
        $drawRow($headerValues, $posY);

        $regularFont = $pdf->font->cloneFont($pdf->pon, $boldFont['idx'], '');
        $pdf->page->addContent($regularFont['out']);
    };

    $posY = EXPORT_PDF_PAGE_MARGIN;
    $drawHeaderRow($posY);
    $posY += EXPORT_PDF_ROW_HEIGHT;

    $rowCount = 0;
    foreach ($rows as $row) {
        if ($posY + EXPORT_PDF_ROW_HEIGHT > $pageHeight - EXPORT_PDF_PAGE_MARGIN) {
            $startNewPage();
            $posY = EXPORT_PDF_PAGE_MARGIN;
            $drawHeaderRow($posY);
            $posY += EXPORT_PDF_ROW_HEIGHT;
        }

        $drawRow(array_values(exportRowValues($headers, $row)), $posY);
        $posY += EXPORT_PDF_ROW_HEIGHT;
        $rowCount++;
    }

    $buildMs = (int) round((microtime(true) - $buildStart) * 1000);

    log_message('debug', "Export pdf write: build={$buildMs}ms rows=$rowCount");

    return $pdf->getOutPDFString();
}
