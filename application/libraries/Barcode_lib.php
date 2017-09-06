<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use emberlabs\Barcode\BarcodeBase;
require APPPATH.'/views/barcodes/BarcodeBase.php';
require APPPATH.'/views/barcodes/Code39.php';
require APPPATH.'/views/barcodes/Code128.php';
require APPPATH.'/views/barcodes/Ean13.php';
require APPPATH.'/views/barcodes/Ean8.php';

/**
 * Barcode library
 *
 * Library with utilities to manage barcodes
 */

class Barcode_lib
{
	private $CI;
	private $supported_barcodes = array('Code39' => 'Code 39', 'Code128' => 'Code 128', 'Ean8' => 'EAN 8', 'Ean13' => 'EAN 13');

	public function __construct()
	{
		$this->CI =& get_instance();
	}

	public function get_list_barcodes()
	{
		return $this->supported_barcodes;
	}

	public function get_barcode_config()
	{
		$data['company'] = $this->CI->config->item('company');
		$data['barcode_content'] = $this->CI->config->item('barcode_content');
		$data['barcode_type'] = $this->CI->config->item('barcode_type');
		$data['barcode_font'] = $this->CI->config->item('barcode_font');
		$data['barcode_font_size'] = $this->CI->config->item('barcode_font_size');
		$data['barcode_height'] = $this->CI->config->item('barcode_height');
		$data['barcode_width'] = $this->CI->config->item('barcode_width');
		$data['barcode_quality'] = $this->CI->config->item('barcode_quality');
		$data['barcode_first_row'] = $this->CI->config->item('barcode_first_row');
		$data['barcode_second_row'] = $this->CI->config->item('barcode_second_row');
		$data['barcode_third_row'] = $this->CI->config->item('barcode_third_row');
		$data['barcode_num_in_row'] = $this->CI->config->item('barcode_num_in_row');
		$data['barcode_page_width'] = $this->CI->config->item('barcode_page_width');
		$data['barcode_page_cellspacing'] = $this->CI->config->item('barcode_page_cellspacing');
		$data['barcode_generate_if_empty'] = $this->CI->config->item('barcode_generate_if_empty');
		$data['barcode_formats'] = $this->CI->config->item('barcode_formats');

		return $data;
	}

	public function parse_barcode_fields(&$quantity, &$item_id_or_number_or_item_kit_or_receipt)
	{
		$barcode_formats = json_decode($this->CI->config->item('barcode_formats'));

		if(!empty($barcode_formats))
		{
			foreach($barcode_formats as $barcode_format)
			{
				if(preg_match("/$barcode_format/", $item_id_or_number_or_item_kit_or_receipt, $matches) && sizeof($matches) > 1)
				{
					$qtyfirst = strpos('d', $barcode_format) - strpos('w', $barcode_format) < 0;
					$quantity = $matches[$qtyfirst ? 1 : 2];
					if(strstr($barcode_format, '02'))
					{
						$quantity = $quantity / 1000;
					}
					$item_id_or_number_or_item_kit_or_receipt = $matches[$qtyfirst ? 2  : 1];

					return;
				}
			}
		}

		$quantity = 1;
	}

	public function validate_barcode($barcode)
	{
		$barcode_type = $this->CI->config->item('barcode_type');
		$barcode_instance = $this->get_barcode_instance($barcode_type);
		return $barcode_instance->validate($barcode);
	}

	public static function barcode_instance($item, $barcode_config)
	{
		$barcode_instance = Barcode_lib::get_barcode_instance($barcode_config['barcode_type']);
		$is_valid = empty($item['item_number']) && $barcode_config['barcode_generate_if_empty'] || $barcode_instance->validate($item['item_number']);

		// if barcode validation does not succeed,
		if (!$is_valid)
		{
			$barcode_instance = Barcode_lib::get_barcode_instance();
		}
		$seed = Barcode_lib::barcode_seed($item, $barcode_instance, $barcode_config);
		$barcode_instance->setData($seed);

		return $barcode_instance;
	}

	private static function get_barcode_instance($barcode_type='Code128')
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

	private static function barcode_seed($item, $barcode_instance, $barcode_config)
	{
		$seed = $barcode_config['barcode_content'] !== "id" && !empty($item['item_number']) ? $item['item_number'] : $item['item_id'];

		if( $barcode_config['barcode_content'] !== "id" && !empty($item['item_number']))
		{
			$seed = $item['item_number'];
		}
		else
		{
			if ($barcode_config['barcode_generate_if_empty'])
			{
				// generate barcode with the correct instance
				$seed = $barcode_instance->generate($seed);
			}
			else
			{
				$seed = $item['item_id'];
			}
		}
		return $seed;
	}

	private function generate_barcode($item, $barcode_config)
	{
		try
		{
			$barcode_instance = Barcode_lib::barcode_instance($item, $barcode_config);
			$barcode_instance->setQuality($barcode_config['barcode_quality']);
			$barcode_instance->setDimensions($barcode_config['barcode_width'], $barcode_config['barcode_height']);

			$barcode_instance->draw();

			return $barcode_instance->base64();
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
			$barcode->setData($barcode_content);

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

	public function display_barcode($item, $barcode_config)
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
		elseif($layout_type == 'category' && isset($item['category']))
		{
			$result = $this->CI->lang->line('items_category') . " " . $item['category'];
		}
		elseif($layout_type == 'cost_price' && isset($item['cost_price']))
		{
			$result = $this->CI->lang->line('items_cost_price') . " " . to_currency($item['cost_price']);
		}
		elseif($layout_type == 'unit_price' && isset($item['unit_price']))
		{
			$result = $this->CI->lang->line('items_unit_price') . " " . to_currency($item['unit_price']);
		}
		elseif($layout_type == 'company_name')
		{
			$result = $barcode_config['company'];
		}
		elseif($layout_type == 'item_code')
		{
			$result = $barcode_config['barcode_content'] !== "id" && isset($item['item_number']) ? $item['item_number'] : $item['item_id'];
		}

		return character_limiter($result, 40);
	}

	public function listfonts($folder)
	{
		$array = array();

		if (($handle = opendir($folder)) !== FALSE)
		{
			while (($file = readdir($handle)) !== FALSE)
			{
				if(substr($file, -4, 4) === '.ttf')
				{
					$array[$file] = $file;
				}
			}
		}

		closedir($handle);

		array_unshift($array, $this->CI->lang->line('config_none'));

		return $array;
	}

	public function get_font_name($font_file_name)
	{
		return substr($font_file_name, 0, -4);
	}
}

?>
