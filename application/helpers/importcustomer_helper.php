<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Generates the header content for the import_customers.csv file
 *
 * @return	string						Comma separated headers for the CSV file
 */
function generate_import_customers_csv($tags)
{
	$csv_headers = pack("CCC",0xef,0xbb,0xbf);	//Encode the Byte-Order Mark (BOM) so that UTF-8 File headers display properly in Microsoft Excel
	$csv_headers .= '"First Name","Last Name",Gender,Consent,Email,"Phone Number","Address 1",Address2,City,State,Zip,Country,Comments,Company,"Account Number",Discount,Discount_Type,Taxable';
	$csv_headers .= generate_tag_headers($tags);

	return $csv_headers;
}


/**
 * Generates a list of tag names as a string
 *
 * @return string					Comma-separated list of tag names
 */
function generate_tag_headers($tag_names)
{
	$tag_headers = "";
	unset($tag_names[-1]);

	foreach($tag_names as $tag_name)
	{
		$tag_headers .= ',"tag_' . $tag_name . '"';
	}

	return $tag_headers;
}

/**
 * Read the contents of a given CSV formatted file into a two-dimensional array
 *
 * @param	string				$file_name	Name of the file to read.
 * @return	boolean|array[][]				two-dimensional array with the file contents or FALSE on failure.
 */
function get_customer_csv_file($file_name)
{
	ini_set("auto_detect_line_endings", true);

	if(($csv_file = fopen($file_name,'r')) !== FALSE)
	{
		//Skip Byte-Order Mark
		if(customer_bom_exists($csv_file) === TRUE)
		{
			fseek($csv_file, 3);
		}

		while (($data = fgetcsv($csv_file)) !== FALSE)
		{
		//Skip empty lines
			if(array(null) !== $data)
			{
				$line_array[] = $data;
			}
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
function customer_bom_exists(&$file_handle)
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