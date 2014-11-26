<?php
require_once ("secure_area.php");
require_once ("interfaces/idata_controller.php");
class Items extends Secure_area implements iData_controller
{
	function __construct()
	{
		parent::__construct('items');
		$this->load->library('item_lib');
	}

	function index()
	{
		$config['base_url'] = site_url('/items/index');
		$config['total_rows'] = $this->Item->count_all();
		$config['per_page'] = '20';
		$config['uri_segment'] = 3;
		$this->pagination->initialize($config);
		
		$stock_location=$this->item_lib->get_item_location();
		$stock_locations=$this->Stock_locations->get_allowed_locations();
		$data['stock_location']=$stock_location;
		$data['stock_locations']=$stock_locations;
		$data['controller_name']=strtolower(get_class());
		$data['form_width']=$this->get_form_width();
		$data['manage_table']=get_items_manage_table( $this->Item->get_all( $stock_location, $config['per_page'], $this->uri->segment( $config['uri_segment'] ) ), $this );
		$this->load->view('items/manage',$data);
	}

	function refresh()
	{
		$is_serialized=$this->input->post('is_serialized');
		$no_description=$this->input->post('no_description');
		$search_custom=$this->input->post('search_custom');//GARRISON ADDED 4/13/2013    
        $is_deleted=$this->input->post('is_deleted'); // Parq 131215
        $this->item_lib->set_item_location($this->input->post('stock_location'));
        
       	$stock_location=$this->input->post('stock_location');
		$stock_locations=$this->Stock_locations->get_allowed_locations();
		$data['stock_location']=$this->item_lib->get_item_location();
		$data['stock_locations']=$stock_locations;
        
		$data['search_section_state']=$this->input->post('search_section_state');
		$data['is_serialized']=$this->input->post('is_serialized');
		$data['no_description']=$this->input->post('no_description');
		$data['search_custom']=$this->input->post('search_custom');//GARRISON ADDED 4/13/2013
		$data['is_deleted']=$this->input->post('is_deleted'); // Parq 131215
		$data['controller_name']=strtolower(get_class());
		$data['form_width']=$this->get_form_width(); 
		$data['manage_table']=get_items_manage_table($this->Item->get_all_filtered($stock_location,$is_serialized,$no_description,$search_custom,$is_deleted),$this);//GARRISON MODIFIED 4/13/2013, Parq 131215
		$this->load->view('items/manage',$data);
	}

	function find_item_info()
	{
		$item_number=$this->input->post('scan_item_number');
		echo json_encode($this->Item->find_item_info($item_number));
	}

	function search()
	{
		$search=$this->input->post('search');
		$stock_location=$this->item_lib->get_item_location();
		$data_rows=get_items_manage_table_data_rows($this->Item->search($search,$stock_location),$this);
		echo $data_rows;
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$suggestions = $this->Item->get_search_suggestions($this->input->post('q'),$this->input->post('limit'));
		echo implode("\n",$suggestions);
	}
	
	function item_search()
	{
		$suggestions = $this->Item->get_item_search_suggestions($this->input->post('q'),$this->input->post('limit'),'warehouse');
		echo implode("\n",$suggestions);
	}
	
	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest_category()
	{
		$suggestions = $this->Item->get_category_suggestions($this->input->post('q'));
		echo implode("\n",$suggestions);
	}

/**GARRISON ADDED 5/18/2013**/	
	/*
	 Gives search suggestions based on what is being searched for
	*/
	function suggest_location()
	{
		$suggestions = $this->Item->get_location_suggestions($this->input->post('q'));
		echo implode("\n",$suggestions);
	}
	
	/*
	 Gives search suggestions based on what is being searched for
	*/
	function suggest_custom1()
	{
		$suggestions = $this->Item->get_custom1_suggestions($this->input->post('q'));
		echo implode("\n",$suggestions);
	}
	
	/*
	 Gives search suggestions based on what is being searched for
	*/
	function suggest_custom2()
	{
		$suggestions = $this->Item->get_custom2_suggestions($this->input->post('q'));
		echo implode("\n",$suggestions);
	}
	
	/*
	 Gives search suggestions based on what is being searched for
	*/
	function suggest_custom3()
	{
		$suggestions = $this->Item->get_custom3_suggestions($this->input->post('q'));
		echo implode("\n",$suggestions);
	}
	
	/*
	 Gives search suggestions based on what is being searched for
	*/
	function suggest_custom4()
	{
		$suggestions = $this->Item->get_custom4_suggestions($this->input->post('q'));
		echo implode("\n",$suggestions);
	}
	
