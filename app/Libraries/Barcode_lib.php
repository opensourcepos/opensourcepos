<?php

namespace App\Libraries;

use Exception;
use Picqer\Barcode\BarcodeGeneratorPNG;
use App\Libraries\Barcodes\Code39;
use App\Libraries\Barcodes\Code128;
use App\Libraries\Barcodes\Ean8;
use App\Libraries\Barcodes\Ean13;

/**
 * Barcode library
 *
 * Library with utilities to manage barcodes
 */

class Barcode_lib
{
	private $supported_barcodes = [
		'Code39' => 'Code 39',
		'Code128' => 'Code 128',
		'Ean8' => 'EAN 8',
		'Ean13' => 'EAN 13'
	];

	public function get_list_barcodes(): array
	{
		return $this->supported_barcodes;
	}

	public function get_barcode_config(): array
	{
		$config = config(OSPOS::class)->settings;

		$data['company'] = $config['company'];
		$data['barcode_content'] = $config['barcode_content'];
		$data['barcode_type'] = $config['barcode_type'];
		$data['barcode_font'] = $config['barcode_font'];
		$data['barcode_font_size'] = $config['barcode_font_size'];
		$data['barcode_height'] = $config['barcode_height'];
		$data['barcode_width'] = $config['barcode_width'];
		$data['barcode_first_row'] = $config['barcode_first_row'];
		$data['barcode_second_row'] = $config['barcode_second_row'];
		$data['barcode_third_row'] = $config['barcode_third_row'];
		$data['barcode_num_in_row'] = $config['barcode_num_in_row'];
		$data['barcode_page_width'] = $config['barcode_page_width'];
		$data['barcode_page_cellspacing'] = $config['barcode_page_cellspacing'];
		$data['barcode_generate_if_empty'] = $config['barcode_generate_if_empty'];
		$data['barcode_formats'] = $config['barcode_formats'];

		return $data;
	}

	public static function barcode_instance(array $item, array $barcode_config): object
	{
		$barcode_instance = Barcode_lib::get_barcode_instance($barcode_config['barcode_type']);
		$is_valid = empty($item['item_number'])
			&& $barcode_config['barcode_generate_if_empty']
			|| $barcode_instance->validate($item['item_number']);

		// if barcode validation does not succeed,
		if(!$is_valid)
		{
			$barcode_instance = Barcode_lib::get_barcode_instance();
		}

		$seed = Barcode_lib::barcode_seed($item, $barcode_instance, $barcode_config);
		$barcode_instance->setData($seed);

		return $barcode_instance;
	}

	private static function get_barcode_instance(string $barcode_type = 'Code128'): object
	{
		switch($barcode_type)
		{
			case 'Code39':
				return new Code39();

			case 'Code128':
			default:
				return new Code128();

			case 'Ean8':
				return new Ean8();

			case 'Ean13':
				return new Ean13();
		}
	}

	private static function barcode_seed(array $item, object $barcode_instance, array $barcode_config)
	{
		$seed = $barcode_config['barcode_content'] !== "id" && !empty($item['item_number'])
			? $item['item_number']
			: $item['item_id'];

		if($barcode_config['barcode_content'] !== "id" && !empty($item['item_number']))	//TODO: === ?
		{
			$seed = $item['item_number'];
		}
		else
		{
			if($barcode_config['barcode_generate_if_empty'])
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

	private function generate_barcode(array $item, array $barcode_config): string
	{
		try
		{
			$generator = new BarcodeGeneratorPNG();
			$barcode_instance = Barcode_lib::barcode_instance($item, $barcode_config);
			$barcode_instance->setDimensions($barcode_config['barcode_width'], $barcode_config['barcode_height']);

			$barcode_instance->draw();

			return $barcode_instance->base64();
		}
		catch(Exception $e)
		{
			echo 'Caught exception: ', $e->getMessage(), "\n";
			return '';
		}
	}

	public function generate_receipt_barcode($barcode_content): string
	{
		try
		{
			$generator = new BarcodeGeneratorPNG();

			//Code128 is the default and used in this case for the receipts
			$barcode = $this->get_barcode_instance();

			// set the receipt number to generate the barcode for
			$barcode->setData($barcode_content);

			// width: 300, height: 50
			$barcode->setDimensions(300, 50);

			// draw the image
			$barcode->draw();

			return $barcode->base64();
		}
		catch(Exception $e)
		{
			echo 'Caught exception: ', $e->getMessage(), "\n";
			return '';
		}
	}

	/**
	 * Displays the barcode.  Called in a View.
	 *
	 * @param array $item
	 * @param array $barcode_config
	 * @return string
	 */
	public function display_barcode(array $item, array $barcode_config): string
	{
		$display_table = '<table>';
		$display_table .= "<tr><td style=\"text-align=center;\">" . $this->manage_display_layout($barcode_config['barcode_first_row'], $item, $barcode_config) . '</td></tr>';
		$barcode = $this->generate_barcode($item, $barcode_config);
		$display_table .= "<tr><td style=\"text-align=center;\"><img src='data:image/png;base64,$barcode' /></td></tr>";
		$display_table .= "<tr><td style=\"text-align=center;\">" . $this->manage_display_layout($barcode_config['barcode_second_row'], $item, $barcode_config) . '</td></tr>';
		$display_table .= "<tr><td style=\"text-align=center;\">" . $this->manage_display_layout($barcode_config['barcode_third_row'], $item, $barcode_config) . '</td></tr>';
		$display_table .= '</table>';

		return $display_table;
	}

	private function manage_display_layout($layout_type, array $item, array $barcode_config): string
	{
		$result = '';

		//TODO: this needs to be converted to a switch statement.
		if($layout_type == 'name')
		{
			$result = lang('Items.name') . " " . $item['name'];
		}
		elseif($layout_type == 'category' && isset($item['category']))
		{
			$result = lang('Items.category') . " " . $item['category'];
		}
		elseif($layout_type == 'cost_price' && isset($item['cost_price']))
		{
			$result = lang('Items.cost_price') . " " . to_currency($item['cost_price']);
		}
		elseif($layout_type == 'unit_price' && isset($item['unit_price']))
		{
			$result = lang('Items.unit_price') . " " . to_currency($item['unit_price']);
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

	/**
	 * Finds all acceptable fonts to be used. Called in a View.
	 *
	 * @param string $folder
	 * @return array
	 */
	public function listfonts(string $folder): array	//TODO: This function does not follow naming conventions.
	{
		$array = [];	//TODO: Naming of this variable should be changed.  The variable should never be named the data type.  $fonts would be a better name.

		if(($handle = opendir($folder)) !== FALSE)
		{
			while(($file = readdir($handle)) !== FALSE)
			{
				if(substr($file, -4, 4) === '.ttf')
				{
					$array[$file] = $file;
				}
			}
		}

		closedir($handle);

		array_unshift($array, lang('Config.none'));

		return $array;
	}

	/**
	 * Returns the name of the font from the file name.  Called in a View.
	 *
	 * @param string $font_file_name
	 * @return string
	 */
	public function get_font_name(string $font_file_name): string
	{
		return substr($font_file_name, 0, -4);
	}
}
