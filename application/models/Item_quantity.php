<?php
class Item_quantity extends CI_Model
{
    function exists($item_id, $location_id)
    {
        $this->db->from('item_quantities');
        $this->db->where('item_id', $item_id);
        $this->db->where('location_id', $location_id);
        $query = $this->db->get();

        return ($query->num_rows()==1);
    }
    
    function save($location_detail, $item_id, $location_id)
    {
        if (!$this->exists($item_id, $location_id))
        {
            if($this->db->insert('item_quantities', $location_detail))
            {
                return true;
            }
            return false;
        }

        $this->db->where('item_id', $item_id);
        $this->db->where('location_id', $location_id);

        return $this->db->update('item_quantities', $location_detail);
    }
    
    function get_item_quantity($item_id, $location_id)
    {     
        $this->db->from('item_quantities');
        $this->db->where('item_id', $item_id);
        $this->db->where('location_id', $location_id);
        $result = $this->db->get()->row();
        if(empty($result) == true)
        {
            //Get empty base parent object, as $item_id is NOT an item
            $result = new stdClass();
            //Get all the fields from items table (TODO to be reviewed)
            $fields = $this->db->list_fields('item_quantities');
            foreach($fields as $field)
            {
                $result->$field = '';
            }
            $result->quantity = 0;
        }
		
        return $result;   
    }
	
	/*
	 * changes to quantity of an item according to the given amount.
	 * if $quantity_change is negative, it will be subtracted,
	 * if it is positive, it will be added to the current quantity
	 */
	function change_quantity($item_id, $location_id, $quantity_change)
	{
		$quantity_old = $this->get_item_quantity($item_id, $location_id);
		$quantity_new = $quantity_old->quantity + intval($quantity_change);
		$location_detail = array('item_id'=>$item_id, 'location_id'=>$location_id, 'quantity'=>$quantity_new);

		return $this->save($location_detail, $item_id, $location_id);
	}
	
	/*
	* Set to 0 all quantity in the given item
	*/
	function reset_quantity($item_id)
	{
        $this->db->where('item_id', $item_id);

        return $this->db->update('item_quantities', array('quantity'=>0));
	}
	
	/*
	* Set to 0 all quantity in the given list of items
	*/
	function reset_quantity_list($item_ids)
	{
        $this->db->where_in('item_id', $item_ids);

        return $this->db->update('item_quantities', array('quantity'=>0));
	}
}
?>