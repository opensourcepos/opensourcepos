<?php
class Barcode_lib
{
    var $CI;
    var $supported_barcodes = array(1 => 'Code 39');
    
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
    
    function create_display_barcode($item, $barcode_config)
    {
        $display_table = "<table>";
        $display_table .= "<tr><td align='center'>". $this->manage_display_layout($barcode_config['barcode_first_row'], $item, $barcode_config)."</td></tr>";
        $display_table .= "<tr><td align='center'>". $this->manage_display_layout($barcode_config['barcode_second_row'], $item, $barcode_config)."</td></tr>";
        $display_table .= "<tr><td align='center'>". $this->manage_display_layout($barcode_config['barcode_third_row'], $item, $barcode_config)."</td></tr>";
        $display_table .= "</table>";
        return $display_table;
    }
    
    private function manage_display_layout($layout_type, $item, $barcode_config)
    {
        $result = '';
        
        if($layout_type == 'item_code')
        {
            $result = "<img src='".site_url()."/barcode?".
                                   "&width=".$barcode_config['barcode_width'].
                                   "&height=".$barcode_config['barcode_height'].
                                   "&barcode=".($this->CI->Appconfig->get('barcode_content') === "id" ? $item['item_id'] : $item['item_number']).
                                   "&quality=".$barcode_config['barcode_quality'].
                                   "&type=".$this->CI->Appconfig->get('barcode_type').
                                   "' onerror=\"(function(pThis){pThis.onerror = null;  pThis.src = pThis.src;})(this)\" />";
        }
        else if($layout_type == 'name')
        {
            $result = $this->CI->lang->line('items_name') . " " . $item['name'];
        }
        else if($layout_type == 'category')
        {
            $result = $this->CI->lang->line('items_category') . " " . $item['category'];
        }
        else if($layout_type == 'cost_price')
        {
            $result = $this->CI->lang->line('items_cost_price') . " " . to_currency($item['cost_price']);
        }
        else if($layout_type == 'unit_price')
        {
            $result = $this->CI->lang->line('items_unit_price') . " " . to_currency($item['unit_price']);
        }
        else if($layout_type == 'company_name')
        {
        	$result = $this->CI->Appconfig->get('company');
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