<?php

namespace Tests\Helpers;

use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\OsposConfigFixtureTrait;

class export_helperTest extends CIUnitTestCase
{
    use OsposConfigFixtureTrait;

    protected function setUp(): void
    {
        require_once __DIR__ . '/../../app/Helpers/export_helper.php';
    }

    public function testSanitizeExportCsvValue_PrefixesEqualsWithQuote(): void
    {
        $this->assertSame("'=SUM(A1:A2)", sanitizeExportCsvValue('=SUM(A1:A2)'));
    }

    public function testSanitizeExportCsvValue_PrefixesPlusWithQuote(): void
    {
        $this->assertSame("'+HYPERLINK(1)", sanitizeExportCsvValue('+HYPERLINK(1)'));
    }

    public function testSanitizeExportCsvValue_PrefixesMinusWithQuote(): void
    {
        $this->assertSame("'-cmd", sanitizeExportCsvValue('-cmd'));
    }

    public function testSanitizeExportCsvValue_PrefixesAtWithQuote(): void
    {
        $this->assertSame("'@echo", sanitizeExportCsvValue('@echo'));
    }

    public function testSanitizeExportCsvValue_AcceptsNumericStringUnchanged(): void
    {
        $this->assertSame('-5', sanitizeExportCsvValue('-5'));
    }

    public function testSanitizeExportCsvValue_AcceptsPlainTextUnchanged(): void
    {
        $this->assertSame('Regular Item Name', sanitizeExportCsvValue('Regular Item Name'));
    }

    public function testSanitizeExportCsvValue_AcceptsEmptyStringUnchanged(): void
    {
        $this->assertSame('', sanitizeExportCsvValue(''));
    }

    public function testSanitizeExportCsvValue_AcceptsNonStringUnchanged(): void
    {
        $this->assertSame(42, sanitizeExportCsvValue(42));
        $this->assertNull(sanitizeExportCsvValue(null));
    }

    public function testWriteExportCsv_IncludesBomAndHeaderRow(): void
    {
        $headers = [['name' => 'Name'], ['value' => 'Value']];
        $rows = [(object) ['name' => 'Widget', 'value' => '10']];

        $csv = writeExportCsv($headers, $rows, ',');

        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString("Name,Value", $csv);
        $this->assertStringContainsString("Widget,10", $csv);
    }

    public function testWriteExportCsv_SanitizesFormulaValues(): void
    {
        $headers = [['name' => 'Name']];
        $rows = [(object) ['name' => '=cmd|calc']];

        $csv = writeExportCsv($headers, $rows, ',');

        $this->assertStringContainsString("'=cmd|calc", $csv);
    }

    public function testWriteExportCsv_UsesTabDelimiterForTxtFormat(): void
    {
        $headers = [['a' => 'A'], ['b' => 'B']];
        $rows = [(object) ['a' => '1', 'b' => '2']];

        $csv = writeExportCsv($headers, $rows, "\t");

        $this->assertStringContainsString("A\tB", $csv);
        $this->assertStringContainsString("1\t2", $csv);
    }

    public function testWriteExportJson_RoundTripsValues(): void
    {
        $headers = [['name' => 'Name'], ['value' => 'Value']];
        $rows = [(object) ['name' => 'Widget', 'value' => '10']];

        $json = writeExportJson($headers, $rows);
        $decoded = json_decode($json, true);

        $this->assertSame([['name' => 'Widget', 'value' => '10']], $decoded);
    }

    public function testWriteExportJson_AcceptsEmptyRowsAsEmptyArray(): void
    {
        $json = writeExportJson([['name' => 'Name']], []);

        $this->assertSame([], json_decode($json, true));
    }

    public function testSanitizeExportXmlTag_PrefixesNumericLeadingKey(): void
    {
        $this->assertSame('col_123', sanitizeExportXmlTag('123'));
    }

    public function testSanitizeExportXmlTag_ReplacesNonAlnumChars(): void
    {
        $this->assertSame('a_b_c', sanitizeExportXmlTag('a.b-c'));
    }

    public function testSanitizeExportXmlTag_FallsBackToColumnWhenEmpty(): void
    {
        $this->assertSame('column', sanitizeExportXmlTag(''));
    }

    public function testSanitizeExportXmlTag_AcceptsValidKeyUnchanged(): void
    {
        $this->assertSame('item_id', sanitizeExportXmlTag('item_id'));
    }

