<?php
require_once ("Secure_area.php");
class Config extends Secure_area 
{
	function __construct()
	{
		parent::__construct('config');
		$this->load->library('barcode_lib');
	}
	
	function index()
	{
		$location_names = array();
		$data['stock_locations'] = $this->Stock_location->get_all()->result_array();
		$data['support_barcode'] = $this->barcode_lib->get_list_barcodes();
		$data['logo_exists'] = $this->Appconfig->get('company_logo') != '';

		$this->load->view("configs/manage", $data);
	}
		
	function save_info()
	{
		$upload_success = $this->_handle_logo_upload();
		$upload_data = $this->upload->data();
		
		$batch_save_data = array(
			'company'=>$this->input->post('company'),
			'address'=>$this->input->post('address'),
			'phone'=>$this->input->post('phone'),
			'email'=>$this->input->post('email'),
			'fax'=>$this->input->post('fax'),
			'website'=>$this->input->post('website'),	
			'return_policy'=>$this->input->post('return_policy')
		);
		
		if (!empty($upload_data['orig_name']))
		{
			$batch_save_data['company_logo'] = $upload_data['raw_name'] . $upload_data['file_ext'];
		}
		
		$result = $this->Appconfig->batch_save($batch_save_data);
		$success = $upload_success && $result ? true : false;
		$message = $this->lang->line('config_saved_' . ($success ? '' : 'un') . 'successfully');
		$message = $upload_success ? $message : $this->upload->display_errors();

		echo json_encode(array('success'=>$success, 'message'=>$message));
	}
		
	function save_general()
	{
		$batch_save_data = array(
			'default_tax_1_rate'=>$this->input->post('default_tax_1_rate'),	
			'default_tax_1_name'=>$this->input->post('default_tax_1_name'),	
			'default_tax_2_rate'=>$this->input->post('default_tax_2_rate'),	
			'default_tax_2_name'=>$this->input->post('default_tax_2_name'),
			'tax_included'=>$this->input->post('tax_included') != null,
			'receiving_calculate_average_price'=>$this->input->post('receiving_calculate_average_price') != null,
			'lines_per_page'=>$this->input->post('lines_per_page'),
			'default_sales_discount'=>$this->input->post('default_sales_discount'),
			'custom1_name'=>$this->input->post('custom1_name'),
			'custom2_name'=>$this->input->post('custom2_name'),
			'custom3_name'=>$this->input->post('custom3_name'),
			'custom4_name'=>$this->input->post('custom4_name'),
			'custom5_name'=>$this->input->post('custom5_name'),
			'custom6_name'=>$this->input->post('custom6_name'),
			'custom7_name'=>$this->input->post('custom7_name'),
			'custom8_name'=>$this->input->post('custom8_name'),
			'custom9_name'=>$this->input->post('custom9_name'),
			'custom10_name'=>$this->input->post('custom10_name')
		);
		
		$result = $this->Appconfig->batch_save($batch_save_data);
		$success = $result ? true : false;

		echo json_encode(array('success'=>$success, 'message'=>$this->lang->line('config_saved_' . ($success ? '' : 'un') . 'successfully')));
	}

	function save_locale()
	{
		$batch_save_data = array(	
			'currency_symbol'=>$this->input->post('currency_symbol'),
			'currency_side'=>$this->input->post('currency_side') != null,
			'language'=>$this->input->post('language'),
			'timezone'=>$this->input->post('timezone'),
			'dateformat'=>$this->input->post('dateformat'),
			'timeformat'=>$this->input->post('timeformat'),
			'thousands_separator'=>$this->input->post('thousands_separator'),
			'decimal_point'=>$this->input->post('decimal_point'),
			'currency_decimals'=>$this->input->post('currency_decimals'),
			'tax_decimals'=>$this->input->post('tax_decimals'),
			'quantity_decimals'=>$this->input->post('quantity_decimals')
		);
	
		$result = $this->Appconfig->batch_save($batch_save_data);
		$success = $result ? true : false;

		echo json_encode(array('success'=>$success, 'message'=>$this->lang->line('config_saved_' . ($success ? '' : 'un') . 'successfully')));
	}
	
	function save_message()
	{
		$batch_save_data = array(	
			'msg_msg'=>$this->input->post('msg_msg'),
			'msg_uid'=>$this->input->post('msg_uid'),
			'msg_pwd'=>$this->input->post('msg_pwd'),
			'msg_src'=>$this->input->post('msg_src')
		);
	
		$result = $this->Appconfig->batch_save($batch_save_data);
		$success = $result ? true : false;

		echo json_encode(array('success'=>$success, 'message'=>$this->lang->line('config_saved_' . ($success ? '' : 'un') . 'successfully')));
	}
	
	function stock_locations() 
	{
		$stock_locations = $this->Stock_location->get_all()->result_array();

		$this->load->view('partial/stock_locations', array('stock_locations'=>$stock_locations));
	} 
	
	function _clear_session_state()
	{
		$this->load->library('sale_lib');
		$this->sale_lib->clear_sale_location();
		$this->sale_lib->clear_all();
		$this->load->library('receiving_lib');
		$this->receiving_lib->clear_stock_source();
		$this->receiving_lib->clear_stock_destination();
		$this->receiving_lib->clear_all();
	}
	
