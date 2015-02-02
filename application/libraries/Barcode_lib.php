<?php
class Barcode_lib
{
    var $CI;
    var $supported_barcodes = array(
    // 1D
    'BCGcodabar' => 'Codabar',
    'BCGcode11' => 'Code 11',
    'BCGcode39' => 'Code 39',
    'BCGcode39extended' => 'Code 39 Extended',
    'BCGcode93' => 'Code 93',
    'BCGcode128' => 'Code 128',
    'BCGean8' => 'EAN-8',
    'BCGean13' => 'EAN-13',
    'BCGgs1128' => 'GS1-128 (EAN-128)',
    'BCGisbn' => 'ISBN',
    'BCGi25' => 'Interleaved 2 of 5',
    'BCGs25' => 'Standard 2 of 5',
    'BCGmsi' => 'MSI Plessey',
    'BCGupca' => 'UPC-A',
    'BCGupce' => 'UPC-E',
    'BCGupcext2' => 'UPC Extenstion 2 Digits',
    'BCGupcext5' => 'UPC Extenstion 5 Digits',
    'BCGpostnet' => 'Postnet',
    'BCGintelligentmail' => 'Intelligent Mail',
    'BCGothercode' => 'Other Barcode');
    
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
        $data['barcode_dpi'] = $this->CI->Appconfig->get('barcode_dpi');
        $data['barcode_scale'] = $this->CI->Appconfig->get('barcode_scale');
        $data['barcode_rotation'] = $this->CI->Appconfig->get('barcode_rotation');
        $data['barcode_font'] = $this->CI->Appconfig->get('barcode_font');
        $data['barcode_font_size'] = $this->CI->Appconfig->get('barcode_font_size');
        $data['barcode_thickness'] = $this->CI->Appconfig->get('barcode_thickness');
        $data['barcode_checksum'] = $this->CI->Appconfig->get('barcode_checksum');
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
        $display_table .= "<tr><td>". $this->manage_display_layout($barcode_config['barcode_first_row'], $item, $barcode_config)."</td></tr>";
        $display_table .= "<tr><td>". $this->manage_display_layout($barcode_config['barcode_second_row'], $item, $barcode_config)."</td></tr>";
        $display_table .= "<tr><td>". $this->manage_display_layout($barcode_config['barcode_third_row'], $item, $barcode_config)."</td></tr>";
        $display_table .= "</table>";
        return $display_table;
    }
    
    private function manage_display_layout($layout_type, $item, $barcode_config)
    {
        $result = '';
        
        if($layout_type == 'item_code')
        {
            $result = "<img src='".site_url()."/barcode?filetype=PNG&dpi=".$barcode_config['barcode_dpi'].
                                   "&scale=".$barcode_config['barcode_scale'].
                                   "&rotation=".$barcode_config['barcode_rotation'].
                                   "&font_family=".$barcode_config['barcode_font'].
                                   "&font_size=".$barcode_config['barcode_font_size'].
                                   "&text=".($this->CI->Appconfig->get('barcode_content') === "id" ? $item['item_id'] : $item['item_number']).
                                   "&thickness=".$barcode_config['barcode_thickness'].
                                   "&checksum=".$barcode_config['barcode_checksum'].
                                   "&code=".$this->CI->Appconfig->get('barcode_type').
                                   "' onerror=\"(function(pThis){pThis.onerror = null;  pThis.src = pThis.src;})(this)\" />";
        }
        else if($layout_type == 'name'){
            $result = $this->CI->lang->line('items_name') . " " . $item['name'];
        }
        else if($layout_type == 'category'){
            $result = $this->CI->lang->line('items_category') . " " . $item['category'];
        }
        else if($layout_type == 'cost_price'){
            $result = $this->CI->lang->line('items_cost_price') . " " . to_currency($item['cost_price']);
        }
        else if($layout_type == 'unit_price'){
            $result = $this->CI->lang->line('items_unit_price') . " " . to_currency($item['unit_price']);
        }
        return $result;
    }

    function get_font_name($font_file_name)
    {
        return substr($font_file_name, 0, -4);
    }
}
?>