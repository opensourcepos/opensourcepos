<?php
class Sale_suspended extends CI_Model
{
	function get_all()
	{
		$this->db->from('sales_suspended');
		$this->db->order_by('sale_id');
		return $this->db->get();
	}
	
	public function get_info($sale_id)
	{
		$this->db->from('sales_suspended');
		$this->db->where('sale_id',$sale_id);
		return $this->db->get();
	}

	function exists($sale_id)
	{
		$this->db->from('sales_suspended');
		$this->db->where('sale_id',$sale_id);
		$query = $this->db->get();

		return ($query->num_rows()==1);
	}
	
	function update($sale_data, $sale_id)
	{
		$this->db->where('sale_id', $sale_id);
		$success = $this->db->update('sales_suspended',$sale_data);
		
		return $success;
	}
	
	function save ($items,$customer_id,$employee_id,$comment,$payments,$sale_id=false)
	{
		if(count($items)==0)
			return -1;

		//Alain Multiple payments
		//Build payment types string
		$payment_types='';
		foreach($payments as $payment_id=>$payment)
		{
			$payment_types=$payment_types.$payment['payment_type'].': '.to_currency($payment['payment_amount']).'<br />';
		}

		$sales_data = array(
			'sale_time' => date('Y-m-d H:i:s'),
			'customer_id'=> $this->Customer->exists($customer_id) ? $customer_id : null,
			'employee_id'=>$employee_id,
			'payment_type'=>$payment_types,
			'comment'=>$comment
		);

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		$this->db->insert('sales_suspended',$sales_data);
		$sale_id = $this->db->insert_id();

		foreach($payments as $payment_id=>$payment)
		{
			$sales_payments_data = array
			(
				'sale_id'=>$sale_id,
				'payment_type'=>$payment['payment_type'],
				'payment_amount'=>$payment['payment_amount']
			);
			$this->db->insert('sales_suspended_payments',$sales_payments_data);
		}

		foreach($items as $line=>$item)
		{
			$cur_item_info = $this->Item->get_info($item['item_id']);

			$sales_items_data = array
			(
				'sale_id'=>$sale_id,
				'item_id'=>$item['item_id'],
				'line'=>$item['line'],
				'description'=>$item['description'],
				'serialnumber'=>$item['serialnumber'],
				'quantity_purchased'=>$item['quantity'],
				'discount_percent'=>$item['discount'],
				'item_cost_price' => $cur_item_info->cost_price,
				'item_unit_price'=>$item['price']
			);

			$this->db->insert('sales_suspended_items',$sales_items_data);

			$customer = $this->Customer->get_info($customer_id);
 			if ($customer_id == -1 or $customer->taxable)
 			{
				foreach($this->Item_taxes->get_info($item['item_id']) as $row)
				{
					$this->db->insert('sales_suspended_items_taxes', array(
						'sale_id' 	=>$sale_id,
						'item_id' 	=>$item['item_id'],
						'line'      =>$item['line'],
						'name'		=>$row['name'],
						'percent' 	=>$row['percent']
					));
				}
			}
		}
		$this->db->trans_complete();
		
		if ($this->db->trans_status() === FALSE)
		{
			return -1;
		}
		
		return $sale_id;
	}
	
	function delete($sale_id)
	{
		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();
		
		$this->db->delete('sales_suspended_payments', array('sale_id' => $sale_id)); 
		$this->db->delete('sales_suspended_items_taxes', array('sale_id' => $sale_id)); 
		$this->db->delete('sales_suspended_items', array('sale_id' => $sale_id)); 
		$this->db->delete('sales_suspended', array('sale_id' => $sale_id)); 
		
		$this->db->trans_complete();
				
		return $this->db->trans_status();
	}

	function get_sale_items($sale_id)
	{
		$this->db->from('sales_suspended_items');
		$this->db->where('sale_id',$sale_id);
		return $this->db->get();
	}

	function get_sale_payments($sale_id)
	{
		$this->db->from('sales_suspended_payments');
		$this->db->where('sale_id',$sale_id);
		return $this->db->get();
	}

	function get_customer($sale_id)
	{
		$this->db->from('sales_suspended');
		$this->db->where('sale_id',$sale_id);
		return $this->Customer->get_info($this->db->get()->row()->customer_id);
	}
	
	function get_comment($sale_id)
	{
		$this->db->from('sales_suspended');
		$this->db->where('sale_id',$sale_id);
		return $this->db->get()->row()->comment;
	}
}
?>
