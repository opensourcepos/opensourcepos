<?php

use emberlabs\Barcode\BarcodeBase;
require APPPATH.'/views/barcodes/BarcodeBase.php';
require APPPATH.'/views/barcodes/Code39.php';
require APPPATH.'/views/barcodes/Code128.php';

class Barcode_lib
{
    var $CI;
    var $supported_barcodes = array(1 => 'Code 39', 2 => 'Code 128');
    
    function __construct()
    {
        $this->CI =& get_instance();
    }
    
    function get_list_barcodes()
    {
        return $this->supported_barcodes;
    }
    
    function get_barcode_config()
    {
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
    
    function generate_barcode($barcode_content, $barcode_config)
    {
    	try
    	{
	    	if ($barcode_config['barcode_type'] == '1')
	    	{
	    		$barcode = new emberlabs\Barcode\Code39();
	    	}
	    	else
	    	{
	    		$barcode = new emberlabs\Barcode\Code128();
	    	}
    		$barcode->setData($barcode_content);
    		$barcode->setQuality($barcode_config['barcode_quality']);
    		$barcode->setDimensions($barcode_config['barcode_width'], $barcode_config['barcode_height']);
    		$barcode->draw();
    		return $barcode->base64();
    		return "";
    	} 
    	catch(Exception $e)
    	{
    		echo 'Caught exception: ',  $e->getMessage(), "\n";    	
    	}
    }
    
    function create_display_barcode($item, $barcode_config)
    {
    	
        $display_table = "<table>";
        $display_table .= "<tr><td align='center'>". $this->manage_display_layout($barcode_config['barcode_first_row'], $item, $barcode_config)."</td></tr>";
        $barcode_content=$this->CI->Appconfig->get('barcode_content') === "id" ? $item['item_id'] : $item['item_number'];
        $barcode = $this->generate_barcode($barcode_content,$barcode_config);
        $display_table .= "<tr><td align='center'><img src='data:image/png;base64,$barcode' /></td></tr>";
        $display_table .= "<tr><td align='center'>". $this->manage_display_layout($barcode_config['barcode_second_row'], $item, $barcode_config)."</td></tr>";
        $display_table .= "<tr><td align='center'>". $this->manage_display_layout($barcode_config['barcode_third_row'], $item, $barcode_config)."</td></tr>";
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
        	$result = $this->CI->Appconfig->get('company');
        }
        else if($layout_type == 'item_code')
        {
        	$result = $this->CI->Appconfig->get('barcode_content') !== "id" && isset($item['item_number']) ? $item['item_number'] : $item['item_id'];
        }
        return $result;
    }
    
    function listfonts($folder) 
    {
    	$array = array();
    	if (($handle = opendir($folder)) !== false) {
    		while (($file = readdir($handle)) !== false) {
    			if(substr($file, -4, 4) === '.ttf') {
    				$array[$file] = $file;
    			}
    		}
    	}
    	closedir($handle);
    
    	array_unshift($array, 'No Label');
    
    	return $array;
    }

    function get_font_name($font_file_name)
    {
        return substr($font_file_name, 0, -4);
    }
}
?>