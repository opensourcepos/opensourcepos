<?php

/**
 * @param array $stock_locations
 * @param array $attributes
 * @return string
 */
function generateImportItemsCsv(array $stockLocations, array $attributes): string
{
    $csvHeaders = pack('CCC', 0xef, 0xbb, 0xbf);    // Encode the Byte-Order Mark (BOM) so that UTF-8 File headers display properly in Microsoft Excel
    $csvHeaders .= 'Id,Barcode,"Item Name",Category,"Supplier ID","Cost Price","Unit Price","Tax 1 Name","Tax 1 Percent","Tax 2 Name","Tax 2 Percent","Reorder Level",Description,"Allow Alt Description","Item has Serial Number",Image,HSN';
    $csvHeaders .= generateStockLocationHeaders($stockLocations);
    $csvHeaders .= generateAttributeHeaders($attributes);

    return $csvHeaders;
}

function generateStockLocationHeaders(array $locations): string
{
    $locationHeaders = '';

    foreach ($locations as $locationName) {
        $locationHeaders .= ',"location_' . $locationName . '"';
    }

    return $locationHeaders;
}

function generateAttributeHeaders(array $attributeNames): string
{
    $attributeHeaders = '';
    unset($attributeNames[-1]);

    foreach ($attributeNames as $attributeName) {
        $attributeHeaders .= ',"attribute_' . $attributeName . '"';
    }

    return $attributeHeaders;
}

/**
 * Processes a CSV file and returns it.
 * @param string $file_name
 * @return array A multidimensional array of rows found within the file and their associative key/value pairs.
 */
function get_csv_file(string $file_name): array
{
    $csvRows = false;

    if (($csvFile = fopen($file_name, 'r')) !== false) {
        helper('security');

        $csvRows = [];

        if (bomExists($csvFile)) {
            fseek($csvFile, 3);
        }

        $headers = fgetcsv($csvFile);

        while (($row = fgetcsv($csvFile)) !== false) {
            if ($row !== [null]) {
                $csvRows[] = array_combine($headers, $row);
            }
        }

        fclose($csvFile);
    }

    return $csvRows;
}

function bomExists(mixed &$fileHandle): bool
{
    $candidate = fread($fileHandle, 3);

    rewind($fileHandle);

    $bom = pack('CCC', 0xef, 0xbb, 0xbf);

    return 0 === strncmp($candidate, $bom, 3);
}

/**
 * Checks that the parsed item import rows expose the columns the current
 * template requires. A file whose header row does not match the template
 * (for example when the "Id" column is missing, an attribute column was
 * renamed, or a stock location was added since the file was last downloaded)
 * would otherwise trigger "Undefined array key" errors while building each
 * item. The required columns are derived from generateImportItemsCsv(),
 * the same function that builds the downloadable template, so location and
 * attribute columns stay in sync automatically.
 *
 * @param array $csv_rows Rows returned by get_csv_file().
 * @param array $stock_locations Stock locations, as returned by Stock_location::get_allowed_locations().
 * @param array $attribute_names Attribute definition names, as returned by Attribute::get_definition_names().
 * @return bool True when the required columns are present.
 */
function csvImportHasRequiredItemHeaders(array $csvRows, array $stockLocations, array $attributeNames): bool
{
    if (empty($csvRows)) {
        return false;
    }

    $templateHeaderLine = substr(generateImportItemsCsv($stockLocations, $attributeNames), 3);
    $requiredHeaders = str_getcsv($templateHeaderLine, ',', '"', '\\');

    $presentHeaders = array_keys(reset($csvRows));

    return empty(array_diff($requiredHeaders, $presentHeaders));
}
