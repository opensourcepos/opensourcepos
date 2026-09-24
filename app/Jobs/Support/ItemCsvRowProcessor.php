<?php

namespace App\Jobs\Support;

use App\Models\Attribute;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\Item_quantity;
use App\Models\Item_taxes;
use App\Models\Stock_location;
use App\Models\Supplier;
use CodeIgniter\Validation\FormatRules;

/**
 * Per-row Items CSV import logic (issue #3833 Phase 3), extracted from
 * Items::postImportCsvFile() so it can run both synchronously (controller)
 * and from ItemImportJob (queue worker, no HTTP/session context). Each row
 * is processed and saved independently — there is no shared transaction
 * across rows, matching the Customers import's existing partial-success
 * behavior instead of the old all-or-nothing whole-file transaction.
 */
class ItemCsvRowProcessor
{
    private Item $item;
    private Item_quantity $itemQuantity;
    private Item_taxes $itemTaxes;
    private Inventory $inventory;
    private Attribute $attribute;
    private Supplier $supplier;
    private Stock_location $stockLocation;

    public function __construct()
    {
        $this->item = model(Item::class);
        $this->itemQuantity = model(Item_quantity::class);
        $this->itemTaxes = model(Item_taxes::class);
        $this->inventory = model(Inventory::class);
        $this->attribute = model(Attribute::class);
        $this->supplier = model(Supplier::class);
        $this->stockLocation = model(Stock_location::class);
    }

