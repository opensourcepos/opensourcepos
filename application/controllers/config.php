<?php
require_once ("secure_area.php");
class Config extends Secure_area 
{
	function __construct()
	{
		parent::__construct('config');
	}
	
	function index()
	{
		$location_names = array();
		$locations = $this->Stock_locations->get_location_names();
		foreach($locations->result_array() as $array) 
		{
			array_push($location_names, $array['location_name']);
		}
		$data['location_names'] = implode(',', $location_names);
		$this->load->view("config", $data);
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
		
		$stock_locations = explode( ',', $this->input->post('stock_location'));
        $stock_locations_trimmed=array();
        foreach($stock_locations as $location)
        {
            array_push($stock_locations_trimmed, trim($location, ' '));
        }        
        $current_locations = $this->Stock_locations->concat_location_names()->location_names;
        if ($this->input->post('stock_locations') != $current_locations) 
        {
        	$this->load->library('sale_lib');
			$this->sale_lib->clear_sale_location();
			$this->sale_lib->clear_all();
			$this->load->library('receiving_lib');
			$this->receiving_lib->clear_stock_source();
			$this->receiving_lib->clear_stock_destination();
			$this->receiving_lib->clear_all();
        }
        
		if( $this->Appconfig->batch_save( $batch_save_data ) && $this->Stock_locations->array_save($stock_locations_trimmed))
		{
			echo json_encode(array('success'=>true,'message'=>$this->lang->line('config_saved_successfully')));
		}
	}
}
?>