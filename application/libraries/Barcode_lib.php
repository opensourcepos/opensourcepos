<?php

use emberlabs\Barcode\BarcodeBase;
require APPPATH.'/views/barcodes/BarcodeBase.php';
require APPPATH.'/views/barcodes/Code39.php';
require APPPATH.'/views/barcodes/Code128.php';
require APPPATH.'/views/barcodes/Ean13.php';
require APPPATH.'/views/barcodes/Ean8.php';

class Barcode_lib
{
	private $CI = null;
	private $supported_barcodes = array('Code39' => 'Code 39', 'Code128' => 'Code 128', 'Ean8' => 'EAN 8', 'Ean13' => 'EAN 13');
	
	function __construct()
	{
		$this->CI =& get_instance();
	}
	
	public function get_list_barcodes()
	{
		return $this->supported_barcodes;
	}
	
	public function get_barcode_config()
	{
		$data['company'] = $this->CI->Appconfig->get('company');
		$data['barcode_content'] = $this->CI->Appconfig->get('barcode_content');
		$data['barcode_type'] = $this->CI->Appconfig->get('barcode_type');
		$data['barcode_font'] = $this->CI->Appconfig->get('barcode_font');
		$data['barcode_font_size'] = $this->CI->Appconfig->get('barcode_font_size');
		$data['barcode_height'] = $this->CI->Appconfig->get('barcode_height');
		$data['barcode_width'] = $this->CI->Appconfig->get('barcode_width');
		$data['barcode_quality'] = $this->CI->Appconfig->get('barcode_quality');
		$data['barcode_first_row'] = $this->CI->Appconfig->get('barcode_first_row');
		$data['barcode_second_row'] = $this->CI->Appconfig->get('barcode_second_row');
		$data['barcode_third_row'] = $this->CI->Appconfig->get('barcode_third_row');
		$data['barcode_num_in_row'] = $this->CI->Appconfig->get('barcode_num_in_row');
		$data['barcode_page_width'] = $this->CI->Appconfig->get('barcode_page_width');	  
		$data['barcode_page_cellspacing'] = $this->CI->Appconfig->get('barcode_page_cellspacing');
		
		return $data;
	}
	
	private function get_barcode_instance($barcode_type='Code128')
	{
		switch($barcode_type)
		{
			case 'Code39':
				return new emberlabs\Barcode\Code39();
				break;
				
			case 'Code128':
			default:
				return new emberlabs\Barcode\Code128();
				break;
				
			case 'Ean8':
				return new emberlabs\Barcode\Ean8();
				break;
				
			case 'Ean13':
				return new emberlabs\Barcode\Ean13();
				break;
		}
	}
	
	private function generate_barcode($item, $barcode_config)
	{
		try
		{
			$barcode = $this->get_barcode_instance($barcode_config['barcode_type']);

			// generate a barcode only if one is not already available and we use the item_id as seed.
			// This avoids generating Barcodes out of existing Barcodes
			if( $barcode_config['barcode_content'] !== "id" && isset($item['item_number']) )
			{
				$barcode->setData($item['item_number'], false);
			}
			else
			{
				$barcode->setData($item['item_id'], true);
			}
			$barcode->setQuality($barcode_config['barcode_quality']);
			$barcode->setDimensions($barcode_config['barcode_width'], $barcode_config['barcode_height']);

			$barcode->draw();
			
			return $barcode->base64();
		} 
		catch(Exception $e)
		{
			echo 'Caught exception: ', $e->getMessage(), "\n";		
		}
	}

	public function generate_receipt_barcode($barcode_content)
	{
		try
		{
			// Code128 is the default and used in this case for the receipts
			$barcode = $this->get_barcode_instance();

			// set the receipt number to generate the barcode for
			$barcode->setData($barcode_content, false);
			
			// image quality 100
			$barcode->setQuality(100);
			
			// width: 200, height: 30
			$barcode->setDimensions(200, 30);

			// draw the image
			$barcode->draw();
			
			return $barcode->base64();
		} 
		catch(Exception $e)
		{
			echo 'Caught exception: ', $e->getMessage(), "\n";		
		}
	}
	
	public function get_barcode($item, $barcode_config)
	{
		try
		{
			$barcode = $this->get_barcode_instance($barcode_config['barcode_type']);

			// generate a barcode only if one is not already available and we use the item_id as seed.
			// This to avoid generating Barcodes out of existing Barcodes
			if( $barcode_config['barcode_content'] !== "id" && isset($item['item_number']) )
			{
				$barcode->setData($item['item_number'], false);
				
				return null;
			}
			else
			{
				$barcode->setData($item['item_id'], true);
				
				return $barcode->getData();
			}
		} 
		catch(Exception $e)
		{
			echo 'Caught exception: ', $e->getMessage(), "\n";		
		}
	}

	public function create_display_barcode($item, $barcode_config)
	{
		$display_table = "<table>";
		$display_table .= "<tr><td align='center'>" . $this->manage_display_layout($barcode_config['barcode_first_row'], $item, $barcode_config) . "</td></tr>";
		$barcode = $this->generate_barcode($item, $barcode_config);
		$display_table .= "<tr><td align='center'><img src='data:image/png;base64,$barcode' /></td></tr>";
		$display_table .= "<tr><td align='center'>" . $this->manage_display_layout($barcode_config['barcode_second_row'], $item, $barcode_config) . "</td></tr>";
		$display_table .= "<tr><td align='center'>" . $this->manage_display_layout($barcode_config['barcode_third_row'], $item, $barcode_config) . "</td></tr>";
		$display_table .= "</table>";
		
		return $display_table;
	}
	
	private function manage_display_layout($layout_type, $item, $barcode_config)
	{
		$result = '';
		
		if($layout_type == 'name')
		{
			$result = $this->CI->lang->line('items_name') . " " . $item['name'];
		}
		else if($layout_type == 'category' && isset($item['category']))
		{
			$result = $this->CI->lang->line('items_category') . " " . $item['category'];
		}
		else if($layout_type == 'cost_price' && isset($item['cost_price']))
		{
			$result = $this->CI->lang->line('items_cost_price') . " " . to_currency($item['cost_price']);
		}
		else if($layout_type == 'unit_price' && isset($item['unit_price']))
		{
			$result = $this->CI->lang->line('items_unit_price') . " " . to_currency($item['unit_price']);
		}
		else if($layout_type == 'company_name')
		{
			$result = $barcode_config['company'];
		}
		else if($layout_type == 'item_code')
		{
			$result = $barcode_config['barcode_content'] !== "id" && isset($item['item_number']) ? $item['item_number'] : $item['item_id'];
		}

		return $result;
	}
	
	public function listfonts($folder) 
	{
		$array = array();

		if (($handle = opendir($folder)) !== false)
		{
			while (($file = readdir($handle)) !== false)
			{
				if(substr($file, -4, 4) === '.ttf')
				{
					$array[$file] = $file;
				}
			}
		}

		closedir($handle);

		array_unshift($array, 'No Label');

		return $array;
	}

	public function get_font_name($font_file_name)
	{
		return substr($font_file_name, 0, -4);
	}
}
?>