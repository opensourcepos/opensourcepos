<?php
class Receiving extends CI_Model
{
	function get_info($receiving_id)
	{
		$this->db->from('receivings');
		$this->db->where('receiving_id',$receiving_id);
		return $this->db->get();
	}
	
	function get_requisition_info($requisition_id) 
	{
		$this->db->from('requisitions');
		$this->db->where('requisition_id',$requisition_id);
		return $this->db->get();	
	}

	function exists($receiving_id)
	{
		$this->db->from('receivings');
		$this->db->where('receiving_id',$receiving_id);
		$query = $this->db->get();

		return ($query->num_rows()==1);
	}

	function save ($items,$supplier_id,$employee_id,$comment,$payment_type,$stock_location,$receiving_id=false)
	{
		if(count($items)==0)
			return -1;

		$receivings_data = array(
		'supplier_id'=> $this->Supplier->exists($supplier_id) ? $supplier_id : null,
		'employee_id'=>$employee_id,
		'payment_type'=>$payment_type,
		'comment'=>$comment
		);

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		$this->db->insert('receivings',$receivings_data);
		$receiving_id = $this->db->insert_id();


		foreach($items as $line=>$item)
		{
			$cur_item_info = $this->Item->get_info($item['item_id']);

			$receivings_items_data = array
			(
				'receiving_id'=>$receiving_id,
				'item_id'=>$item['item_id'],
				'line'=>$item['line'],
				'description'=>$item['description'],
				'serialnumber'=>$item['serialnumber'],
				'quantity_purchased'=>$item['quantity'],
				'discount_percent'=>$item['discount'],
				'item_cost_price' => $cur_item_info->cost_price,
				'item_unit_price'=>$item['price']
			);

			$this->db->insert('receivings_items',$receivings_items_data);

			//Update stock quantity
			$item_quantity = $this->Item_quantitys->get_item_quantity($item['item_id'], $this->receiving_lib->get_location_id_from_stock_location($stock_location));		
            $this->Item_quantitys->save(array('quantity'=>$item_quantity->quantity + $item['quantity'],
                                              'item_id'=>$item['item_id'],
                                              'location_id'=>$this->receiving_lib->get_location_id_from_stock_location($stock_location)), $item_quantity->item_quantity_id);
			
			
			$qty_recv = $item['quantity'];
			$recv_remarks ='RECV '.$receiving_id;
			$inv_data = array
			(
				'trans_date'=>date('Y-m-d H:i:s'),
				'trans_items'=>$item['item_id'],
				'trans_user'=>$employee_id,
				'location_id'=>$this->receiving_lib->get_location_id_from_stock_location($stock_location),
				'trans_comment'=>$recv_remarks,
				'trans_inventory'=>$qty_recv
			);
			$this->Inventory->insert($inv_data);

			$supplier = $this->Supplier->get_info($supplier_id);
		}
		$this->db->trans_complete();
		
		if ($this->db->trans_status() === FALSE)
		{
			return -1;
		}

		return $receiving_id;
	}

