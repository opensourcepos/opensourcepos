<?php

namespace App\Libraries;

use Config\OSPOS;
use Exception;
use Picqer\Barcode\BarcodeGeneratorSVG;

/**
 * Barcode library
 *
 * Library with utilities to manage barcodes
 */
class Barcode_lib
{
	/**
	 * @var array Values from Picqer\Barcode\BarcodeGenerator class. If that class changes, this array will need to be updated.
	 */
	private array $supported_barcodes = [
		'C32' => 'Code 32',
		'C39' => 'Code 39',
		'C39+' => 'Code 39 Checksum',
		'C39E' => 'Code 39E',
		'C39E+' => 'Code 39E Checksum',
		'C93' => 'Code 93',
		'S25' => 'Standard 2 5',
		'S25+' => 'Standard 2 5 Checksum',
		'I25' => 'Interleaved 2 5',
		'I25+' => 'Interleaved 2 5 Checksum',
		'C128' => 'Code 128',
		'C128A' => 'Code 128 A',
		'C128B' => 'Code 128 B',
		'C128C' => 'Code 128 C',
		'EAN2' => 'EAN 2',
		'EAN5' => 'EAN 5',
		'EAN8' => 'EAN 8',
		'EAN13' => 'EAN 13',
		'ITF14' => 'ITF14',
		'UPCA' => 'UPC A',
		'UPCE' => 'UPC E',
		'MSI' => 'Msi',
		'MSI+' => 'MSI Checksum',
		'POSTNET' => 'Postnet',
		'PLANET' => 'Planet',
		'RMS4CC' => 'RMS4CC',
		'KIX' => 'KIX',
		'IMB' => 'IMB',
		'CODABAR' => 'Codabar',
		'CODE11' => 'Code 11',
		'PHARMA' => 'Pharma Code',
		'PHARMA2T' => 'Pharma Code Two Tracks',
	];

	/**
	 * @return array
	 */
	public function get_list_barcodes(): array
	{
		return $this->supported_barcodes;
	}

	/**
	 * @return array
	 */
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
		$data['barcode_formats'] = $config['barcode_formats'] !== 'null'? $config['barcode_formats'] : [];

		return $data;
	}

	/**
	 * Returns the value to be used in the barcode.
	 *
	 * @param array $item Contains item data
	 * @param array $barcode_config Contains barcode configuration
	 * @return string Barcode value
	 */
	private function get_barcode_value(array $item, array $barcode_config): string
	{
		return $barcode_config['barcode_content'] !== 'id'
			? ($item['item_number'] ?? $item['name'])
			: $item['item_id'];
	}

	/**
	 * @param array $item
	 * @param array $barcode_config
	 * @return string
	 */
	private function generate_barcode(array $item, array $barcode_config): string
	{
		try
		{
			$generator = new BarcodeGeneratorSVG();
			$barcode_value = $this->get_barcode_value($item, $barcode_config);

			return $generator->getBarcode($barcode_value, $barcode_config['barcode_type'], 2, $barcode_config['barcode_height']);
		}
		catch(Exception $e)
		{
			echo 'Caught exception: ', $e->getMessage(), "\n";
			echo 'Stack trace: ', $e->getTraceAsString();

			return '';
		}
	}

	/**
	 * @param $barcode_content
	 * @return string
	 */
	public function generate_receipt_barcode($barcode_content): string
	{
		try
		{
			$generator = new BarcodeGeneratorSVG();
			return $generator->getBarcode($barcode_content, $generator::TYPE_CODE_128);
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
		if((isset($item['item_number']) || isset($item['name'])) && isset($item['item_id']))
		{
			$display_table = '<table>';
			$display_table .= '<tr><td style="text-align:center;">' . $this->manage_display_layout($barcode_config['barcode_first_row'], $item, $barcode_config) . '</td></tr>';
			$barcode = $this->generate_barcode($item, $barcode_config);
			$display_table .= '<tr><td style="text-align:center;"><div style=\'height:' . $barcode_config['barcode_height'] . 'px; width:'. $barcode_config['barcode_width'] . "px'>$barcode</div></td></tr>";
			$display_table .= '<tr><td style="text-align:center;">' . $this->manage_display_layout($barcode_config['barcode_second_row'], $item, $barcode_config) . '</td></tr>';
			$display_table .= '<tr><td style="text-align:center;">' . $this->manage_display_layout($barcode_config['barcode_third_row'], $item, $barcode_config) . '</td></tr>';
			$display_table .= '</table>';

			return $display_table;
		}

		return "Item number or Item ID not found in the item array.";	//TODO: this needs to be run through the translation engine.
	}

	/**
	 * @param $layout_type
	 * @param array $item
	 * @param array $barcode_config
	 * @return string
	 */
	private function manage_display_layout($layout_type, array $item, array $barcode_config): string
	{
		$result = '';
		helper('text');

		if($layout_type == 'name')
		{
			$result = $item['name'];
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
			$result = $barcode_config['barcode_content'] !== "id" && isset($item['item_number'])
				? $item['item_number']
				: $item['item_id'];
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

		if(($handle = opendir($folder)) !== false)
		{
			while(($file = readdir($handle)) !== false)
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