	/*
	 Gives search suggestions based on what is being searched for
	*/
	function suggest_custom5()
	{
		$suggestions = $this->Item->get_custom5_suggestions($this->input->post('q'));
		echo implode("\n",$suggestions);
	}
	
	/*
	 Gives search suggestions based on what is being searched for
	*/
	function suggest_custom6()
	{
		$suggestions = $this->Item->get_custom6_suggestions($this->input->post('q'));
		echo implode("\n",$suggestions);
	}
	
	/*
	 Gives search suggestions based on what is being searched for
	*/
	function suggest_custom7()
	{
		$suggestions = $this->Item->get_custom7_suggestions($this->input->post('q'));
		echo implode("\n",$suggestions);
	}
	
	/*
	 Gives search suggestions based on what is being searched for
	*/
	function suggest_custom8()
	{
		$suggestions = $this->Item->get_custom8_suggestions($this->input->post('q'));
		echo implode("\n",$suggestions);
	}
	
	/*
	 Gives search suggestions based on what is being searched for
	*/
	function suggest_custom9()
	{
		$suggestions = $this->Item->get_custom9_suggestions($this->input->post('q'));
		echo implode("\n",$suggestions);
	}
	
	/*
	 Gives search suggestions based on what is being searched for
	*/
	function suggest_custom10()
	{
		$suggestions = $this->Item->get_custom10_suggestions($this->input->post('q'));
		echo implode("\n",$suggestions);
	}

/**END GARRISON ADDED**/
		
	function get_row()
	{
		$item_id = $this->input->post('row_id');
		$item_info = $this->Item->get_info($item_id);
		$stock_location = $this->item_lib->get_item_location();
		$item_quantity = $this->Item_quantities->get_item_quantity($item_id,$stock_location);
		$item_info->quantity = $item_quantity->quantity; 
		$data_row=get_item_data_row($item_info,$this);
		
		echo $data_row;
	}

	function view($item_id=-1)
	{
		$data['item_info']=$this->Item->get_info($item_id);
		$data['item_tax_info']=$this->Item_taxes->get_info($item_id);
		$suppliers = array('' => $this->lang->line('items_none'));
		foreach($this->Supplier->get_all()->result_array() as $row)
		{
			$suppliers[$row['person_id']] = $row['company_name'] .' ('.$row['first_name'] .' '. $row['last_name'].')';
		}

		$data['suppliers']=$suppliers;
		$data['selected_supplier'] = $this->Item->get_info($item_id)->supplier_id;
		$data['default_tax_1_rate']=($item_id==-1) ? $this->Appconfig->get('default_tax_1_rate') : '';
		$data['default_tax_2_rate']=($item_id==-1) ? $this->Appconfig->get('default_tax_2_rate') : '';
        
        $locations_data = $this->Stock_locations->get_undeleted_all()->result_array();
        foreach($locations_data as $location)
        {
           $quantity = $this->Item_quantities->get_item_quantity($item_id,$location['location_id'])->quantity;
           $quantity = ($item_id == -1) ? null: $quantity;
           $location_array[$location['location_id']] =  array('location_name'=>$location['location_name'],
                                                                       'quantity'=>$quantity);
           $data['stock_locations']= $location_array;
        }
		$this->load->view("items/form",$data);
	}

    
	//Ramel Inventory Tracking
	function inventory($item_id=-1)
	{
		$data['item_info']=$this->Item->get_info($item_id);
        
        $data['stock_locations'] = array();
        $stock_locations = $this->Stock_locations->get_undeleted_all()->result_array();          
        foreach($stock_locations as $location_data)
        {            
            $data['stock_locations'][$location_data['location_id']] = $location_data['location_name'];
            $data['item_quantities'][$location_data['location_id']] = $this->Item_quantities->get_item_quantity($item_id,$location_data['location_id'])->quantity;
        }     
        
		$this->load->view("items/inventory",$data);
	}
	
	function count_details($item_id=-1)
	{
		$data['item_info']=$this->Item->get_info($item_id);
        
        $data['stock_locations'] = array();
        $stock_locations = $this->Stock_locations->get_undeleted_all()->result_array();   
        foreach($stock_locations as $location_data)
        {            
            $data['stock_locations'][$location_data['location_id']] = $location_data['location_name'];
            $data['item_quantities'][$location_data['location_id']] = $this->Item_quantities->get_item_quantity($item_id,$location_data['location_id'])->quantity;
        }     
                
		$this->load->view("items/count_details",$data);
	} //------------------------------------------- Ramel