    function save_requisition ($items,$employee_id,$comment,$receiving_id=false)
    {
        if(count($items)==0)
            return -1;

        $requisition_data = array(
        'employee_id'=>$employee_id,
        'comment'=>$comment
        );

        //Run these queries as a transaction, we want to make sure we do all or nothing
        $this->db->trans_start();

        $this->db->insert('requisitions',$requisition_data);
        
        $requisition_id = $this->db->insert_id();


        foreach($items as $line=>$item)
        {
            $cur_item_info = $this->Item->get_info($item['item_id']);
            $related_item_number = $this->Item_unit->get_info($item['item_id'])->related_number;
            $related_item_unit_quantity = $this->Item_unit->get_info($item['item_id'])->unit_quantity;         
            $related_item_total_quantity = $item['quantity']*$related_item_unit_quantity;
            $related_item_id;
            //Update stock quantity  
            $related_item_data = array();  
            if((strlen($related_item_number) == 0) or ($related_item_number == $cur_item_info->item_number))
            {
                if($this->Item->is_sale_store_item_exist($cur_item_info->item_number))
                {
                    $related_item_id = $this->Item->get_item_id($cur_item_info->item_number,'sale_stock');               
                    $related_item_data = array('quantity'=>$this->Item->get_info($related_item_id)->quantity + $related_item_total_quantity);
                    $this->Item->save($related_item_data,$related_item_id);
                }
                else 
                {
                    $related_item_data = array(
                                    'name'=>$cur_item_info->name,
                                    'description'=>$cur_item_info->description,
                                    'category'=>$cur_item_info->category,
                                    'supplier_id'=>$cur_item_info->supplier_id,
                                    'item_number'=>$cur_item_info->item_number,
                                    'cost_price'=>$cur_item_info->cost_price,
                                    'unit_price'=>$cur_item_info->unit_price,
                                    'quantity'=>$related_item_total_quantity,
                                    'reorder_level'=>$cur_item_info->reorder_level,
                                    'location'=>$cur_item_info->location,
                                    'allow_alt_description'=>$cur_item_info->allow_alt_description,
                                    'is_serialized'=>$cur_item_info->is_serialized,
                                    'stock_type'=>'sale_stock',
                                    'custom1'=>$cur_item_info->custom1,   /**GARRISON ADDED 4/21/2013**/          
                                    'custom2'=>$cur_item_info->custom2,/**GARRISON ADDED 4/21/2013**/
                                    'custom3'=>$cur_item_info->custom3,/**GARRISON ADDED 4/21/2013**/
                                    'custom4'=>$cur_item_info->custom4,/**GARRISON ADDED 4/21/2013**/
                                    'custom5'=>$cur_item_info->custom5,/**GARRISON ADDED 4/21/2013**/
                                    'custom6'=>$cur_item_info->custom6,/**GARRISON ADDED 4/21/2013**/
                                    'custom7'=>$cur_item_info->custom7,/**GARRISON ADDED 4/21/2013**/
                                    'custom8'=>$cur_item_info->custom8,/**GARRISON ADDED 4/21/2013**/
                                    'custom9'=>$cur_item_info->custom9,/**GARRISON ADDED 4/21/2013**/
                                    'custom10'=>$cur_item_info->custom10/**GARRISON ADDED 4/21/2013**/
                                    );
                    $this->Item->save($related_item_data); 
                    $related_item_id = $related_item_data[item_id];
                }
                
            }
            else if($this->Item->is_sale_store_item_exist($related_item_number))
            {
                $related_item_id = $this->Item->get_item_id($related_item_number,'sale_stock');               
                $related_item_data = array('quantity'=>$this->Item->get_info($related_item_id)->quantity + $related_item_total_quantity);
                $this->Item->save($related_item_data,$related_item_id);
            }
            else if($this->Item->is_warehouse_item_exist($related_item_number))
            {
                $related_item_id = $this->Item->get_item_id($related_item_number,'warehouse'); 
                $item_data_temp= $this->Item->get_info($related_item_id,'warehouse');
                $related_item_data = array(
                                    'name'=>$item_data_temp->name,
                                    'description'=>$item_data_temp->description,
                                    'category'=>$item_data_temp->category,
                                    'supplier_id'=>$item_data_temp->supplier_id,
                                    'item_number'=>$item_data_temp->item_number,
                                    'cost_price'=>$item_data_temp->cost_price,
                                    'unit_price'=>$item_data_temp->unit_price,
                                    'quantity'=>$related_item_total_quantity,
                                    'reorder_level'=>$item_data_temp->reorder_level,
                                    'location'=>$item_data_temp->location,
                                    'allow_alt_description'=>$item_data_temp->allow_alt_description,
                                    'is_serialized'=>$item_data_temp->is_serialized,
                                    'stock_type'=>'sale_stock',
                                    'custom1'=>$item_data_temp->custom1,   /**GARRISON ADDED 4/21/2013**/          
                                    'custom2'=>$item_data_temp->custom2,/**GARRISON ADDED 4/21/2013**/
                                    'custom3'=>$item_data_temp->custom3,/**GARRISON ADDED 4/21/2013**/
                                    'custom4'=>$item_data_temp->custom4,/**GARRISON ADDED 4/21/2013**/
                                    'custom5'=>$item_data_temp->custom5,/**GARRISON ADDED 4/21/2013**/
                                    'custom6'=>$item_data_temp->custom6,/**GARRISON ADDED 4/21/2013**/
                                    'custom7'=>$item_data_temp->custom7,/**GARRISON ADDED 4/21/2013**/
                                    'custom8'=>$item_data_temp->custom8,/**GARRISON ADDED 4/21/2013**/
                                    'custom9'=>$item_data_temp->custom9,/**GARRISON ADDED 4/21/2013**/
                                    'custom10'=>$item_data_temp->custom10/**GARRISON ADDED 4/21/2013**/
                                    );
                $this->Item->save($related_item_data); 
                $related_item_id = $related_item_data[item_id];
            }
            else 
            {
               return false;
            }
                        
            $item_data = array('quantity'=>$cur_item_info->quantity - $item['quantity']);
            $this->Item->save($item_data,$item['item_id']);
            
            //update requisition item
            $requisition_items_data = array
            (
                'requisition_id'=>$requisition_id,
                'item_id'=>$item['item_id'],
                'line'=>$item['line'],
                'requisition_quantity'=>$item['quantity'],
                'related_item_id'=>$related_item_id,
                'related_item_quantity'=>$related_item_unit_quantity,
                'related_item_total_quantity' => $item['quantity']*$related_item_unit_quantity
            );

            $this->db->insert('requisitions_items',$requisition_items_data);            
                                              
            //update inventory
            $qty_recv = $item['quantity']*(-1);
            $recv_remarks ='REQS '.$requisition_id;
            $inv_data = array
            (
                'trans_date'=>date('Y-m-d H:i:s'),
                'trans_items'=>$item['item_id'],
                'trans_user'=>$employee_id,
                'trans_comment'=>$recv_remarks,
                'trans_inventory'=>$qty_recv
            );
            $this->Inventory->insert($inv_data);
            
            $related_item_qty_recv = $requisition_items_data['related_item_total_quantity'];
            $recv_remarks ='REQS '.$requisition_id;
            $related_item_inv_data = array
            (
                'trans_date'=>date('Y-m-d H:i:s'),
                'trans_items'=>$related_item_id,
                'trans_user'=>$employee_id,
                'trans_comment'=>$recv_remarks,
                'trans_inventory'=>$related_item_qty_recv
            );
            $this->Inventory->insert($related_item_inv_data);

        }
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE)
        {
            return -1;
        }

