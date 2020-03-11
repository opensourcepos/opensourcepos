<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Generates the header content for the import_items.csv file
 *
 * @return	string						Comma separated headers for the CSV file
 */
function generate_import_items_csv($stock_locations,$attributes)
{
	$csv_headers = pack("CCC",0xef,0xbb,0xbf);	//Encode the Byte-Order Mark (BOM) so that UTF-8 File headers display properly in Microsoft Excel
	$csv_headers .= 'Barcode,"Item Name",Category,"Supplier ID","Cost Price","Unit Price","Tax 1 Name","Tax 1 Percent","Tax 2 Name","Tax 2 Percent","Reorder Level",Description,"Allow Alt Description","Item has Serial Number",item_image,HSN';
	$csv_headers .= generate_stock_location_headers($stock_locations);
	$csv_headers .= generate_attribute_headers($attributes);
	
	return $csv_headers;
}

/**
 * Generates a list of stock location names as a string
 *
 * @return string					Comma-separated list of stock location names
 */
function generate_stock_location_headers($locations)
{
	$location_headers = "";
	
	foreach($locations as $location_id => $location_name)
	{
		$location_headers .= ',"location_' . $location_name . '"';
	}
	
	return $location_headers;
}

/**
 * Generates a list of attribute names as a string
 *
 * @return string					Comma-separated list of attribute names
 */
function generate_attribute_headers($attribute_names)
{
	$attribute_headers = "";
	unset($attribute_names[-1]);
	
	foreach($attribute_names as $attribute_name)
	{
		$attribute_headers .= ',"attribute_' . $attribute_name . '"';
	}
	
	return $attribute_headers;
}

/**
 * Read the contents of a given CSV formatted file into a two-dimensional array
 *
 * @param	string				$file_name	Name of the file to read.
 * @return	boolean|array[][]				two-dimensional array with the file contents or FALSE on failure.
 */
function get_csv_file($file_name)
{
	ini_set("auto_detect_line_endings", true);
	
	if(($csv_file = fopen($file_name,'r')) !== FALSE)
	{
		//Skip Byte-Order Mark
		if(bom_exists($csv_file) === TRUE)
		{
			fseek($csv_file, 3);
		}
		
		while (($data = fgetcsv($csv_file)) !== FALSE)
		{
			$line_array[] = $data;
		}
	}
	else
	{
		return FALSE;
	}
	
	return $line_array;
}

/**
 * Checks the first three characters of a file for the Byte-Order Mark then returns the file position to the first character.
 *
 * @param	object $file_handle	File handle to check
 * @return	bool				Returns TRUE if the BOM exists and FALSE otherwise.
 */
function bom_exists(&$file_handle)
{
	$str = fread($file_handle,3);
	rewind($file_handle);
	
	$bom = pack("CCC", 0xef, 0xbb, 0xbf);
	
	if (0 === strncmp($str, $bom, 3))
	{
		return TRUE;
	}
	else
	{
		return FALSE;
	}
}
?>