	function generate_barcodes($item_ids)
	{
		$result = array();

		$item_ids = explode(':', $item_ids);
		foreach ($item_ids as $item_id)
		{
			$item_info = $this->Item->get_info($item_id);

			$result[] = array('name' =>$item_info->name, 'id'=> $item_id);
		}

		$data['items'] = $result;
		$this->load->view("barcode_sheet", $data);
	}

	function bulk_edit()
	{
		$data = array();
		$suppliers = array('' => $this->lang->line('items_none'));
		foreach($this->Supplier->get_all()->result_array() as $row)
		{
			$suppliers[$row['person_id']] = $row['first_name'] .' '. $row['last_name'];
		}
		$data['suppliers'] = $suppliers;
		$data['allow_alt_description_choices'] = array(
			''=>$this->lang->line('items_do_nothing'), 
			1 =>$this->lang->line('items_change_all_to_allow_alt_desc'),
			0 =>$this->lang->line('items_change_all_to_not_allow_allow_desc'));
				
		$data['serialization_choices'] = array(
			''=>$this->lang->line('items_do_nothing'), 
			1 =>$this->lang->line('items_change_all_to_serialized'),
			0 =>$this->lang->line('items_change_all_to_unserialized'));
		$this->load->view("items/form_bulk", $data);
	}

	function save($item_id=-1)
	{
        //Save item data
		$item_data = array(
		'name'=>$this->input->post('name'),
		'description'=>$this->input->post('description'),
		'category'=>$this->input->post('category'),
		'supplier_id'=>$this->input->post('supplier_id')=='' ? null:$this->input->post('supplier_id'),
		'item_number'=>$this->input->post('item_number')=='' ? null:$this->input->post('item_number'),
		'cost_price'=>$this->input->post('cost_price'),
		'unit_price'=>$this->input->post('unit_price'),
		'reorder_level'=>$this->input->post('reorder_level'),
		'receiving_quantity'=>$this->input->post('receiving_quantity'),
		'allow_alt_description'=>$this->input->post('allow_alt_description'),
		'is_serialized'=>$this->input->post('is_serialized'),
		'deleted'=>$this->input->post('is_deleted'),  /** Parq 131215 **/
		'custom1'=>$this->input->post('custom1'),	/**GARRISON ADDED 4/21/2013**/			
		'custom2'=>$this->input->post('custom2'),/**GARRISON ADDED 4/21/2013**/
		'custom3'=>$this->input->post('custom3'),/**GARRISON ADDED 4/21/2013**/
		'custom4'=>$this->input->post('custom4'),/**GARRISON ADDED 4/21/2013**/
		'custom5'=>$this->input->post('custom5'),/**GARRISON ADDED 4/21/2013**/
		'custom6'=>$this->input->post('custom6'),/**GARRISON ADDED 4/21/2013**/
		'custom7'=>$this->input->post('custom7'),/**GARRISON ADDED 4/21/2013**/
		'custom8'=>$this->input->post('custom8'),/**GARRISON ADDED 4/21/2013**/
		'custom9'=>$this->input->post('custom9'),/**GARRISON ADDED 4/21/2013**/
		'custom10'=>$this->input->post('custom10')/**GARRISON ADDED 4/21/2013**/
		);
		
		$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
		$cur_item_info = $this->Item->get_info($item_id);


		if($this->Item->save($item_data,$item_id))
		{
			//New item
			if($item_id==-1)
			{
				echo json_encode(array('success'=>true,'message'=>$this->lang->line('items_successful_adding').' '.
				$item_data['name'],'item_id'=>$item_data['item_id']));
				$item_id = $item_data['item_id'];
			}
			else //previous item
			{
				echo json_encode(array('success'=>true,'message'=>$this->lang->line('items_successful_updating').' '.
				$item_data['name'],'item_id'=>$item_id));
			}
			
			$items_taxes_data = array();
			$tax_names = $this->input->post('tax_names');
			$tax_percents = $this->input->post('tax_percents');
			for($k=0;$k<count($tax_percents);$k++)
			{
				if (is_numeric($tax_percents[$k]))
				{
					$items_taxes_data[] = array('name'=>$tax_names[$k], 'percent'=>$tax_percents[$k] );
				}
			}
			$this->Item_taxes->save($items_taxes_data, $item_id);

            
            //Save item quantity
            $stock_locations = $this->Stock_locations->get_undeleted_all()->result_array();          
            foreach($stock_locations as $location_data)
            {
                $updated_quantity = $this->input->post($location_data['location_id'].'_quantity');
                $location_detail = array('item_id'=>$item_id,
                                        'location_id'=>$location_data['location_id'],
                                        'quantity'=>$updated_quantity);  
                $item_quantity = $this->Item_quantities->get_item_quantity($item_id, $location_data['location_id']);
                if ($item_quantity->quantity != $updated_quantity) 
                {              
	                $this->Item_quantities->save($location_detail, $item_id, $location_data['location_id']);
	                
	                $inv_data = array
	                (
	                    'trans_date'=>date('Y-m-d H:i:s'),
	                    'trans_items'=>$item_id,
	                    'trans_user'=>$employee_id,
	                    'trans_location'=>$location_data['location_id'],
	                    'trans_comment'=>$this->lang->line('items_manually_editing_of_quantity'),
	                    'trans_inventory'=>$updated_quantity - $item_quantity->quantity
	                );
	                $this->Inventory->insert($inv_data);       
                }                                            
            }        
		}
		else//failure
		{
			echo json_encode(array('success'=>false,'message'=>$this->lang->line('items_error_adding_updating').' '.
			$item_data['name'],'item_id'=>-1));
		}

	}
	
