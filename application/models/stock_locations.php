<?php
class Stock_locations extends CI_Model
{
    function exists($location_name='')
    {
        $this->db->from('stock_locations');  
        $this->db->where('location_name',$location_name);
        $query = $this->db->get();
        
        return ($query->num_rows()==1);
    }
    
    function get_all($limit=10000, $offset=0)
    {
        $this->db->from('stock_locations');
        $this->db->limit($limit);
        $this->db->offset($offset);
        return $this->db->get();
    }
    
    function get_location_names() 
    {
    	$this->db->select('location_name');
    	$this->db->from('stock_locations');
    	$this->db->where('deleted', 0);
    	return $this->db->get();
    }
    
    function concat_location_names() 
    {
    	$this->db->select('GROUP_CONCAT(location_name SEPARATOR\',\') AS location_names', FALSE);
    	$this->db->from('stock_locations');
    	$this->db->where('deleted', 0);
    	return $this->db->get()->row();
    }
    
    function get_undeleted_all()
    {
        $this->db->from('stock_locations');
        $this->db->join('permissions','permissions.location_id=stock_locations.location_id');
		$this->db->join('grants','grants.permission_id=permissions.permission_id');
        $this->db->where('person_id', $this->session->userdata('person_id'));
        $this->db->where('deleted',0);
        return $this->db->get();
    }
    
    function get_allowed_locations()
    {
    	$stock = $this->get_undeleted_all()->result_array();
    	$stock_locations = array();
    	foreach($stock as $location_data)
    	{
    		$stock_locations[$location_data['location_id']] = $location_data['location_name'];
    	}
    	return $stock_locations;
    }
    
    function get_default_location_id()
    {
    	$this->db->from('stock_locations');
    	$this->db->join('permissions','permissions.location_id=stock_locations.location_id');
		$this->db->join('grants','grants.permission_id=permissions.permission_id');
    	$this->db->where('person_id', $this->session->userdata('person_id'));
    	$this->db->where('deleted',0);
    	$this->db->limit(1);
    	return $this->db->get()->row()->location_id;
    }
    
    function get_location_name($location_id) 
    {
    	$this->db->from('stock_locations');
    	$this->db->where('location_id',$location_id);
    	return $this->db->get()->row()->location_name;
    }
    
    function array_save($stock_locations)
    {
    	$allowed_locations = $this->get_allowed_locations();
    	// check for insertion
        foreach ($stock_locations as $location_name)
        {
        	if(!$this->exists($location_name)) 
            {
            	$this->db->trans_start();
            	$location_data = array('location_name'=>$location_name,'deleted'=>0);
                $this->db->insert('stock_locations',$location_data);
                $location_id = $this->db->insert_id();
                	
                // insert new permission for stock location
                $permission_id = 'items_'.$location_name;
                $permission_data = array('permission_id'=>$permission_id,'module_id'=>'items','location_id' => $location_id);
                $this->db->insert('permissions', $permission_data);
                
                // insert grants for new permission
                $employees = $this->Employee->get_all();
                foreach ($employees->result_array() as $employee)
                {
                	$grants_data = array('permission_id' => $permission_id, 'person_id' => $employee['person_id']);
                	$this->db->insert('grants', $grants_data);
                }
                	
                // insert quantities for existing items
                $items = $this->Item->get_all();
                foreach ($items->result_array() as $item)
                {
                	$quantity_data = array('item_id' => $item['item_id'], 'location_id' => $location_id, 'quantity' => 0);
                	$this->db->insert('item_quantities', $quantity_data);
                }        	
		        $this->db->trans_complete();
            }
            else if (!in_array($location_name, array_values($allowed_locations))) 
            {
            	$this->db->where('location_name', $location_name);
            	$this->db->update('stock_locations', array('deleted' => 0));
            }
        }
         
        // check for deletion
       	foreach ($allowed_locations as $location_id => $location_name)
       	{
       		if (!in_array($location_name, $stock_locations))
       		{
       			$this->db->where('location_id', $location_id);
       			$this->db->update('stock_locations', array('deleted' => 1));
				       			
       			$this->db->delete('permissions', array('permission_id' => 'items_'.$location_name));
       		}
       	}
        
        return true;            
    }
}
?>