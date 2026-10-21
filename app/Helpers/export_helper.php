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

const EXPORT_CSV_FORMULA_PREFIXES = ['=', '+', '-', '@'];

function sanitizeExportCsvValue(mixed $value): mixed
{
    if (!is_string($value) || $value === '' || is_numeric($value)) {
        return $value;
    }

    foreach (EXPORT_CSV_FORMULA_PREFIXES as $prefix) {
        if (str_starts_with($value, $prefix)) {
            return "'" . $value;
        }
    }

    return $value;
}

function writeExportCsv(array $headers, iterable $rows, string $delimiter): string
{
    $stream = fopen('php://temp', 'r+');

    fputcsv($stream, array_map(static fn ($header) => sanitizeExportCsvValue(reset($header)), $headers), $delimiter, '"', '\\');

    foreach ($rows as $row) {
        $values = array_map('sanitizeExportCsvValue', array_values(exportRowValues($headers, $row)));
        fputcsv($stream, $values, $delimiter, '"', '\\');
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
    $tag = preg_replace('/[^a-zA-Z0-9_]/', '_', $key) ?: 'column';

    return preg_match('/^[0-9]/', $tag) ? 'col_' . $tag : $tag; //Tags cannot start with a number. Prefixed.
}

/**
 * Export column keys that are real, directly-insertable columns on the given DB table,
 * keyed by unprefixed table name. Keys not listed here are joined/decoded labels or
 * computed values (e.g. a supplier's company_name on an item export, or items.quantity
 * which is a computed inventory sum) that don't correspond to a column on that table,
 * so writeExportSql() must drop them rather than emit a bogus INSERT.
 */
const EXPORT_SQL_RESTORABLE_COLUMNS = [
    'people'                => ['people.person_id', 'last_name', 'first_name', 'email', 'phone_number'],
    'customers'             => ['people.person_id'],
    'suppliers'             => ['people.person_id', 'company_name', 'agency_name', 'category'],
    'items'                 => ['items.item_id', 'item_number', 'name', 'category', 'cost_price', 'unit_price'],
    'expense_categories'    => ['expense_category_id', 'category_name', 'category_description'],
    'expenses'              => ['expense_id', 'date', 'amount', 'tax_amount', 'payment_type', 'description'],
    'cash_up'               => [
        'cashup_id', 'open_date', 'open_amount_cash', 'transfer_amount_cash', 'close_date',
        'closed_amount_cash', 'note', 'closed_amount_due', 'closed_amount_card',
        'closed_amount_check', 'closed_amount_total',
    ],
    'giftcards'             => ['giftcard_id', 'giftcard_number', 'value'],
    'attribute_definitions' => ['definition_id', 'definition_name', 'definition_type'],
    'item_kits'             => ['item_kit_id', 'item_kit_number', 'name', 'description'],
    'sales'                 => ['sale_id', 'sale_time', 'invoice_number'],
    'tax_rates'             => ['tax_rate'],
];

/**
 * Columns that are NOT NULL with no default on the real table, so writeExportSql() must
 * always include them (if present in the row) even though they aren't part of the visible
 * $headers column set - without a value, the generated INSERT fails under strict SQL mode.
 * Keyed by unprefixed table name => [db column => row array key].
 */
const EXPORT_SQL_REQUIRED_HIDDEN_COLUMNS = [
    'people'                => [
        'address_1' => 'address_1', 'address_2' => 'address_2', 'city' => 'city',
        'state' => 'state', 'zip' => 'zip', 'country' => 'country', 'comments' => 'comments',
    ],
    'customers'             => ['employee_id' => 'employee_id'],
    'items'                 => [
        'description' => 'description', 'allow_alt_description' => 'allow_alt_description',
        'is_serialized' => 'is_serialized',
    ],
    'expenses'              => ['employee_id' => 'employee_id', 'expense_category_id' => 'expense_category_id'],
    'cash_up'               => [
        'description' => 'description', 'open_employee_id' => 'open_employee_id_raw',
        'close_employee_id' => 'close_employee_id_raw',
    ],
    'attribute_definitions' => ['definition_flags' => 'definition_flags_raw'],
    'tax_rates'             => [
        'rate_tax_code_id' => 'rate_tax_code_id', 'rate_tax_category_id' => 'rate_tax_category_id',
        'rate_jurisdiction_id' => 'rate_jurisdiction_id',
    ],
];

function exportSqlUnprefixedTableName(string $tableName): string
{
    $db = db_connect();

    return str_starts_with($tableName, $db->DBPrefix) ? substr($tableName, strlen($db->DBPrefix)) : $tableName;
}

function writeExportSql(array $headers, iterable $rows, string $tableName): string
{
    $db = db_connect();
    $unprefixed = exportSqlUnprefixedTableName($tableName);
    $restorableColumns = EXPORT_SQL_RESTORABLE_COLUMNS[$unprefixed] ?? null;

    if ($restorableColumns === null) {
        return "-- This dataset has no directly-restorable columns on `$tableName` (joined/computed values only); SQL export is not available for it.\n";
    }

    $headerKeys = array_intersect(array_map(static fn ($header) => array_key_first($header), $headers), $restorableColumns);

    if ($headerKeys === []) {
        return "-- None of this export's columns map to real `$tableName` columns; SQL export is not available for it.\n";
    }

    $requiredHidden = EXPORT_SQL_REQUIRED_HIDDEN_COLUMNS[$unprefixed] ?? [];

    $columns = array_merge(
        array_map(static fn ($key) => str_contains($key, '.') ? substr($key, strrpos($key, '.') + 1) : $key, $headerKeys),
        array_keys($requiredHidden)
    );
    $quotedColumns = implode(', ', array_map(static fn ($col) => '`' . $col . '`', $columns));

    $lines = [];

    foreach ($rows as $row) {
        $values = array_values(array_intersect_key(exportRowValues($headers, $row), array_flip($headerKeys)));

        foreach ($requiredHidden as $rowKey) {
            $values[] = is_array($row) ? ($row[$rowKey] ?? null) : ($row->$rowKey ?? null);
        }

        $quotedValues = implode(', ', array_map(static fn ($value) => $db->escape($value), $values));
        $lines[] = "INSERT INTO `$tableName` ($quotedColumns) VALUES ($quotedValues);";
    }

    if ($lines === []) {
        return "-- No rows matched this export.\n";
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

    $directory = WRITEPATH . 'cache/exports/';

    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException("Unable to create export cache directory: $directory");
    }

    $tempPath = $directory . 'xlsx_' . bin2hex(random_bytes(8)) . '.xlsx';

    $columns = array_map(static fn ($header) => array_key_first($header), $headers);
    $columnFormats = exportXlsxColumnFormats($headers);
    $numericIfNumericIndexes = array_keys(array_intersect($columns, EXPORT_NUMERIC_IF_NUMERIC_COLUMNS));

    $columnStyles = [];
    foreach ($columnFormats as $index => $format) {
        if ($format !== null) {
            $columnStyles[$index] = (new OpenSpout\Common\Entity\Style\Style())->setFormat($format);
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
                $isNumericIfNumeric = in_array($index, $numericIfNumericIndexes, true);

                if ($isNumericIfNumeric) {
                    $format = null;
                }

                $cellValues[$index] = ($format !== null && $value !== '' && is_numeric($value)) ? (float) $value : (string) $value;
            }

            $writer->addRow(OpenSpout\Common\Entity\Row::fromValuesWithStyles($cellValues, null, $columnStyles));
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
const EXPORT_PDF_CELL_PADDING_MM = 2.0;

// Conservative (wider than any real glyph) per-character width, used only to cheaply rule out
// truncation for short strings before paying for a real GetStringWidth() measurement.
const EXPORT_PDF_MAX_CHAR_WIDTH_MM = 0.6 * EXPORT_PDF_FONT_SIZE * 25.4 / 72;

// Columns whose content is naturally short/uniform (price, quantity, dates, short codes,
// checkbox attributes) - sized to fit their label plus a little headroom, rather than sharing
// width proportionally with text columns.
const EXPORT_PDF_LABEL_WIDTH_COLUMNS = ['cost_price', 'unit_price', 'quantity', 'tax_percents', 'category'];

// Attribute types whose values are inherently short (Yes/No, a short code) - same label-width
// treatment as EXPORT_PDF_LABEL_WIDTH_COLUMNS, keyed by type rather than column key since
// attribute columns are keyed by definition_id, not a fixed name.
const EXPORT_PDF_LABEL_WIDTH_ATTRIBUTE_TYPES = [CHECKBOX, DATE];

// Columns that should absorb whatever width is left over after every other column gets its
// minimum - the long free-text columns (item name, text/dropdown attribute values) that
// actually need the room.
const EXPORT_PDF_EXPAND_COLUMNS = ['name'];
const EXPORT_PDF_EXPAND_ATTRIBUTE_TYPES = [TEXT, DROPDOWN];

const EXPORT_PDF_BARCODE_DIGITS = 15;
const EXPORT_PDF_ID_DIGITS = 5;

/**
 * PDF column headers have no room for the attribute_ prefix CSV/JSON/XML/SQL/XLSX use to
 * disambiguate attribute columns from same-named item columns - PDF widths are already tight,
 * so just show the bare attribute name there.
 */
function exportPdfDisplayLabel(string $key, string $label): string
{
    return str_starts_with($key, 'attribute_') ? substr($label, strlen('attribute_')) : $label;
}

function exportPdfColumnWidths(tFPDF $pdf, array $headers, float $usableWidth): array
{
    $widths = [];
    $expandIndexes = [];
    $reserved = 0.0;

    foreach (array_values($headers) as $index => $header) {
        $key = array_key_first($header);
        $label = exportPdfDisplayLabel($key, (string) reset($header));
        $type = $header['type'] ?? null;

        $isExpand = in_array($key, EXPORT_PDF_EXPAND_COLUMNS, true)
            || ($type !== null && in_array($type, EXPORT_PDF_EXPAND_ATTRIBUTE_TYPES, true));

        if ($isExpand) {
            $expandIndexes[] = $index;

            continue;
        }

        $isLabelWidth = in_array($key, EXPORT_PDF_LABEL_WIDTH_COLUMNS, true)
            || ($type !== null && in_array($type, EXPORT_PDF_LABEL_WIDTH_ATTRIBUTE_TYPES, true));

        if ($key === 'items.item_id') {
            $width = $pdf->GetStringWidth(str_repeat('0', EXPORT_PDF_ID_DIGITS)) + (2 * EXPORT_PDF_CELL_PADDING_MM);
        } elseif ($key === 'item_number') {
            $width = $pdf->GetStringWidth(str_repeat('0', EXPORT_PDF_BARCODE_DIGITS)) + (2 * EXPORT_PDF_CELL_PADDING_MM);
        } elseif ($isLabelWidth) {
            $width = $pdf->GetStringWidth($label) + (2 * EXPORT_PDF_CELL_PADDING_MM);
        } else {
            $width = $pdf->GetStringWidth($label) * 1.5 + (2 * EXPORT_PDF_CELL_PADDING_MM);
        }

        $widths[$index] = $width;
        $reserved += $width;
    }

    if ($expandIndexes !== []) {
        $expandWidth = max(EXPORT_PDF_CELL_PADDING_MM * 4, ($usableWidth - $reserved) / count($expandIndexes));

        foreach ($expandIndexes as $index) {
            $widths[$index] = $expandWidth;
        }
    }

    ksort($widths);

    return $widths;
}

/**
 * Truncates $text with an ellipsis so it fits $columnWidth, measured with the PDF's
 * actual loaded font metrics (tFPDF::GetStringWidth()) rather than an average-character
 * estimate - DejaVu Sans is proportional, not monospace, so a fixed-width guess
 * under/over-truncates depending on which characters appear, causing visible overflow.
 */
function exportPdfTruncateCellText(tFPDF $pdf, string $text, float $columnWidth): string
{
    $maxWidth = $columnWidth - EXPORT_PDF_CELL_PADDING_MM;

    // Fast path: even at the widest possible glyph width, this text can't overflow -
    // skip the real (and comparatively expensive) GetStringWidth() measurement entirely.
    if (mb_strlen($text) * EXPORT_PDF_MAX_CHAR_WIDTH_MM <= $maxWidth) {
        return $text;
    }

    if ($pdf->GetStringWidth($text) <= $maxWidth) {
        return $text;
    }

    $low = 0;
    $high = mb_strlen($text);

    while ($low < $high) {
        $mid = (int) ceil(($low + $high) / 2);
        $candidate = mb_substr($text, 0, $mid) . '…';

        if ($pdf->GetStringWidth($candidate) <= $maxWidth) {
            $low = $mid;
        } else {
            $high = $mid - 1;
        }
    }

    return $low > 0 ? mb_substr($text, 0, $low) . '…' : '…';
}

/**
 * PDF cells are plain display text, unlike Excel's real numeric cells - so unlike the
 * raw values exportRowValues() returns for every other format, quantity/price columns
 * need the same locale-formatted display string the live table shows.
 */
function exportPdfFormatRowValues(array $values): array
{
    foreach (EXPORT_QUANTITY_COLUMNS as $key) {
        if (isset($values[$key]) && $values[$key] !== '') {
            $values[$key] = to_quantity_decimals((string) $values[$key]);
        }
    }

    foreach (EXPORT_CURRENCY_COLUMNS as $key) {
        if (isset($values[$key]) && $values[$key] !== '') {
            $values[$key] = to_currency((string) $values[$key]);
        }
    }

    return $values;
}

class ExportPdfWriter extends tFPDF
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

// Columns rendered centered rather than left-aligned.
const EXPORT_PDF_CENTERED_COLUMNS = ['category', 'cost_price', 'unit_price', 'quantity', 'tax_percents'];

function exportPdfColumnAlignments(array $headers): array
{
    return array_values(array_map(
        static fn ($header) => in_array(array_key_first($header), EXPORT_PDF_CENTERED_COLUMNS, true) ? 'C' : 'L',
        $headers
    ));
}

const EXPORT_PDF_PRIMARY_COLUMNS = [
    'items.item_id', 'item_number', 'name', 'category', 'company_name',
    'cost_price', 'unit_price', 'quantity', 'tax_percents',
];

/**
 * Splits $headers into a primary group (identifying/commerce columns, wide enough for
 * barcode/item name to display without heavy truncation) and a secondary group (everything
 * else, e.g. attribute columns) - each gets its own full-page-width set of pages, with the
 * ID column repeated at the start of the secondary group so rows can be cross-referenced.
 *
 * @return array{0: array, 1: array} [primaryHeaders, secondaryHeaders]
 */
function exportPdfSplitHeaders(array $headers): array
{
    $primary = [];
    $secondary = [];
    $idHeader = null;

    foreach ($headers as $header) {
        $key = array_key_first($header);

        if ($key === 'items.item_id') {
            $idHeader = $header;
        }

        if (in_array($key, EXPORT_PDF_PRIMARY_COLUMNS, true)) {
            $primary[] = $header;
        } else {
            $secondary[] = $header;
        }
    }

    if ($secondary !== [] && $idHeader !== null) {
        array_unshift($secondary, $idHeader);
    }

    return [$primary, $secondary];
}

function exportPdfRenderPageSet(ExportPdfWriter $pdf, array $headers, array $rows): void
{
    $pdf->SetFont('DejaVuSans', '', EXPORT_PDF_FONT_SIZE);

    $usableWidth = $pdf->GetPageWidth() - (2 * EXPORT_PDF_PAGE_MARGIN);
    $pdf->exportColumnWidths = array_values(exportPdfColumnWidths($pdf, $headers, $usableWidth));
    $pdf->exportColumnAlignments = exportPdfColumnAlignments($headers);
    $pdf->exportHeaderValues = array_values(array_map(
        static fn ($header) => exportPdfDisplayLabel((string) array_key_first($header), (string) reset($header)),
        $headers
    ));

    $pdf->AddPage();

    foreach ($rows as $row) {
        $values = array_values(exportPdfFormatRowValues(exportRowValues($headers, $row)));

        foreach ($values as $index => $value) {
            $width = $pdf->exportColumnWidths[$index];
            $text = exportPdfTruncateCellText($pdf, (string) $value, $width);
            $pdf->Cell($width, EXPORT_PDF_ROW_HEIGHT, $text, 1, 0, $pdf->exportColumnAlignments[$index]);
        }
        $pdf->Ln();
    }
}

function writeExportPdf(array $headers, iterable $rows): string
{
    $buildStart = microtime(true);

    set_time_limit(300);
    define('FPDF_FONTPATH', FCPATH . 'fonts/');
    require_once APPPATH . '../vendor/setasign/tfpdf/tfpdf.php';

    $pdf = new ExportPdfWriter('L', 'mm', 'A4');
    $pdf->AddFont('DejaVuSans', '', 'DejaVuSans.ttf', true);
    $pdf->AddFont('DejaVuSans', 'B', 'DejaVuSansB.ttf', true);
    $pdf->SetAutoPageBreak(true, EXPORT_PDF_PAGE_MARGIN);
    $pdf->SetMargins(EXPORT_PDF_PAGE_MARGIN, EXPORT_PDF_PAGE_MARGIN, EXPORT_PDF_PAGE_MARGIN);

    [$primaryHeaders, $secondaryHeaders] = exportPdfSplitHeaders($headers);

    // Buffer rows once so both page sets can iterate them without re-querying the DB.
    $rowCount = 0;
    $bufferedRows = [];
    foreach ($rows as $row) {
        $bufferedRows[] = $row;
        $rowCount++;
    }

    exportPdfRenderPageSet($pdf, $primaryHeaders, $bufferedRows);

    if ($secondaryHeaders !== []) {
        exportPdfRenderPageSet($pdf, $secondaryHeaders, $bufferedRows);
    }

    $buildMs = (int) round((microtime(true) - $buildStart) * 1000);
    log_message('debug', "Export pdf write: build={$buildMs}ms rows=$rowCount");

    return $pdf->Output('S');
}