	//Ramel Inventory Tracking
	function save_inventory($item_id=-1)
	{	
		$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
		$cur_item_info = $this->Item->get_info($item_id);
        $location_id = $this->input->post('stock_location');
		$inv_data = array
		(
			'trans_date'=>date('Y-m-d H:i:s'),
			'trans_items'=>$item_id,
			'trans_user'=>$employee_id,
			'trans_location'=>$location_id,
			'trans_comment'=>$this->input->post('trans_comment'),
			'trans_inventory'=>$this->input->post('newquantity')
		);
		$this->Inventory->insert($inv_data);
		
		//Update stock quantity
		
		$item_quantity= $this->Item_quantities->get_item_quantity($item_id,$location_id);
		$item_quantity_data = array(
		'item_id'=>$item_id,
		'location_id'=>$location_id,
		'quantity'=>$item_quantity->quantity + $this->input->post('newquantity')
		);
		if($this->Item_quantities->save($item_quantity_data,$item_id,$location_id))
		{			
			echo json_encode(array('success'=>true,'message'=>$this->lang->line('items_successful_updating').' '.
			$cur_item_info->name,'item_id'=>$item_id));
		}
		else//failure
		{	
			echo json_encode(array('success'=>false,'message'=>$this->lang->line('items_error_adding_updating').' '.
			$cur_item_info->name,'item_id'=>-1));
		}

	}//---------------------------------------------------------------------Ramel

	function bulk_update()
	{
		$items_to_update=$this->input->post('item_ids');
		$item_data = array();

		foreach($_POST as $key=>$value)
		{
			//This field is nullable, so treat it differently
			if ($key == 'supplier_id')
			{
				$item_data["$key"]=$value == '' ? null : $value;
			}
			elseif($value!='' and !(in_array($key, array('item_ids', 'tax_names', 'tax_percents'))))
			{
				$item_data["$key"]=$value;
			}
		}

		//Item data could be empty if tax information is being updated
		if(empty($item_data) || $this->Item->update_multiple($item_data,$items_to_update))
		{
			$items_taxes_data = array();
			$tax_names = $this->input->post('tax_names');
			$tax_percents = $this->input->post('tax_percents');
			for($k=0;$k<count($tax_percents);$k++)
			{
				if (is_numeric($tax_percents[$k]))
				{
					$items_taxes_data[] = array('name'=>$tax_names[$k], 'percent'=>$tax_percents[$k] );
				}
			}
			$this->Item_taxes->save_multiple($items_taxes_data, $items_to_update);

			echo json_encode(array('success'=>true,'message'=>$this->lang->line('items_successful_bulk_edit')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>$this->lang->line('items_error_updating_multiple')));
		}
	}

	function delete()
	{
		$items_to_delete=$this->input->post('ids');

		if($this->Item->delete_list($items_to_delete))
		{
			echo json_encode(array('success'=>true,'message'=>$this->lang->line('items_successful_deleted').' '.
			count($items_to_delete).' '.$this->lang->line('items_one_or_multiple')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>$this->lang->line('items_cannot_be_deleted')));
		}
	}
	
	function excel()
	{
		$data = file_get_contents("import_items.csv");
		$name = 'import_items.csv';
		force_download($name, $data);
	}
	
	function excel_import()
	{
		$this->load->view("items/excel_import", null);
	}