	function save_locations() 
	{
		$this->db->trans_start();
		
		$deleted_locations = $this->Stock_location->get_allowed_locations();
		foreach($this->input->post() as $key => $value)
		{
			if (strstr($key, 'stock_location'))
			{
				$location_id = preg_replace("/.*?_(\d+)$/", "$1", $key);
				unset($deleted_locations[$location_id]);
				// save or update
				$location_data = array('location_name'=>$value);
				if ($this->Stock_location->save($location_data, $location_id))
				{
					$this->_clear_session_state();
				}
			}
		}

		// all locations not available in post will be deleted now
		foreach ($deleted_locations as $location_id => $location_name)
		{
			$this->Stock_location->delete($location_id);
		}

		$success = $this->db->trans_complete();
		
		echo json_encode(array('success'=>$success, 'message'=>$this->lang->line('config_saved_' . ($success ? '' : 'un') . 'successfully')));
	}

    function save_barcode()
    {
        $batch_save_data = array(
			'barcode_type'=>$this->input->post('barcode_type'),
			'barcode_quality'=>$this->input->post('barcode_quality'),
			'barcode_width'=>$this->input->post('barcode_width'),
			'barcode_height'=>$this->input->post('barcode_height'),
			'barcode_font'=>$this->input->post('barcode_font'),
			'barcode_font_size'=>$this->input->post('barcode_font_size'),
			'barcode_first_row'=>$this->input->post('barcode_first_row'),
			'barcode_second_row'=>$this->input->post('barcode_second_row'),
			'barcode_third_row'=>$this->input->post('barcode_third_row'),
			'barcode_num_in_row'=>$this->input->post('barcode_num_in_row'),
			'barcode_page_width'=>$this->input->post('barcode_page_width'),
			'barcode_page_cellspacing'=>$this->input->post('barcode_page_cellspacing'),
			'barcode_generate_if_empty'=>$this->input->post('barcode_generate_if_empty') != null,
			'barcode_content'=>$this->input->post('barcode_content')
        );
        
        $result = $this->Appconfig->batch_save($batch_save_data);
        $success = $result ? true : false;
		
        echo json_encode(array('success'=>$success, 'message'=>$this->lang->line('config_saved_' . ($success ? '' : 'un') . 'successfully')));
    }
    
    function save_receipt()
    {
    	$batch_save_data = array (
			'receipt_show_taxes'=>$this->input->post('receipt_show_taxes') != null,
			'receipt_show_total_discount'=>$this->input->post('receipt_show_total_discount') != null,
			'receipt_show_description'=>$this->input->post('receipt_show_description') != null,
			'receipt_show_serialnumber'=>$this->input->post('receipt_show_serialnumber') != null,
			'print_silently'=>$this->input->post('print_silently') != null,
			'print_header'=>$this->input->post('print_header') != null,
			'print_footer'=>$this->input->post('print_footer') != null,
			'print_top_margin'=>$this->input->post('print_top_margin'),
			'print_left_margin'=>$this->input->post('print_left_margin'),
			'print_bottom_margin'=>$this->input->post('print_bottom_margin'),
			'print_right_margin'=>$this->input->post('print_right_margin')
		);

    	$result = $this->Appconfig->batch_save($batch_save_data);
    	$success = $result ? true : false;

    	echo json_encode(array('success'=>$success, 'message'=>$this->lang->line('config_saved_' . ($success ? '' : 'un') . 'successfully')));
    }

    function save_invoice()
    {
    	$batch_save_data = array (
			'invoice_enable'=>$this->input->post('invoice_enable') != null,
			'sales_invoice_format'=>$this->input->post('sales_invoice_format'),
			'recv_invoice_format'=>$this->input->post('recv_invoice_format'),
			'use_invoice_template'=>$this->input->post('use_invoice_template') != null,
			'invoice_default_comments'=>$this->input->post('invoice_default_comments'),
			'invoice_email_message'=>$this->input->post('invoice_email_message')
		);

    	$result = $this->Appconfig->batch_save($batch_save_data);
    	$success = $result ? true : false;

    	echo json_encode(array('success'=>$success, 'message'=>$this->lang->line('config_saved_' . ($success ? '' : 'un') . 'successfully')));
    }

	public function remove_logo()
	{
		$result = $this->Appconfig->batch_save(array('company_logo' => ''));
		
		echo json_encode(array('success'=>$result));
	}
    
    private function _handle_logo_upload()
    {
    	$this->load->helper('directory');

    	// load upload library
    	$config = array('upload_path' => './uploads/',
    			'allowed_types' => 'gif|jpg|png',
    			'max_size' => '1024',
    			'max_width' => '800',
    			'max_height' => '680',
    			'file_name' => 'company_logo');
    	$this->load->library('upload', $config);
    	$this->upload->do_upload('company_logo');

    	return strlen($this->upload->display_errors()) == 0 || !strcmp($this->upload->display_errors(), '<p>'.$this->lang->line('upload_no_file_selected').'</p>');
    }
    
    function backup_db()
    {
    	$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
    	if($this->Employee->has_module_grant('config',$employee_id))
    	{
    		$this->load->dbutil();
    		$prefs = array(
				'format' => 'zip',
				'filename' => 'ospos.sql'
    		);
    		 
    		$backup =& $this->dbutil->backup($prefs);
    		 
			$file_name = 'ospos-' . date("Y-m-d-H-i-s") .'.zip';
    		$save = 'uploads/'.$file_name;
    		$this->load->helper('download');
    		while (ob_get_level())
			{
    			ob_end_clean();
    		}
    		force_download($file_name, $backup);
    	}
    	else 
    	{
    		redirect('no_access/config');
    	}
    }
}
?>
