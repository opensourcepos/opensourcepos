<?php
class Receiving_lib
{
	var $CI;

  	function __construct()
	{
		$this->CI =& get_instance();
	}

	function get_cart()
	{
		if(!$this->CI->session->userdata('cartRecv'))
			$this->set_cart(array());

		return $this->CI->session->userdata('cartRecv');
	}

	function set_cart($cart_data)
	{
		$this->CI->session->set_userdata('cartRecv',$cart_data);
	}

	function get_supplier()
	{
		if(!$this->CI->session->userdata('supplier'))
			$this->set_supplier(-1);

		return $this->CI->session->userdata('supplier');
	}

	function set_supplier($supplier_id)
	{
		$this->CI->session->set_userdata('supplier',$supplier_id);
	}

	function get_mode()
	{
		if(!$this->CI->session->userdata('recv_mode'))
			$this->set_mode('receive');

		return $this->CI->session->userdata('recv_mode');
	}

	function set_mode($mode)
	{
		$this->CI->session->set_userdata('recv_mode',$mode);
	}
    
    function get_stock_source()
    {
        if(!$this->CI->session->userdata('recv_stock_source'))
        {
             $stock_locations = $this->CI->Stock_locations->get_undeleted_all()->result_array();
             $location_name = $stock_locations[0]['location_id'];
             $this->set_stock_source($location_name);
        }
        return $this->CI->session->userdata('recv_stock_source');
    }

    function set_stock_source($stock_source)
    {
        $this->CI->session->set_userdata('recv_stock_source',$stock_source);
    }
    
    function clear_stock_source()
    {
    	$this->CI->session->unset_userdata('recv_stock_source');
    }
    
    function get_stock_destination()
    {
        if(!$this->CI->session->userdata('recv_stock_destination'))
        {
             $stock_locations = $this->CI->Stock_locations->get_undeleted_all()->result_array();
             $location_name = $stock_locations[0]['location_id'];
             $this->set_stock_destination($location_name);
        }
        return $this->CI->session->userdata('recv_stock_destination');
    }

    function set_stock_destination($stock_destination)
    {
        $this->CI->session->set_userdata('recv_stock_destination',$stock_destination);
    }
    
    function clear_stock_destination()
    {
    	$this->CI->session->unset_userdata('recv_stock_destination');
    }
    
	function add_item($item_id,$quantity=1,$item_location,$discount=0,$price=null,$description=null,$serialnumber=null)
	{
		//make sure item exists in database.
		if(!$this->CI->Item->exists($item_id))
		{
			//try to get item id given an item_number
			$item_id = $this->CI->Item->get_item_id($item_id);

			if(!$item_id)
				return false;
		}

		//Get items in the receiving so far.
		$items = $this->get_cart();

        //We need to loop through all items in the cart.
        //If the item is already there, get it's key($updatekey).
        //We also need to get the next key that we are going to use in case we need to add the
        //item to the list. Since items can be deleted, we can't use a count. we use the highest key + 1.

        $maxkey=0;                       //Highest key so far
        $itemalreadyinsale=FALSE;        //We did not find the item yet.
		$insertkey=0;                    //Key to use for new entry.
		$updatekey=0;                    //Key to use to update(quantity)

		foreach ($items as $item)
		{
            //We primed the loop so maxkey is 0 the first time.
            //Also, we have stored the key in the element itself so we can compare.
            //There is an array function to get the associated key for an element, but I like it better
            //like that!

			if($maxkey <= $item['line'])
			{
				$maxkey = $item['line'];
			}

			if($item['item_id']==$item_id && $item['item_location']==$item_location)
			{
				$itemalreadyinsale=TRUE;
				$updatekey=$item['line'];
			}
		}

		$insertkey=$maxkey+1;
		$item_info=$this->CI->Item->get_info($item_id);
		//array records are identified by $insertkey and item_id is just another field.
		$item = array(($insertkey)=>
		array(
			'item_id'=>$item_id,
			'item_location'=>$item_location,
			'stock_name'=>$this->CI->Stock_locations->get_location_name($item_location), 	
			'line'=>$insertkey,
			'name'=>$item_info->name,
			'description'=>$description!=null ? $description: $item_info->description,
			'serialnumber'=>$serialnumber!=null ? $serialnumber: '',
			'allow_alt_description'=>$item_info->allow_alt_description,
			'is_serialized'=>$item_info->is_serialized,
			'quantity'=>$quantity,
            'discount'=>$discount,
			'in_stock'=>$this->CI->Item_quantities->get_item_quantity($item_id, $item_location)->quantity,
			'price'=>$price!=null ? $price: $item_info->cost_price
			)
		);

		//Item already exists
		if($itemalreadyinsale)
		{
			$items[$updatekey]['quantity']+=$quantity;
		}
		else
		{
			//add to existing array
			$items+=$item;
		}

		$this->set_cart($items);
		return true;

	}