    /**
     * @param array $row CSV row, keyed by header column name.
     * @param int $employeeId Employee id of the user who started the import.
     * @param array $definitionNames Attribute definition names (see Attribute::getDefinitionNames()).
     * @param array $attributeData Attribute definition lookups (see Attribute::getDefinitionByName()/getDefinitionValues()).
     * @return bool True on success, false if the row failed validation or save.
     */
    public function process(array $row, int $employeeId, array $definitionNames, array $attributeData): bool
    {
        $allowedStockLocations = $this->stockLocation->get_allowed_locations();

        $itemId = (int)$row['Id'];
        $isUpdate = ($itemId > 0);
        $itemData = [
            'item_id'       => $itemId,
            'name'          => $row['Item Name'],
            'description'   => filter_var($row['Description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'category'      => $row['Category'],
            'cost_price'    => $row['Cost Price'],
            'unit_price'    => $row['Unit Price'],
            'reorder_level' => $row['Reorder Level'],
            'deleted'       => false,
            'hsn_code'      => $row['HSN'],
            'pic_filename'  => $row['Image'],
        ];

        if (!empty($row['Supplier ID'])) {
            $itemData['supplier_id'] = $this->supplier->exists($row['Supplier ID']) ? $row['Supplier ID'] : null;
        }

        if ($isUpdate) {
            $itemData['allow_alt_description'] = $row['Allow Alt Description'] === '' ? null : $row['Allow Alt Description'];
            $itemData['is_serialized'] = $row['Item has Serial Number'] === '' ? null : $row['Item has Serial Number'];
        } else {
            $itemData['allow_alt_description'] = $row['Allow Alt Description'] === '' ? '0' : '1';
            $itemData['is_serialized'] = $row['Item has Serial Number'] === '' ? '0' : '1';
        }

        $isFailedRow = false;

        if (!empty($row['Barcode'])) {
            $itemData['item_number'] = $row['Barcode'];
            $isFailedRow = $this->item->item_number_exists($itemData['item_number'], $itemId);
        }

        if (!$isFailedRow) {
            $isFailedRow = $this->validateRow($row, $itemData, $allowedStockLocations, $definitionNames, $attributeData);
        }

        // Remove false, null, '' and empty strings but keep 0
        $itemData = array_filter($itemData, static fn ($value) => $value !== null && strlen($value));

        if ($isFailedRow || !$this->item->save_value($itemData, $itemId)) {
            return false;
        }

        $success = true;

        if (!$this->saveTaxData($row, $itemData)) {
            $success = false;
        }

        if (!$this->saveInventoryQuantities($row, $itemData, $allowedStockLocations, $employeeId)) {
            $success = false;
        }

        $csvAttributeValues = $this->extractAttributeData($row);

        if (!$this->attribute->saveCSVRowAttributeData($csvAttributeValues, $itemData, $attributeData)) {
            $success = false;
        }

        return $success;
    }

    private function extractAttributeData(array $row): array
    {
        $attributeData = [];

        foreach ($row as $key => $value) {
            if (str_starts_with($key, 'attribute_')) {
                $definitionName = substr($key, 10);
                $attributeData[$definitionName] = $value;
            }
        }

        return $attributeData;
    }

    /**
     * @return array Invalid location names found in the row, empty if all valid.
     */
    private function validateStockLocations(array $row, array $allowedLocations): array
    {
        $invalidLocations = [];
        $allowedLocationNames = array_values($allowedLocations);

        foreach (array_keys($row) as $key) {
            if (str_starts_with($key, 'location_')) {
                $locationName = substr($key, 9);
                if (!in_array($locationName, $allowedLocationNames)) {
                    $invalidLocations[] = $locationName;
                }
            }
        }

        return $invalidLocations;
    }

    /**
     * @return bool True when there is an error in the row's data.
     */
    private function validateRow(array $row, array $itemData, array $allowedStockLocations, array $definitionNames, array $attributeData): bool
    {
        $itemId = $row['Id'];
        $isUpdate = (bool)$itemId;

        $valuesToCheckForEmpty = [
            'name'       => $itemData['name'],
            'category'   => $itemData['category'],
            'unit_price' => $itemData['unit_price'],
        ];

        foreach ($valuesToCheckForEmpty as $key => $value) {
            if (($value === null || $value === '') && !$isUpdate) {
                log_message('error', "Empty required value in $key.");

                return true;
            }
        }

        if (!$isUpdate) {
            $itemData['cost_price'] = empty($itemData['cost_price']) ? 0 : $itemData['cost_price'];
        } elseif (!$this->item->exists($itemId)) {
            log_message('error', "non-existent item_id: '$itemId' when either existing item_id or no item_id is required.");

            return true;
        }

        $valuesToCheckForNumeric = [
            'cost_price'    => $itemData['cost_price'],
            'unit_price'    => $itemData['unit_price'],
            'reorder_level' => $itemData['reorder_level'],
            'supplier_id'   => $row['Supplier ID'],
            'Tax 1 Percent' => $row['Tax 1 Percent'],
            'Tax 2 Percent' => $row['Tax 2 Percent'],
        ];

        foreach ($allowedStockLocations as $locationName) {
            $valuesToCheckForNumeric[] = $row["location_$locationName"];
        }

        foreach ($valuesToCheckForNumeric as $key => $value) {
            if (!is_numeric($value) && !empty($value)) {
                log_message('error', "non-numeric: '$value' for '$key' when numeric is required");

                return true;
            }
        }

        if (!empty($itemData['item_number'])) {
            $formatRules = new FormatRules();

            if (!$formatRules->alpha_numeric_punct($itemData['item_number'])) {
                log_message('error', "invalid item_number: '{$itemData['item_number']}' contains disallowed characters");

                return true;
            }
        }

        $invalidLocations = $this->validateStockLocations($row, $allowedStockLocations);

        if (!empty($invalidLocations)) {
            log_message('error', 'CSV import: Invalid stock location(s) found: ' . implode(', ', $invalidLocations));

            return true;
        }

        foreach ($definitionNames as $definitionName) {
            $attributeColumn = "attribute_$definitionName";

            if (!array_key_exists($attributeColumn, $row) || $row[$attributeColumn] == '') {
                continue;
            }

            $definitionType = $attributeData[$definitionName]['definition_type'];
            $attributeValue = $row[$attributeColumn];

            if (strcasecmp($attributeValue, '_DELETE_') === 0) {
                continue;
            }

            switch ($definitionType) {
                case DROPDOWN:
                    $dropdownValues = $attributeData[$definitionName]['dropdown_values'];
                    $dropdownValues[] = '';

                    if (!empty($attributeValue) && !in_array($attributeValue, $dropdownValues)) {
                        log_message('error', "Value: '$attributeValue' is not an acceptable DROPDOWN value");

                        return true;
                    }
                    break;
                case DECIMAL:
                    if (!is_numeric($attributeValue) && !empty($attributeValue)) {
                        log_message('error', "'$attributeValue' is not an acceptable DECIMAL value");

                        return true;
                    }
                    break;
                case DATE:
                    if (!isValidDate($attributeValue) && !empty($attributeValue)) {
                        log_message('error', "'$attributeValue' is not an acceptable DATE value. The value must match the set locale.");

                        return true;
                    }
                    break;
            }
        }

        return false;
    }

    private function saveInventoryQuantities(array $row, array $itemData, array $allowedLocations, int $employeeId): bool
    {
        $comment = lang('Items.inventory_CSV_import_quantity');
        $isUpdate = (bool)$row['Id'];
        $success = true;

        foreach ($allowedLocations as $locationId => $locationName) {
            $itemQuantityData = ['item_id' => $itemData['item_id'], 'location_id' => $locationId];

            $csvData = [
                'trans_items'    => $itemData['item_id'],
                'trans_user'     => $employeeId,
                'trans_comment'  => $comment,
                'trans_location' => $locationId,
            ];

            if (!empty($row["location_$locationName"]) || $row["location_$locationName"] === '0') {
                $itemQuantityData['quantity'] = $row["location_$locationName"];
                $success &= $this->itemQuantity->save_value($itemQuantityData, $itemData['item_id'], $locationId);

                $csvData['trans_inventory'] = $row["location_$locationName"];
                $success &= (bool)$this->inventory->insert($csvData, false);
            } elseif ($isUpdate) {
                continue;
            } else {
                $itemQuantityData['quantity'] = 0;
                $success &= $this->itemQuantity->save_value($itemQuantityData, $itemData['item_id'], $locationId);

                $csvData['trans_inventory'] = 0;
                $success &= (bool)$this->inventory->insert($csvData, false);
            }
        }

        return (bool)$success;
    }

    private function saveTaxData(array $row, array $itemData): bool
    {
        $itemsTaxesData = [];

        if (is_numeric($row['Tax 1 Percent']) && $row['Tax 1 Name'] !== '') {
            $itemsTaxesData[] = ['name' => $row['Tax 1 Name'], 'percent' => $row['Tax 1 Percent']];
        }

        if (is_numeric($row['Tax 2 Percent']) && $row['Tax 2 Name'] !== '') {
            $itemsTaxesData[] = ['name' => $row['Tax 2 Name'], 'percent' => $row['Tax 2 Percent']];
        }

        if (!empty($itemsTaxesData)) {
            return $this->itemTaxes->save_value($itemsTaxesData, $itemData['item_id']);
        }

        return true;
    }
}
