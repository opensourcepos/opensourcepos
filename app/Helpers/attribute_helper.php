<?php

/**
 * Translates the attribute type to the corresponding database column name.
 *
 * Maps attribute type constants to their corresponding attribute_values table columns.
 * Defaults to 'attribute_value' for TEXT, DROPDOWN and CHECKBOX attribute types.
 *
 * @param string $input The attribute type constant (DATE, DECIMAL, etc.)
 * @return string The database column name for storing this attribute type
 */
function getAttributeDataType(string $input): string
{
    $columnMap = [
        DATE => 'attribute_date',
        DECIMAL => 'attribute_decimal',
    ];

    return $columnMap[$input] ?? 'attribute_value';
}

/**
 * Validates that the provided data type is an allowed attribute value type.
 *
 * @param string $dataType
 * @return void
 */
function validateAttributeValueType(string $dataType): void
{
    $attributeValueTypes = ['attribute_value', 'attribute_decimal', 'attribute_date'];

    if (!in_array($dataType, $attributeValueTypes, true)) {
        throw new InvalidArgumentException('Invalid data type');
    }
}
