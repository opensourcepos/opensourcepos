<?php
class Item_unit extends CI_Model
{
    /*
    Gets item info for a particular item
    */
    function get_info($item_id)
    {
        $this->db->from('item_unit');
        $this->db->where('item_id',$item_id);
        
        $query = $this->db->get();

        if($query->num_rows()==1)
        {
            return $query->row();
        }
        else
        {
            //Get empty base parent object, as $item_id is NOT an item
            $item_obj=new stdClass();

            //Get all the fields from items table
            $fields = $this->db->list_fields('item_unit');

            foreach ($fields as $field)
            {
                $item_obj->$field='';
            }

            return $item_obj;
        }
    }
    
    /*
    Inserts or updates an item's unit
    */
    function save(&$items_unit_data, $item_id)
    {
        //Run these queries as a transaction, we want to make sure we do all or nothing
        $this->db->trans_start();

        $this->delete($item_id);

        $this->db->insert('item_unit',$items_unit_data);      
       
        
        $this->db->trans_complete();
        return true;
    }

    /*
    Deletes unit given an item
    */
    function delete($item_id)
    {
        return $this->db->delete('item_unit', array('item_id' => $item_id)); 
    }
}
?>
