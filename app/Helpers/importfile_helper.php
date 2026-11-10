<?php

/**
 * @param array $stock_locations
 * @param array $attributes
 * @return string
 */
function generate_import_items_csv(array $stock_locations, array $attributes): string
{
    $csv_headers = pack('CCC', 0xef, 0xbb, 0xbf);    // Encode the Byte-Order Mark (BOM) so that UTF-8 File headers display properly in Microsoft Excel
    $csv_headers .= 'Id,Barcode,"Item Name",Category,"Supplier ID","Cost Price","Unit Price","Tax 1 Name","Tax 1 Percent","Tax 2 Name","Tax 2 Percent","Reorder Level",Description,"Allow Alt Description","Item has Serial Number",Image,HSN';
    $csv_headers .= generate_stock_location_headers($stock_locations);
    $csv_headers .= generate_attribute_headers($attributes);

    return $csv_headers;
}

/**
 * @param array $locations
 * @return string
 */
function generate_stock_location_headers(array $locations): string
{
    $locationHeaders = '';

    foreach ($locations as $locationName) {
        $locationHeaders .= ',"location_' . str_replace('"', '""', $locationName) . '"';
    }

    return $locationHeaders;
}

/**
 * @param array $attributeNames
 * @return string
 */
function generate_attribute_headers(array $attributeNames): string
{
    $attributeHeaders = '';
    unset($attributeNames[-1]);

    foreach ($attributeNames as $attributeName) {
        $attributeHeaders .= ',"attribute_' . str_replace('"', '""', $attributeName) . '"';
    }

    return $attributeHeaders;
}

/**
 * Processes a CSV file and returns it.
 * @param string $fileName
 * @return array A multidimensional array of rows found within the file and their associative key/value pairs.
 */
function get_csv_file(string $fileName): array
{
    $csvRows = [];

    if (($csvFile = fopen($fileName, 'r')) !== false) {
        helper('security');

        // Skip Byte-Order Mark
        if (bom_exists($csvFile)) {
            fseek($csvFile, 3);
        }

        $headers = fgetcsv($csvFile);

        if (!is_array($headers)) {
            fclose($csvFile);

            return $csvRows;
        }

        while (($row = fgetcsv($csvFile)) !== false) {
            if ($row !== [null] && is_array($row) && count($headers) === count($row)) {
                $csvRows[] = array_combine($headers, $row);
            }
        }

        fclose($csvFile);
    }

    return $csvRows;
}

/**
 * @param $file_handle
 * @return bool
 */
function bom_exists(&$file_handle): bool
{
    $result        = false;
    $candidate    = fread($file_handle, 3);

    rewind($file_handle);

    $bom = pack('CCC', 0xef, 0xbb, 0xbf);

    if (0 === strncmp($candidate, $bom, 3)) {
        $result = true;
    }

    return $result;
}

/**
 * Checks that the parsed item import rows expose the columns the current
 * template requires. A file whose header row does not match the template
 * (for example when the "Id" column is missing, an attribute column was
 * renamed, or a stock location was added since the file was last downloaded)
 * would otherwise trigger "Undefined array key" errors while building each
 * item. The required columns are derived from generate_import_items_csv(),
 * the same function that builds the downloadable template, so location and
 * attribute columns stay in sync automatically.
 *
 * @param array $csv_rows Rows returned by get_csv_file().
 * @param array $stock_locations Stock locations, as returned by Stock_location::get_allowed_locations().
 * @param array $attribute_names Attribute definition names, as returned by Attribute::get_definition_names().
 * @return bool True when the required columns are present.
 */
function csvImportHasRequiredItemHeaders(array $csv_rows, array $stock_locations, array $attribute_names): bool
{
    if (empty($csv_rows)) {
        return false;
    }

    $template_header_line = substr(generate_import_items_csv($stock_locations, $attribute_names), 3);
    $required_headers = str_getcsv($template_header_line);

    $present_headers = array_keys(reset($csv_rows));

    return empty(array_diff($required_headers, $present_headers));
}
