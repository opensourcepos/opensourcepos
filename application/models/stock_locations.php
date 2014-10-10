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
        $location_db = $this->get_all()->result_array();     
        //Delete all in db
        $this->db->trans_start();
        $location_ids=array();
        foreach($location_db as $db)
        {
            array_push($location_ids,$db['location_id']);            
        }
        if (sizeof($location_ids) > 0) 
        {
	        $this->db->where_in('location_id', $location_ids);
	        $this->db->update('stock_locations',array('deleted'=>1));
	        $this->db->trans_complete();
        }
        
        //Update the stock location
        $this->db->trans_start();
        foreach ($stock_locations as $location)
        {
            $to_create = true;
            foreach($location_db as $db)
            {
                if($db['location_name'] == $location)
                {
                    if($db['deleted'] == 1)
                    {
                        $this->db->where('location_id', $db['location_id']);
                        
                        $this->db->update('stock_locations',array('location_name'=>$db['location_name'],'deleted'=>0));
						// remmove module (and permissions) for stock location 
                        $this->db->delete('permissions', array('module_id' => 'items_'.$db['location_name']));
                    }
                    $to_create = false;
                    break;
                }
            }
            
            if($to_create)
            {
                $location_data = array('location_name'=>$location,'deleted'=>0);
                $this->db->insert('stock_locations',$location_data);
                $location_id = $this->db->insert_id();
                
                // insert new permission for stock location
                $permission_id = 'items_'.$location;
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
            }
        }
        $this->db->trans_complete();
        return true;            
    }
}
?>
