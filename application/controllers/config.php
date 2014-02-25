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
		$this->load->view("config");
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
		
		if( $this->Appconfig->batch_save( $batch_save_data ) )
		{
			echo json_encode(array('success'=>true,'message'=>$this->lang->line('config_saved_successfully')));
		}
	}
}
?>