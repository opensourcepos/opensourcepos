<?php
class Dinner_table extends CI_Model
{
    public function exists($dinner_table_id)
    {
        $this->db->from('dinner_tables');  
        $this->db->where('dinner_table_id', $dinner_table_id);

        return ($this->db->get()->num_rows() >= 1);
    }


    public function save(&$table_data, $dinner_table_id) 
    {
        $name = $$table_data['name'];

        if(!$this->exists($dinner_table_id))
        {
            $this->db->trans_start();

            $location_data = array('name'=>$name, 'deleted'=>0);
            $this->db->insert('dinner_tables', $table_data);
            $dinner_table_id = $this->db->insert_id();

            $this->db->trans_complete();

            return $this->db->trans_status();
        }
        else 
        {
            $this->db->where('dinner_table_id', $dinner_table_id);

            return $this->db->update('dinner_tables', $table_data);
        }
    }

    /*
    Get empty tables
    */
    public function get_empty_tables()
    {
        $this->db->from('dinner_tables');
        $this->db->where('status', 0);
        $this->db->where('deleted', 0);

        $empty_tables =  $this->db->get()->result_array();

        $empty_tables_array = array();
        foreach($empty_tables as $empty_table)
        {
            $empty_tables_array[$empty_table['dinner_table_id']] = $empty_table['name'];
        }

        return $empty_tables_array;

    }

    public function get_name($dinner_table_id)
    {
        $this->db->from('dinner_tables');
        $this->db->where('dinner_table_id',$dinner_table_id);

        return $this->db->get()->row()->name;
    }

    public function get_all()
    {
        $this->db->from('dinner_tables');

        return $this->db->get();
    }

    public function get_undeleted_all()
    {
        $this->db->from('dinner_tables');
        $this->db->where('deleted', 0);

        return $this->db->get();
    }

    /*
    Deletes one table
    */
    public function delete($dinner_table_id)
    {
        $this->db->trans_start();

        $this->db->where('dinner_table_id', $dinner_table_id);
        $this->db->update('dinner_tables', array('deleted' => 1));

        $this->db->trans_complete();

        return $this->db->trans_status();
    }
}
?>