    public function testWriteExportXml_ProducesValidDocumentWithSanitizedTags(): void
    {
        $headers = [['123' => 'Numeric'], ['items.item_id' => 'Id']];
        $rows = [(object) ['123' => 'foo', 'item_id' => '7']];

        $xml = writeExportXml($headers, $rows);

        $this->assertStringContainsString('<col_123>foo</col_123>', $xml);
        $this->assertStringContainsString('<items_item_id>7</items_item_id>', $xml);

        $sxml = simplexml_load_string($xml);
        $this->assertNotFalse($sxml, 'writeExportXml() must produce well-formed XML');
    }

    public function testGenerateExportFile_RejectsUnsupportedFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);

        generateExportFile('exe', [['a' => 'A']], [], 'base', 'items');
    }

    public function testGenerateExportFile_UsesCorrectExtensionPerFormat(): void
    {
        $headers = [['name' => 'Name']];
        $rows = [(object) ['name' => 'Widget']];

        $csvFile = generateExportFile('csv', $headers, $rows, 'export', 'items');
        $this->assertSame('export.csv', $csvFile['filename']);

        $jsonFile = generateExportFile('json', $headers, $rows, 'export', 'items');
        $this->assertSame('export.json', $jsonFile['filename']);

        $txtFile = generateExportFile('txt', $headers, $rows, 'export', 'items');
        $this->assertSame('export.txt', $txtFile['filename']);

        $xmlFile = generateExportFile('xml', $headers, $rows, 'export', 'items');
        $this->assertSame('export.xml', $xmlFile['filename']);
    }

    public function testExportSqlUnprefixedTableName_StripsConfiguredPrefix(): void
    {
        parent::setUp();
        $db = db_connect();

        $this->assertSame('items', exportSqlUnprefixedTableName($db->prefixTable('items')));
    }

    public function testExportSqlUnprefixedTableName_AcceptsUnprefixedNameUnchanged(): void
    {
        parent::setUp();

        $this->assertSame('items', exportSqlUnprefixedTableName('items'));
    }

    public function testWriteExportSql_ReturnsCommentForNonRestorableTable(): void
    {
        parent::setUp();

        $sql = writeExportSql([['foo' => 'Foo']], [], 'ospos_some_report_view');

        $this->assertStringContainsString('no directly-restorable columns', $sql);
    }

    public function testWriteExportSql_ReturnsCommentWhenNoHeaderColumnsMapToTable(): void
    {
        parent::setUp();
        $db = db_connect();

        $sql = writeExportSql([['not_a_real_column' => 'Nope']], [], $db->prefixTable('giftcards'));

        $this->assertStringContainsString("None of this export's columns map to real", $sql);
    }

    public function testWriteExportSql_ReturnsCommentForEmptyRowSet(): void
    {
        parent::setUp();
        $db = db_connect();

        $sql = writeExportSql([['giftcard_id' => 'Id']], [], $db->prefixTable('giftcards'));

        $this->assertStringContainsString('No rows matched', $sql);
    }

    public function testWriteExportSql_ProducesInsertWithRequiredHiddenColumns(): void
    {
        parent::setUp();
        $db = db_connect();
        $tableName = $db->prefixTable('giftcards');

        $headers = [
            ['giftcard_id' => 'Id'],
            ['giftcard_number' => 'Number'],
            ['value' => 'Value'],
        ];
        $rows = [(object) ['giftcard_id' => 1, 'giftcard_number' => 'GC-1', 'value' => '25.00']];

        $sql = writeExportSql($headers, $rows, $tableName);

        $this->assertStringContainsString("INSERT INTO `$tableName`", $sql);
        $this->assertStringContainsString('`giftcard_id`, `giftcard_number`, `value`', $sql);
        $this->assertStringContainsString("'GC-1'", $sql);
    }

    public function testWriteExportSql_IncludesHiddenNotNullColumnsForItems(): void
    {
        parent::setUp();
        $db = db_connect();
        $tableName = $db->prefixTable('items');

        $headers = [
            ['items.item_id' => 'Id'],
            ['name' => 'Name'],
        ];
        $rows = [(object) [
            'items.item_id'         => 1,
            'name'                  => 'Widget',
            'description'           => 'A widget',
            'allow_alt_description' => 0,
            'is_serialized'         => 0,
        ]];

        $sql = writeExportSql($headers, $rows, $tableName);

        // Hidden NOT NULL columns must be appended even though they aren't in $headers.
        $this->assertStringContainsString('`description`', $sql);
        $this->assertStringContainsString('`allow_alt_description`', $sql);
        $this->assertStringContainsString('`is_serialized`', $sql);
        $this->assertStringContainsString("'A widget'", $sql);
    }

    public function testSweepStaleExportCache_RemovesOnlyFilesOlderThanTenMinutes(): void
    {
        parent::setUp();

        $directory = WRITEPATH . 'cache/exports/';
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $staleFile = $directory . 'xlsx_stale_' . uniqid() . '.tmp';
        $freshFile = $directory . 'xlsx_fresh_' . uniqid() . '.tmp';

        file_put_contents($staleFile, 'stale');
        file_put_contents($freshFile, 'fresh');
        touch($staleFile, time() - 700);
        touch($freshFile, time() - 5);

        try {
            sweepStaleExportCache();

            $this->assertFileDoesNotExist($staleFile);
            $this->assertFileExists($freshFile);
        } finally {
            @unlink($staleFile);
            @unlink($freshFile);
        }
    }

    public function testExportXlsxColumnFormats_AssignsIntegerFormatForIdColumns(): void
    {
        $this->injectOsposConfig();

        $headers = [['items.item_id' => 'Id']];
        $formats = exportXlsxColumnFormats($headers);

        $this->assertSame('0', $formats[0]);
    }

    public function testExportXlsxColumnFormats_AssignsQuantityFormat(): void
    {
        $this->injectOsposConfig();

        $headers = [['quantity' => 'Quantity']];
        $formats = exportXlsxColumnFormats($headers);

        $this->assertSame('0.0000', $formats[0]);
    }

    public function testExportXlsxColumnFormats_AssignsCurrencyFormat(): void
    {
        $this->injectOsposConfig();

        $headers = [['cost_price' => 'Cost Price']];
        $formats = exportXlsxColumnFormats($headers);

        $this->assertSame('0.00', $formats[0]);
    }

    public function testExportXlsxColumnFormats_AssignsDecimalFormatForDecimalTypeHeader(): void
    {
        $this->injectOsposConfig();

        $headers = [['attribute_weight' => 'Weight', 'type' => DECIMAL]];
        $formats = exportXlsxColumnFormats($headers);

        $this->assertSame('0.00', $formats[0]);
    }

    public function testExportXlsxColumnFormats_AcceptsPlainTextColumnAsNull(): void
    {
        $this->injectOsposConfig();

        $headers = [['name' => 'Name']];
        $formats = exportXlsxColumnFormats($headers);

        $this->assertNull($formats[0]);
    }

    private function injectOsposConfig(): void
    {
        parent::setUp();

        $this->injectOsposSettings([
            'quantity_decimals' => 4,
            'currency_decimals' => 2,
            'number_locale'     => 'en_US',
            'currency_symbol'   => '$',
        ]);
    }

    private function newPdfWriter(): ExportPdfWriter
    {
        if (!defined('FPDF_FONTPATH')) {
            define('FPDF_FONTPATH', FCPATH . 'fonts/');
        }
        require_once APPPATH . '../vendor/setasign/tfpdf/tfpdf.php';

        $pdf = new ExportPdfWriter('L', 'mm', 'A4');
        $pdf->AddFont('DejaVuSans', '', 'DejaVuSans.ttf', true);
        $pdf->AddFont('DejaVuSans', 'B', 'DejaVuSansB.ttf', true);
        $pdf->SetFont('DejaVuSans', '', 9);

        return $pdf;
    }

    public function testExportPdfDisplayLabel_StripsAttributePrefix(): void
    {
        $this->assertSame('Color', exportPdfDisplayLabel('attribute_Color', 'attribute_Color'));
    }

    public function testExportPdfDisplayLabel_AcceptsNonAttributeKeyUnchanged(): void
    {
        $this->assertSame('Item Name', exportPdfDisplayLabel('name', 'Item Name'));
    }

    public function testExportPdfColumnWidths_GivesIdColumnFixedBarcodeWidth(): void
    {
        $pdf = $this->newPdfWriter();
        $headers = [['items.item_id' => 'Id']];

        $widths = exportPdfColumnWidths($pdf, $headers, 200.0);

        $this->assertGreaterThan(0, $widths[0]);
    }

    public function testExportPdfColumnWidths_ExpandColumnAbsorbsLeftoverWidth(): void
    {
        $pdf = $this->newPdfWriter();
        $headers = [['category' => 'Category'], ['name' => 'Name']];

        $widths = exportPdfColumnWidths($pdf, $headers, 200.0);

        $this->assertGreaterThan($widths[0], $widths[1]);
    }

    public function testExportPdfColumnWidths_SplitsRemainingWidthAcrossMultipleExpandColumns(): void
    {
        $pdf = $this->newPdfWriter();
        $headers = [
            ['attribute_notes' => 'Notes', 'type' => TEXT],
            ['attribute_description' => 'Description', 'type' => TEXT],
        ];

        $widths = exportPdfColumnWidths($pdf, $headers, 200.0);

        $this->assertEqualsWithDelta($widths[0], $widths[1], 0.01);
    }

    public function testExportPdfTruncateCellText_AcceptsShortTextUnchanged(): void
    {
        $pdf = $this->newPdfWriter();

        $this->assertSame('Short', exportPdfTruncateCellText($pdf, 'Short', 50.0));
    }

    public function testExportPdfTruncateCellText_TruncatesLongTextWithEllipsis(): void
    {
        $pdf = $this->newPdfWriter();
        $longText = str_repeat('Lorem ipsum dolor sit amet ', 10);

        $result = exportPdfTruncateCellText($pdf, $longText, 20.0);

        $this->assertStringEndsWith('…', $result);
        $this->assertLessThan(mb_strlen($longText), mb_strlen($result));
        $this->assertLessThanOrEqual(20.0 - 2.0, $pdf->GetStringWidth($result));
    }

    public function testExportPdfTruncateCellText_ReturnsEllipsisAloneWhenColumnTooNarrow(): void
    {
        $pdf = $this->newPdfWriter();

        $result = exportPdfTruncateCellText($pdf, 'Something long', 2.0);

        $this->assertSame('…', $result);
    }

    public function testExportPdfColumnAlignments_CentersConfiguredColumns(): void
    {
        $headers = [['cost_price' => 'Cost Price'], ['name' => 'Name']];

        $alignments = exportPdfColumnAlignments($headers);

        $this->assertSame('C', $alignments[0]);
        $this->assertSame('L', $alignments[1]);
    }

    public function testExportPdfFormatRowValues_FormatsQuantityAndCurrencyColumns(): void
    {
        $this->injectOsposConfig();

        $values = exportPdfFormatRowValues(['quantity' => '10', 'cost_price' => '5.5', 'name' => 'Widget']);

        $this->assertSame('Widget', $values['name']);
        $this->assertSame(to_quantity_decimals('10'), $values['quantity']);
        $this->assertSame(to_currency('5.5'), $values['cost_price']);
        $this->assertNotSame('10', $values['quantity']);
        $this->assertNotSame('5.5', $values['cost_price']);
    }

    public function testExportPdfFormatRowValues_AcceptsEmptyQuantityUnchanged(): void
    {
        parent::setUp();

        $values = exportPdfFormatRowValues(['quantity' => '', 'cost_price' => '']);

        $this->assertSame('', $values['quantity']);
        $this->assertSame('', $values['cost_price']);
    }

    public function testExportPdfSplitHeaders_SeparatesPrimaryAndSecondaryColumns(): void
    {
        $headers = [
            ['items.item_id' => 'Id'],
            ['name' => 'Name'],
            ['attribute_notes' => 'Notes'],
        ];

        [$primary, $secondary] = exportPdfSplitHeaders($headers);

        $this->assertCount(2, $primary);
        $this->assertCount(2, $secondary, 'secondary group must include the repeated id column plus the attribute column');
        $this->assertSame('items.item_id', array_key_first($secondary[0]));
        $this->assertSame('attribute_notes', array_key_first($secondary[1]));
    }

    public function testExportPdfSplitHeaders_AcceptsAllPrimaryColumnsWithEmptySecondary(): void
    {
        $headers = [['items.item_id' => 'Id'], ['name' => 'Name']];

        [$primary, $secondary] = exportPdfSplitHeaders($headers);

        $this->assertCount(2, $primary);
        $this->assertSame([], $secondary);
    }

    public function testWriteExportPdf_ProducesNonEmptyPdfDocument(): void
    {
        parent::setUp();

        $headers = [['items.item_id' => 'Id'], ['name' => 'Name']];
        $rows = [(object) ['items.item_id' => 1, 'name' => 'Widget']];

        $pdf = writeExportPdf($headers, $rows);

        $this->assertStringStartsWith('%PDF-', $pdf);
    }
}
