<?php
class Item_quantitys extends CI_Model
{
    function exists($item_quantity_id)
    {
        $this->db->from('item_quantitys');
        $this->db->where('item_quantity_id',$item_quantity_id);
        $query = $this->db->get();

        return ($query->num_rows()==1);
    }
    
    function save($location_detail, $item_quantity_id=false)
    {
        if (!$item_quantity_id or !$this->exists($item_quantity_id))
        {
            if($this->db->insert('item_quantitys',$location_detail))
            {
                $location_detail['item_quantity_id']=$this->db->insert_id();
                return true;
            }
            return false;
        }

        $this->db->where('item_quantity_id', $item_quantity_id);
        return $this->db->update('item_quantitys',$location_detail);
    }
    
    function get_item_quantity($item_id, $location_id)
    {     
        $this->db->from('item_quantitys');
        $this->db->where('item_id',$item_id);
        $this->db->where('location_id',$location_id);
        $result = $this->db->get()->row();
        if(empty($result) == true)
        {
            //Get empty base parent object, as $item_id is NOT an item
            $result=new stdClass();
            //Get all the fields from items table
            $fields = $this->db->list_fields('item_quantitys');
            foreach ($fields as $field)
            {
                $result->$field='';
            }
        }          
        return $result;   
    }
}
?>