        return $requisition_id;
    }

	function get_receiving_items($receiving_id)
	{
		$this->db->from('receivings_items');
		$this->db->where('receiving_id',$receiving_id);
		return $this->db->get();
	}
	
	function get_requisition_items($requisition_id)
	{
		$this->db->from('requisitions_items');
		$this->db->where('requisition_id',$requisition_id);
		return $this->db->get();
	}

	function get_supplier($receiving_id)
	{
		$this->db->from('receivings');
		$this->db->where('receiving_id',$receiving_id);
		return $this->Supplier->get_info($this->db->get()->row()->supplier_id);
	}
	
	//We create a temp table that allows us to do easy report/receiving queries
	public function create_receivings_items_temp_table()
	{
		$this->db->query("CREATE TEMPORARY TABLE ".$this->db->dbprefix('receivings_items_temp')."
		(SELECT date(receiving_time) as receiving_date, ".$this->db->dbprefix('receivings_items').".receiving_id, comment,payment_type, employee_id, 
		".$this->db->dbprefix('items').".item_id, ".$this->db->dbprefix('receivings').".supplier_id, quantity_purchased, item_cost_price, item_unit_price,
		discount_percent, (item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100) as subtotal,
		".$this->db->dbprefix('receivings_items').".line as line, serialnumber, ".$this->db->dbprefix('receivings_items').".description as description,
		ROUND((item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100),2) as total,
		(item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100) - (item_cost_price*quantity_purchased) as profit
		FROM ".$this->db->dbprefix('receivings_items')."
		INNER JOIN ".$this->db->dbprefix('receivings')." ON  ".$this->db->dbprefix('receivings_items').'.receiving_id='.$this->db->dbprefix('receivings').'.receiving_id'."
		INNER JOIN ".$this->db->dbprefix('items')." ON  ".$this->db->dbprefix('receivings_items').'.item_id='.$this->db->dbprefix('items').'.item_id'."
		GROUP BY receiving_id, item_id, line)");
	}
    
    public function create_requisition_items_temp_table()
    {
        $this->db->query("CREATE TEMPORARY TABLE ".$this->db->dbprefix('requisition_items_temp')."
        (SELECT date(requisition_time) as requisition_date, ".$this->db->dbprefix('requisitions_items').".requisition_id, comment, employee_id, 
        ".$this->db->dbprefix('items').".item_id, related_item_id, requisition_quantity, related_item_quantity,
        related_item_total_quantity, ".$this->db->dbprefix('requisitions_items').".line as line 
        FROM ".$this->db->dbprefix('requisitions_items')."
        INNER JOIN ".$this->db->dbprefix('requisitions')." ON  ".$this->db->dbprefix('requisitions_items').'.requisition_id='.$this->db->dbprefix('requisitions').'.requisition_id'."
        INNER JOIN ".$this->db->dbprefix('items')." ON  ".$this->db->dbprefix('requisitions_items').'.item_id='.$this->db->dbprefix('items').'.item_id'."
        GROUP BY requisition_id, item_id, line)");
    }

}
?>
