<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Report.php");

class Detailed_receivings extends Report
{
	public function create(array $inputs)
	{
		//Create our temp tables to work with the data in our report
		$this->Receiving->create_temp_table($inputs);
	}

	public function getDataColumns()
	{
		return array(
			'summary' => array(
				array('id' => $this->lang->line('reports_receiving_id')),
				array('receiving_time' => $this->lang->line('reports_date'), 'sortable' => FALSE),
				array('quantity' => $this->lang->line('reports_quantity')),
				array('employee_name' => $this->lang->line('reports_received_by')),
				array('supplier_name' => $this->lang->line('reports_supplied_by')),
				array('total' => $this->lang->line('reports_total'), 'sorter' => 'number_sorter'),
				array('payment_type' => $this->lang->line('reports_payment_type')),
				array('comment' => $this->lang->line('reports_comments')),
				array('reference' => $this->lang->line('receivings_reference'))),
			'details' => array(
				$this->lang->line('reports_item_number'),
				$this->lang->line('reports_name'),
				$this->lang->line('reports_category'),
				$this->lang->line('reports_quantity'),
				$this->lang->line('reports_total'),
				$this->lang->line('reports_discount'))
		);
	}

	public function getDataByReceivingId($receiving_id)
	{
		$this->db->select('receiving_id,
			MAX(receiving_time) as receiving_time,
			SUM(quantity_purchased) AS items_purchased,
			MAX(CONCAT(employee.first_name, " ", employee.last_name)) AS employee_name,
			MAX(supplier.company_name) AS supplier_name,
			SUM(subtotal) AS subtotal,
			SUM(total) AS total,
			SUM(profit) AS profit,
			MAX(payment_type) as payment_type,
			MAX(comment) as comment,
			MAX(reference) as reference');
		$this->db->from('receivings_items_temp');
		$this->db->join('people AS employee', 'receivings_items_temp.employee_id = employee.person_id');
		$this->db->join('suppliers AS supplier', 'receivings_items_temp.supplier_id = supplier.person_id', 'left');
		$this->db->where('receiving_id', $receiving_id);
		$this->db->group_by('receiving_id');

		return $this->db->get()->row_array();
	}

	public function getData(array $inputs)
	{
		$this->db->select('receiving_id,
			MAX(receiving_time) as receiving_time,
			SUM(quantity_purchased) AS items_purchased,
			MAX(CONCAT(employee.first_name," ",employee.last_name)) AS employee_name,
			MAX(supplier.company_name) AS supplier_name,
			SUM(total) AS total,
			SUM(profit) AS profit,
			MAX(payment_type) AS payment_type,
			MAX(comment) AS comment,
			MAX(reference) AS reference');
		$this->db->from('receivings_items_temp AS receivings_items_temp');
		$this->db->join('people AS employee', 'receivings_items_temp.employee_id = employee.person_id');
		$this->db->join('suppliers AS supplier', 'receivings_items_temp.supplier_id = supplier.person_id', 'left');

		if($inputs['location_id'] != 'all')
		{
			$this->db->where('item_location', $inputs['location_id']);
		}

		if($inputs['receiving_type'] == 'receiving')
		{
			$this->db->where('quantity_purchased >', 0);
		}
		elseif($inputs['receiving_type'] == 'returns')
		{
			$this->db->where('quantity_purchased <', 0);
		}
		elseif($inputs['receiving_type'] == 'requisitions')
		{
			$this->db->having('items_purchased = 0');
		}
		$this->db->group_by('receiving_id', 'receiving_time');
		$this->db->order_by('receiving_id');

		$data = array();
		$data['summary'] = $this->db->get()->result_array();
		$data['details'] = array();

		foreach($data['summary'] as $key=>$value)
		{
			$this->db->select('
				MAX(name) AS name, 
				MAX(item_number) AS item_number, 
				MAX(category) AS category, 
				MAX(quantity_purchased) AS quantity_purchased, 
				MAX(serialnumber) AS serialnumber, 
				MAX(total) AS total, 
				MAX(discount) AS discount, 
				MAX(discount_type) AS discount_type, 
				MAX(item_location) AS item_location, 
				MAX(item_receiving_quantity) AS receiving_quantity');
			$this->db->from('receivings_items_temp');
			$this->db->join('items', 'receivings_items_temp.item_id = items.item_id');
			if(count($inputs['definition_ids']) > 0)
			{
				$format = $this->db->escape(dateformat_mysql());
				$this->db->select('GROUP_CONCAT(DISTINCT CONCAT_WS(\'_\', definition_id, attribute_value) ORDER BY definition_id SEPARATOR \'|\') AS attribute_values');
				$this->db->select("GROUP_CONCAT(DISTINCT CONCAT_WS('_', definition_id, DATE_FORMAT(attribute_date, $format)) SEPARATOR '|') AS attribute_dtvalues");
				$this->db->select('GROUP_CONCAT(DISTINCT CONCAT_WS(\'_\', definition_id, attribute_decimal) SEPARATOR \'|\') AS attribute_dvalues');
				$this->db->join('attribute_links', 'attribute_links.item_id = items.item_id AND attribute_links.receiving_id = receivings_items_temp.receiving_id AND definition_id IN (' . implode(',', $inputs['definition_ids']) . ')', 'left');
				$this->db->join('attribute_values', 'attribute_values.attribute_id = attribute_links.attribute_id', 'left');
			}
			$this->db->where('receivings_items_temp.receiving_id', $value['receiving_id']);
			$this->db->group_by('receivings_items_temp.receiving_id, receivings_items_temp.item_id');
			$data['details'][$key] = $this->db->get()->result_array();
		}

		return $data;
	}

	public function getSummaryData(array $inputs)
	{
		$this->db->select('SUM(total) AS total');
		$this->db->from('receivings_items_temp');

		if($inputs['location_id'] != 'all')
		{
			$this->db->where('item_location', $inputs['location_id']);
		}

		if($inputs['receiving_type'] == 'receiving')
		{
			$this->db->where('quantity_purchased >', 0);
		}
		elseif($inputs['receiving_type'] == 'returns')
		{
			$this->db->where('quantity_purchased <', 0);
		}
		elseif($inputs['receiving_type'] == 'requisitions')
		{
			$this->db->where('quantity_purchased', 0);
		}

		return $this->db->get()->row_array();
	}
}
?>