	function do_excel_import()
	{
		$msg = 'do_excel_import';
		$failCodes = array();
		if ($_FILES['file_path']['error']!=UPLOAD_ERR_OK)
		{
			$msg = $this->lang->line('items_excel_import_failed');
			echo json_encode( array('success'=>false,'message'=>$msg) );
			return;
		}
		else
		{
			if (($handle = fopen($_FILES['file_path']['tmp_name'], "r")) !== FALSE)
			{
				//Skip first row
				fgetcsv($handle);
				
				$i=1;
				while (($data = fgetcsv($handle)) !== FALSE)
				{
                                    
					$item_data = array(
					'name'			=>	$data[1],
					'description'	=>	$data[11],
					'category'		=>	$data[2],
					'cost_price'	=>	$data[4],
					'unit_price'	=>	$data[5],
					'reorder_level'	=>	$data[10],
					'supplier_id'	=>  $this->Supplier->exists($data[3]) ? $data[3] : null,
					'allow_alt_description'	=>	$data[12] != '' ? '1' : '0',
					'is_serialized'	=>	$data[13] != '' ? '1' : '0',
					'custom1'		=>	$data[14],	/** GARRISON ADDED 5/6/2013 **/
					'custom2'		=>	$data[15],	/** GARRISON ADDED 5/6/2013 **/
					'custom3'		=>	$data[16],	/** GARRISON ADDED 5/6/2013 **/
					'custom4'		=>	$data[17],	/** GARRISON ADDED 5/6/2013 **/
					'custom5'		=>	$data[18],	/** GARRISON ADDED 5/6/2013 **/
					'custom6'		=>	$data[19],	/** GARRISON ADDED 5/6/2013 **/
					'custom7'		=>	$data[20],	/** GARRISON ADDED 5/6/2013 **/
					'custom8'		=>	$data[21],	/** GARRISON ADDED 5/6/2013 **/
					'custom9'		=>	$data[22],	/** GARRISON ADDED 5/6/2013 **/
					'custom10'		=>	$data[23]	/** GARRISON ADDED 5/6/2013 **/
					);
					$item_number = $data[0];
					
					if ($item_number != "")
					{
						$item_data['item_number'] = $item_number;
					}
					
					if($this->Item->save($item_data)) 
					{
						$items_taxes_data = null;
						//tax 1
						if( is_numeric($data[7]) && $data[6]!='' )
						{
							$items_taxes_data[] = array('name'=>$data[6], 'percent'=>$data[7] );
						}

						//tax 2
						if( is_numeric($data[9]) && $data[8]!='' )
						{
							$items_taxes_data[] = array('name'=>$data[8], 'percent'=>$data[9] );
						}

						// save tax values
						if(count($items_taxes_data) > 0)
						{
							$this->Item_taxes->save($items_taxes_data, $item_data['item_id']);
						}
                                                
                                                // quantities                                                
                                                $cols = count($data);
                                                for ($col = 24; $col < $cols; $col = $col + 2)
                                                {
                                                    $item_quantity_data = array(
                                                        'item_id' => $item_data['item_id'],
                                                        'location_id' => $data[$col],
                                                        'quantity' => $data[$col + 1],
                                                    );
                                                    $this->Item_quantities->save($item_quantity_data, $data[$col], $data[$col + 1]);
                                                }



                                                // Inventory Info
                                                
						
							$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
							$emp_info=$this->Employee->get_info($employee_id);
							$comment ='Qty CSV Imported';
							$excel_data = array
								(
								'trans_items'=>$item_data['item_id'],
								'trans_user'=>$employee_id,
								'trans_comment'=>$comment,
								'trans_inventory'=>$data[10]
								);
								$this->db->insert('inventory',$excel_data);
						//------------------------------------------------Ramel
					}
					else//insert or update item failure
					{
						$failCodes[] = $i;
					}
				}
				
				$i++;
			}
			else 
			{
				echo json_encode( array('success'=>false,'message'=>'Your upload file has no data or not in supported format.') );
				return;
			}
		}

		$success = true;
		if(count($failCodes) > 1)
		{
			$msg = "Most items imported. But some were not, here is list of their CODE (" .count($failCodes) ."): ".implode(", ", $failCodes);
			$success = false;
		}
		else
		{
			$msg = "Import items successful";
		}

		echo json_encode( array('success'=>$success,'message'=>$msg) );
	}

	/*
	get the width for the add/edit form
	*/
	function get_form_width()
	{
		return 360;
	}
    
	function item_number_check($item_number)
	{
		if ($this->Item->get_item_id($item_number) != FALSE)
		{
			$this->form_validation->set_message('item_number_check', $this->lang->line('items_item_number_exists'));
			echo json_encode(array('success'=>false,'message'=>$this->lang->line('items_error_adding')));
		}
		echo json_encode(array('success'=>true,'message'=>$this->lang->line('items_successful_adding')));
	}
    
}
?>
