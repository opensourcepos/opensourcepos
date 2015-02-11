<?php
require_once ("secure_area.php");
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
		$data['stock_locations'] = $this->Stock_locations->get_all()->result_array();
		$data['support_barcode'] = $this->barcode_lib->get_list_barcodes();
		$this->load->view("configs/manage", $data);
	}
		
	function save()
	{
		$batch_save_data=array(
		'company'=>$this->input->post('company'),
		'address'=>$this->input->post('address'),
		'phone'=>$this->input->post('phone'),
		'email'=>$this->input->post('email'),
		'fax'=>$this->input->post('fax'),
		'website'=>$this->input->post('website'),
		'default_tax_1_rate'=>$this->input->post('default_tax_1_rate'),		
		'default_tax_1_name'=>$this->input->post('default_tax_1_name'),		
		'default_tax_2_rate'=>$this->input->post('default_tax_2_rate'),	
		'default_tax_2_name'=>$this->input->post('default_tax_2_name'),		
		'currency_symbol'=>$this->input->post('currency_symbol'),
		'currency_side'=>$this->input->post('currency_side'),/**GARRISON ADDED 4/20/2013**/
		'return_policy'=>$this->input->post('return_policy'),
		'language'=>$this->input->post('language'),
		'timezone'=>$this->input->post('timezone'),
		'print_after_sale'=>$this->input->post('print_after_sale'),
        'tax_included'=>$this->input->post('tax_included'),
		'recv_invoice_format'=>$this->input->post('recv_invoice_format'),
		'sales_invoice_format'=>$this->input->post('sales_invoice_format'),
		'custom1_name'=>$this->input->post('custom1_name'),/**GARRISON ADDED 4/20/2013**/
		'custom2_name'=>$this->input->post('custom2_name'),/**GARRISON ADDED 4/20/2013**/
		'custom3_name'=>$this->input->post('custom3_name'),/**GARRISON ADDED 4/20/2013**/
		'custom4_name'=>$this->input->post('custom4_name'),/**GARRISON ADDED 4/20/2013**/
		'custom5_name'=>$this->input->post('custom5_name'),/**GARRISON ADDED 4/20/2013**/
		'custom6_name'=>$this->input->post('custom6_name'),/**GARRISON ADDED 4/20/2013**/
		'custom7_name'=>$this->input->post('custom7_name'),/**GARRISON ADDED 4/20/2013**/
		'custom8_name'=>$this->input->post('custom8_name'),/**GARRISON ADDED 4/20/2013**/
		'custom9_name'=>$this->input->post('custom9_name'),/**GARRISON ADDED 4/20/2013**/
		'custom10_name'=>$this->input->post('custom10_name')/**GARRISON ADDED 4/20/2013**/
		);
		
		$result = $this->Appconfig->batch_save( $batch_save_data );
		$success = $result ? true : false;
		echo json_encode(array('success'=>$success,'message'=>$this->lang->line('config_saved_' . ($success ? '' : 'un') . 'successfully')));
		$this->_remove_duplicate_cookies();	
	}
	
	function stock_locations() 
	{
		$stock_locations = $this->Stock_locations->get_all()->result_array();
		$this->load->view('partial/stock_locations', array('stock_locations' => $stock_locations));
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
		
		$deleted_locations = $this->Stock_locations->get_allowed_locations();
		foreach($this->input->post() as $key => $value)
		{
			if (strstr($key, 'stock_location'))
			{
				$location_id = preg_replace("/.*?_(\d+)$/", "$1", $key);
				unset($deleted_locations[$location_id]);
				// save or update
				$location_data = array('location_name' => $value);
				if ($this->Stock_locations->save($location_data, $location_id))
				{
					$this->_clear_session_state();
				}
			}
		}
		// all locations not available in post will be deleted now
		foreach ($deleted_locations as $location_id => $location_name)
		{
			$this->Stock_locations->delete($location_id);
		}
		$success = $this->db->trans_complete();
		echo json_encode(array('success'=>$success,'message'=>$this->lang->line('config_saved_' . ($success ? '' : 'un') . 'successfully')));
		$this->_remove_duplicate_cookies();
	}

    function save_barcode()
    {
        $batch_save_data=array(
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
        'barcode_content'=>$this->input->post('barcode_content'),
        );
        
        $result = $this->Appconfig->batch_save( $batch_save_data );
        $success = $result ? true : false;
        echo json_encode(array('success'=>$success, 'message'=>$this->lang->line('config_saved_' . ($success ? '' : 'un') . 'successfully')));
        
    }
    
    function backup_db()
    {
    	$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
    	if($this->Employee->has_module_grant('config',$employee_id))
    	{
    		$this->load->dbutil();
    		$prefs = array(
    				'format'      => 'zip',
    				'filename'    => 'ospos.sql'
    		);
    		 
    		$backup =& $this->dbutil->backup($prefs);
    		 
			$file_name =  'ospos-' . date("Y-m-d-H-i-s") .'.zip';
    		$save = 'uploads/'.$file_name;
    		$this->load->helper('download');
    		while (ob_get_level()) {
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