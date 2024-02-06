<?php

/**
 * @param array $stock_locations
 * @param array $attributes
 * @return string
 */
function generate_import_items_csv(array $stock_locations, array $attributes): string
{
	$csv_headers = pack('CCC',0xef,0xbb,0xbf);	//Encode the Byte-Order Mark (BOM) so that UTF-8 File headers display properly in Microsoft Excel
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
	$location_headers = '';

	foreach($locations as $location_name)
	{
		$location_headers .= ',"location_' . $location_name . '"';
	}

	return $location_headers;
}

/**
 * @param array $attribute_names
 * @return string
 */
function generate_attribute_headers(array $attribute_names): string
{
	$attribute_headers = '';
	unset($attribute_names[-1]);

	foreach($attribute_names as $attribute_name)
	{
		$attribute_headers .= ',"attribute_' . $attribute_name . '"';
	}

	return $attribute_headers;
}

/**
 * Processes a CSV file and returns it.
 * @param string $file_name
 * @return array A multidimensional array of rows found within the file and their associative key/value pairs.
 */
function get_csv_file(string $file_name): array
{
	$csv_rows = false;

	if(($csv_file = fopen($file_name,'r')) !== false)
	{
		helper('security');

		$csv_rows = [];

		//Skip Byte-Order Mark
		if(bom_exists($csv_file))
		{
			fseek($csv_file, 3);
		}

		$headers = fgetcsv($csv_file);

		while(($row = fgetcsv($csv_file)) !== false)
		{
			if($row !== [null])
			{
				$csv_rows[] = array_combine($headers, $row);
			}
		}

		fclose($csv_file);
	}

	return $csv_rows;
}

/**
 * @param $file_handle
 * @return bool
 */
function bom_exists(&$file_handle): bool
{
	$result		= false;
	$candidate	= fread($file_handle, 3);

	rewind($file_handle);

	$bom = pack('CCC', 0xef, 0xbb, 0xbf);

	if (0 === strncmp($candidate, $bom, 3))
	{
		$result = true;
	}

	return $result;
}
