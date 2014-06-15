<?php
require_once("report.php");
class Detailed_requisition extends Report
{
    function __construct()
    {
        parent::__construct();
    }
    
    public function getDataColumns()
    {
        return array('summary' => array($this->lang->line('reports_requisition_id'), 
                                        $this->lang->line('reports_date'), 
                                        $this->lang->line('reports_requisition_by'), 
                                        $this->lang->line('reports_comments')),
                    'details' => array($this->lang->line('reports_requisition_item'), 
                                       $this->lang->line('reports_requisition_item_quantity'), 
                                       $this->lang->line('reports_requisition_related_item'), 
                                       $this->lang->line('reports_requisition_related_item_unit_quantity'), 
                                       $this->lang->line('reports_requisition_related_item_total_quantity'))
                    );      
    }
    
    public function getData(array $inputs)
    {
        $this->db->select('requisition_id, requisition_date, CONCAT(employee.first_name," ",employee.last_name) as employee_name, comment', false);
        $this->db->from('requisition_items_temp');
        $this->db->join('people as employee', 'requisition_items_temp.employee_id = employee.person_id');       
        $this->db->where('requisition_date BETWEEN "'. $inputs['start_date']. '" and "'. $inputs['end_date'].'"');
      
        $this->db->group_by('requisition_id');
        $this->db->order_by('requisition_date');

        $data = array();
        $data['summary'] = $this->db->get()->result_array();
        $data['details'] = array();
        
        foreach($data['summary'] as $key=>$value)
        {
            $this->db->select('name, requisition_quantity, related_item_id, related_item_quantity, related_item_total_quantity');
            $this->db->from('requisition_items_temp');
            $this->db->join('items', 'requisition_items_temp.item_id = items.item_id');
            $this->db->where('requisition_id = '.$value['requisition_id']);
            $data['details'][$key] = $this->db->get()->result_array();
            
            foreach($data['details'][$key] as $arr_index=>$deatil_reqs)
            {
                $related_item_name = $this->Item->get_info($deatil_reqs['related_item_id'])->name;
                $data['details'][$key][$arr_index]['related_item_id'] = $related_item_name;
            }
            
        }
        
        return $data;
    }

    public function getSummaryData(array $inputs)
    {
        //Do nothing
    }
}
?>