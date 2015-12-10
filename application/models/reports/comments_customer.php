<?php
require_once("report.php");
class Comments_customer extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		return array($this->lang->line('reports_first_name'),
					$this->lang->line('reports_last_name'),
					$this->lang->line('reports_phone_number'),					
					$this->lang->line('reports_email'),
					$this->lang->line('reports_comments'));
	}
	
	public function getData(array $inputs)
	{
	    $this->db->from('customers');
        $this->db->join('people','customers.person_id=people.person_id');
		$this->db->select('first_name, last_name, phone_number, email, comments');
		$this->db->where('comments <>', '');
		$this->db->order_by('first_name');	
		$this->db->order_by('last_name');

		return $this->db->get()->result_array();
	}
	
	/**
	 * calulcates the total value of the given inventory summary by summing all sub_total_values (see Inventory_summary::getData())
	 * 
	 * @param array $inputs expects the reports-data-array which Inventory_summary::getData() returns
	 * @return array
	 */
	public function getSummaryData(array $inputs)
	{
		$return = array('total_customers_wanting_stuff' => 0);
		foreach($inputs as $input)
		{
			$return['total_customers_wanting_stuff'] += 1;
		}
		return $return;
	}
	
}
?>