	function edit_item($line,$description,$serialnumber,$quantity,$discount,$price)
	{
		$items = $this->get_cart();
		if(isset($items[$line]))
		{
			$items[$line]['description'] = $description;
			$items[$line]['serialnumber'] = $serialnumber;
			$items[$line]['quantity'] = $quantity;
			$items[$line]['discount'] = $discount;
			$items[$line]['price'] = $price;
			$this->set_cart($items);
		}

		return false;
	}
    
	function is_valid_receipt($receipt_receiving_id)
	{
		//RECV #
		$pieces = explode(' ',$receipt_receiving_id);

		if(count($pieces)==2)
		{
			return $this->CI->Receiving->exists($pieces[1]);
		}

		return false;
	}
	
	function is_valid_item_kit($item_kit_id)
	{
		//KIT #
		$pieces = explode(' ',$item_kit_id);

		if(count($pieces)==2)
		{
			return $this->CI->Item_kit->exists($pieces[1]);
		}

		return false;
	}

	function return_entire_receiving($receipt_receiving_id)
	{
		//POS #
		$pieces = explode(' ',$receipt_receiving_id);
		$receiving_id = $pieces[1];

		$this->empty_cart();
		$this->delete_supplier();

		foreach($this->CI->Receiving->get_receiving_items($receiving_id)->result() as $row)
		{
			$this->add_item($row->item_id,-$row->quantity_purchased,$row->item_location,$row->discount_percent,$row->item_unit_price,$row->description,$row->serialnumber);
		}
		$this->set_supplier($this->CI->Receiving->get_supplier($receiving_id)->person_id);
	}
	
	function add_item_kit($external_item_kit_id,$item_location)
	{
		//KIT #
		$pieces = explode(' ',$external_item_kit_id);
		$item_kit_id = $pieces[1];
		
		foreach ($this->CI->Item_kit_items->get_info($item_kit_id) as $item_kit_item)
		{
			$this->add_item($item_kit_item['item_id'],$item_kit_item['quantity'],$item_location);
		}
	}

	function copy_entire_receiving($receiving_id)
	{
		$this->empty_cart();
		$this->delete_supplier();

		foreach($this->CI->Receiving->get_receiving_items($receiving_id)->result() as $row)
		{
			$this->add_item($row->item_id,$row->quantity_purchased,$row->item_location,$row->discount_percent,$row->item_unit_price,$row->description,$row->serialnumber);
		}
		$this->set_supplier($this->CI->Receiving->get_supplier($receiving_id)->person_id);

	}
	
	function copy_entire_requisition($requisition_id,$item_location)
	{
		$this->empty_cart();
		$this->delete_supplier();
	
		foreach($this->CI->Receiving->get_requisition_items($requisition_id)->result() as $row)
		{
			$this->add_item_unit($row->item_id,$row->requisition_quantity,$item_location,$row->description);
		}
		$this->set_supplier($this->CI->Receiving->get_supplier($requisition_id)->person_id);
	
	}

	function delete_item($line)
	{
		$items=$this->get_cart();
		unset($items[$line]);
		$this->set_cart($items);
	}

	function empty_cart()
	{
		$this->CI->session->unset_userdata('cartRecv');
	}

	function delete_supplier()
	{
		$this->CI->session->unset_userdata('supplier');
	}

	function clear_mode()
	{
		$this->CI->session->unset_userdata('receiving_mode');
	}

	function clear_all()
	{
		$this->clear_mode();
		$this->empty_cart();
		$this->delete_supplier();
	}

	function get_total()
	{
		$total = 0;
		foreach($this->get_cart() as $item)
		{
            $total+=($item['price']*$item['quantity']-$item['price']*$item['quantity']*$item['discount']/100);
		}
		
		return $total;
	}
}
?>