<?php
require_once("Report.php");
class Detailed_receivings extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		$columns = array(
			'summary' => array(
				'id' => $this->lang->line('reports_receiving_id'),
				'receiving_date' => $this->lang->line('reports_date'),
				'quantity' => $this->lang->line('reports_quantity'),
				'employee' => $this->lang->line('reports_received_by'),
				'supplier' => $this->lang->line('reports_supplied_by'),
				'total' => $this->lang->line('reports_total'),
				'payment_type' => $this->lang->line('reports_payment_type'),
				'invoice_number' => $this->lang->line('recvs_invoice_number'),
				'comment' => $this->lang->line('reports_comments'),
				'edit' => ''),
			'details' => array(
				$this->lang->line('reports_item_number'),
				$this->lang->line('reports_name'),
				$this->lang->line('reports_category'),
				$this->lang->line('reports_quantity'),
				$this->lang->line('reports_total'),
				$this->lang->line('reports_discount'))
		);

		if (!get_instance()->config->item('invoice_enable'))
		{
			unset($columns['summary']['invoice_number']);
		}
		return $columns;
	}
	
	public function getDataByReceivingId($receiving_id)
	{
		$this->db->select('receiving_id, DATE_FORMAT(receiving_date, "%d-%m-%Y") AS receiving_date, sum(quantity_purchased) as items_purchased, CONCAT(employee.first_name, " ", employee.last_name) as employee_name, suppliers.company_name as supplier_name, sum(subtotal) as subtotal, sum(total) as total, sum(profit) as profit, payment_type, comment, invoice_number', false);
		$this->db->from('receivings_items_temp');
		$this->db->join('people as employee', 'receivings_items_temp.employee_id = employee.person_id');
		$this->db->join('suppliers as suppliers', 'receivings_items_temp.supplier_id = suppliers.person_id', 'left');
		$this->db->where('receiving_id', $receiving_id);

		return $this->db->get()->row_array();
	}
	
	public function getData(array $inputs)
	{
		$this->db->select('receiving_id, receiving_date, sum(quantity_purchased) as items_purchased, CONCAT(employee.first_name," ",employee.last_name) as employee_name, CONCAT(supplier.first_name," ",supplier.last_name) as supplier_name, sum(total) as total, sum(profit) as profit, payment_type, comment, invoice_number', false);
		$this->db->from('receivings_items_temp');
		$this->db->join('people as employee', 'receivings_items_temp.employee_id = employee.person_id');
		$this->db->join('people as supplier', 'receivings_items_temp.supplier_id = supplier.person_id', 'left');
		$this->db->where('receiving_date BETWEEN '. $this->db->escape($inputs['start_date']). ' and '. $this->db->escape($inputs['end_date']));
		if ($inputs['location_id'] != 'all')
		{
			$this->db->where('item_location', $inputs['location_id']);
		}
		if ($inputs['receiving_type'] == 'receiving')
		{
			$this->db->where('quantity_purchased > 0');
		}
		elseif ($inputs['receiving_type'] == 'returns')
		{
			$this->db->where('quantity_purchased < 0');
		}
		elseif ($inputs['receiving_type'] == 'requisitions')
		{
			$this->db->having('items_purchased = 0');
		}
		$this->db->group_by('receiving_id');
		$this->db->order_by('receiving_date');

		$data = array();
		$data['summary'] = $this->db->get()->result_array();
		$data['details'] = array();
		
		foreach($data['summary'] as $key=>$value)
		{
			$this->db->select('name, item_number, category, quantity_purchased, serialnumber,total, discount_percent, item_location');
			$this->db->select($this->db->dbprefix('receivings_items_temp').".receiving_quantity");
			$this->db->from('receivings_items_temp');
			$this->db->join('items', 'receivings_items_temp.item_id = items.item_id');
			$this->db->where('receiving_id = '.$value['receiving_id']);
			$data['details'][$key] = $this->db->get()->result_array();
		}
		
		return $data;
	}
	
	public function getSummaryData(array $inputs)
	{
		$this->db->select('sum(total) as total');
		$this->db->from('receivings_items_temp');
		$this->db->where('receiving_date BETWEEN '. $this->db->escape($inputs['start_date']). ' and '. $this->db->escape($inputs['end_date']));

		if ($inputs['location_id'] != 'all')
		{
			$this->db->where('item_location', $inputs['location_id']);
		}
		if ($inputs['receiving_type'] == 'receiving')
		{
			$this->db->where('quantity_purchased > 0');
		}
		elseif ($inputs['receiving_type'] == 'returns')
		{
			$this->db->where('quantity_purchased < 0');
		}
		elseif ($inputs['receiving_type'] == 'requisitions')
		{
			$this->db->where('quantity_purchased = 0');
		}

		return $this->db->get()->row_array();
	}
}
?>