<?php
class Item_quantities extends CI_Model
{
    function exists($item_id,$location_id)
    {
        $this->db->from('item_quantities');
        $this->db->where('item_id',$item_id);
        $this->db->where('location_id',$location_id);
        $query = $this->db->get();

        return ($query->num_rows()==1);
    }
    
    function save($location_detail, $item_id, $location_id)
    {
        if (!$this->exists($item_id,$location_id))
        {
            if($this->db->insert('item_quantities',$location_detail))
            {
                return true;
            }
            return false;
        }

        $this->db->where('item_id', $item_id);
        $this->db->where('location_id', $location_id);
        return $this->db->update('item_quantities',$location_detail);
    }
    
    function get_item_quantity($item_id, $location_id)
    {     
        $this->db->from('item_quantities');
        $this->db->where('item_id',$item_id);
        $this->db->where('location_id',$location_id);
        $result = $this->db->get()->row();
        if(empty($result) == true)
        {
            //Get empty base parent object, as $item_id is NOT an item
            $result=new stdClass();
            //Get all the fields from items table (TODO to be reviewed)
            $fields = $this->db->list_fields('item_quantities');
            foreach ($fields as $field)
            {
                $result->$field='';
            }
            $result->quantity = 0;
        }          
        return $result;   
    }
}
?>