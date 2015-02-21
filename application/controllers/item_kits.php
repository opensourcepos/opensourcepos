<?php
require_once ("secure_area.php");
require_once ("interfaces/idata_controller.php");
class Item_kits extends Secure_area implements iData_controller
{
	function __construct()
	{
		parent::__construct('item_kits');
	}
	
	function index($limit_from=0)
	{
		$data['controller_name']=$this->get_controller_name();
		$data['form_width']=$this->get_form_width();
		$lines_per_page = $this->Appconfig->get('lines_per_page');
		$item_kits = $this->Item_kit->get_all($lines_per_page,$limit_from);
		$data['links'] = $this->_initialize_pagination($this->Item_kit,$lines_per_page,$limit_from);
		$data['manage_table']=get_item_kits_manage_table($item_kits,$this);
		$this->load->view('item_kits/manage',$data);
	}
	
	function search()
	{
		$search = $this->input->post('search');
		$limit_from = $this->input->post('limit_from');
		$lines_per_page = $this->Appconfig->get('lines_per_page');
		$customers = $this->Item_kit->search($search, $lines_per_page, $limit_from);
		$total_rows = $this->Item_kit->get_found_rows($search);
		$links = $this->_initialize_pagination($this->Item_kit,$lines_per_page, $limit_from, $total_rows);
		$data_rows=get_item_kits_manage_table_data_rows($customers,$this);
		echo json_encode(array('total_rows' => $total_rows, 'rows' => $data_rows, 'pagination' => $links));
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$suggestions = $this->Item_kit->get_search_suggestions($this->input->post('q'),$this->input->post('limit'));
		echo implode("\n",$suggestions);
	}

	function get_row()
	{
		$item_kit_id = $this->input->post('row_id');
		$data_row=get_item_kit_data_row($this->Item_kit->get_info($item_kit_id),$this);
		echo $data_row;
	}

	function view($item_kit_id=-1)
	{
		$data['item_kit_info']=$this->Item_kit->get_info($item_kit_id);
		$this->load->view("item_kits/form",$data);
	}
	
	function save($item_kit_id=-1)
	{
		$item_kit_data = array(
		'name'=>$this->input->post('name'),
		'description'=>$this->input->post('description')
		);
		
		if($this->Item_kit->save($item_kit_data,$item_kit_id))
		{
			//New item kit
			if($item_kit_id==-1)
			{
				echo json_encode(array('success'=>true,'message'=>$this->lang->line('item_kits_successful_adding').' '.
				$item_kit_data['name'],'item_kit_id'=>$item_kit_data['item_kit_id']));
				$item_kit_id = $item_kit_data['item_kit_id'];
			}
			else //previous item
			{
				echo json_encode(array('success'=>true,'message'=>$this->lang->line('item_kits_successful_updating').' '.
				$item_kit_data['name'],'item_kit_id'=>$item_kit_id));
			}
			
			if ($this->input->post('item_kit_item'))
			{
				$item_kit_items = array();
				foreach($this->input->post('item_kit_item') as $item_id => $quantity)
				{
					$item_kit_items[] = array(
						'item_id' => $item_id,
						'quantity' => $quantity
						);
				}
			
				$this->Item_kit_items->save($item_kit_items, $item_kit_id);
			}
		}
		else//failure
		{
			echo json_encode(array('success'=>false,'message'=>$this->lang->line('item_kits_error_adding_updating').' '.
			$item_kit_data['name'],'item_kit_id'=>-1));
		}

	}
	
	function delete()
	{
		$item_kits_to_delete=$this->input->post('ids');

		if($this->Item_kit->delete_list($item_kits_to_delete))
		{
			echo json_encode(array('success'=>true,'message'=>$this->lang->line('item_kits_successful_deleted').' '.
			count($item_kits_to_delete).' '.$this->lang->line('item_kits_one_or_multiple')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>$this->lang->line('item_kits_cannot_be_deleted')));
		}
	}
	
	function generate_barcodes($item_kit_ids)
	{
		$this->load->library('barcode_lib');
		$result = array();

		$item_kit_ids = explode(':', $item_kit_ids);
		foreach ($item_kit_ids as $item_kid_id)
		{
			$item_kit_info = $this->Item_kit->get_info($item_kid_id);

			$result[] = array('name' =>$item_kit_info->name, 'item_id'=> 'KIT '.$item_kid_id, 'item_number'=>'KIT '.$item_kid_id);
		}

		$data['items'] = $result;
        $data['configs'] = $this->Appconfig->get_all();
        $data['barcode_config'] = $this->barcode_lib->get_barcode_config();
		$this->load->view("barcode_sheet", $data);
	}
	
	
	/*
	get the width for the add/edit form
	*/
	function get_form_width()
	{
		return 400;
	}
}
?>