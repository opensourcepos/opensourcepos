<?php $this->load->view("partial/header"); ?>
<script type="text/javascript">
$(document).ready(function()
{
    init_table_sorting();
    enable_select_all();
    enable_checkboxes();
    enable_row_selection();
    enable_search('<?php echo site_url("$controller_name/suggest")?>','<?php echo $this->lang->line("common_confirm_search")?>');
    enable_delete('<?php echo $this->lang->line($controller_name."_confirm_delete")?>','<?php echo $this->lang->line($controller_name."_none_selected")?>');
    enable_bulk_edit('<?php echo $this->lang->line($controller_name."_none_selected")?>');

    $('#generate_barcodes').click(function()
    {
    	var selected = get_selected_values();
    	if (selected.length == 0)
    	{
    		alert('<?php echo $this->lang->line('items_must_select_item_for_barcode'); ?>');
    		return false;
    	}

    	$(this).attr('href','index.php/items/generate_barcodes/'+selected.join(':'));
    });

    $("#low_inventory").click(function()
    {
    	$('#items_filter_form').submit();
    });

    $("#is_serialized").click(function()
    {
    	$('#items_filter_form').submit();
    });

    $("#no_description").click(function()
    {
    	$('#items_filter_form').submit();
    });
//GARRISON ADDED 4/21/2013
    $("#search_custom").click(function()
    	    {
    	    	$('#items_filter_form').submit();
    	    });
    	    //END GARRISON ADDED
});

function init_table_sorting()
{
	//Only init if there is more than one row
	if($('.tablesorter tbody tr').length >1)
	{
		$("#sortable_table").tablesorter(
		{
			sortList: [[1,0]],
			headers:
			{
				0: { sorter: false},
				8: { sorter: false},
				9: { sorter: false}
			}

		});
	}
}

function post_item_form_submit(response)
{
	if(!response.success)
	{
		set_feedback(response.message,'error_message',true);
	}
	else
	{
		//This is an update, just update one row
		if(jQuery.inArray(response.item_id,get_visible_checkbox_ids()) != -1)
		{
			update_row(response.item_id,'<?php echo site_url("$controller_name/get_row")?>');
			set_feedback(response.message,'success_message',false);

		}
		else //refresh entire table
		{
			do_search(true,function()
			{
				//highlight new row
				hightlight_row(response.item_id);
				set_feedback(response.message,'success_message',false);
			});
		}
	}
}

function post_bulk_form_submit(response)
{
	if(!response.success)
	{
		set_feedback(response.message,'error_message',true);
	}
	else
	{
		var selected_item_ids=get_selected_values();
		for(k=0;k<selected_item_ids.length;k++)
		{
			update_row(selected_item_ids[k],'<?php echo site_url("$controller_name/get_row")?>');
		}
		set_feedback(response.message,'success_message',false);
	}
}

function show_hide_search_filter(search_filter_section, switchImgTag) {
        var ele = document.getElementById(search_filter_section);
        var imageEle = document.getElementById(switchImgTag);
        var elesearchstate = document.getElementById('search_section_state');
        if(ele.style.display == "block")
        {
                ele.style.display = "none";
				imageEle.innerHTML = '<img src=" <?php echo base_url()?>images/plus.png" style="border:0;outline:none;padding:0px;margin:0px;position:relative;top:-5px;" >';
                elesearchstate.value="none";
        }
        else
        {
                ele.style.display = "block";
                imageEle.innerHTML = '<img src=" <?php echo base_url()?>images/minus.png" style="border:0;outline:none;padding:0px;margin:0px;position:relative;top:-5px;" >';
                elesearchstate.value="block";
        }
}

</script>

<div id="title_bar">
	<div id="title" class="float_left"><?php echo $this->lang->line('common_list_of').' '.$this->lang->line('module_'.$controller_name); ?></div>
	<div id="new_button">
		<?php echo anchor("$controller_name/view/-1/width:$form_width",
		"<div class='big_button' style='float: left;'><span>".$this->lang->line($controller_name.'_new')."</span></div>",
		array('class'=>'thickbox none','title'=>$this->lang->line($controller_name.'_new')));
		?>
		<?php echo anchor("$controller_name/excel_import/width:$form_width",
		"<div class='big_button' style='float: left;'><span>Excel Import</span></div>",
		array('class'=>'thickbox none','title'=>'Import Items from Excel'));
		?>
	</div>
</div>

<div id="titleTextImg" style="background-color:#EEEEEE;height:20px;position:relative;">
	<div style="float:left;vertical-align:text-top;">Search Options :</div>
	<a id="imageDivLink" href="javascript:show_hide_search_filter('search_filter_section', 'imageDivLink');" style="outline:none;">
	<img src="
	<?php echo isset($search_section_state)?  ( ($search_section_state)? base_url().'images/minus.png' : base_url().'images/plus.png') : base_url().'images/plus.png';?>" style="border:0;outline:none;padding:0px;margin:0px;position:relative;top:-5px;"></a>
</div>

<div id="search_filter_section" style="display: <?php echo isset($search_section_state)?  ( ($search_section_state)? 'block' : 'none') : 'none';?>;background-color:#EEEEEE;">
	<?php echo form_open("$controller_name/refresh",array('id'=>'items_filter_form')); ?>
	<?php echo form_label($this->lang->line('items_low_inventory_items').' '.':', 'low_inventory');?>
	<?php echo form_checkbox(array('name'=>'low_inventory','id'=>'low_inventory','value'=>1,'checked'=> isset($low_inventory)?  ( ($low_inventory)? 1 : 0) : 0)).' | ';?>
	<?php echo form_label($this->lang->line('items_serialized_items').' '.':', 'is_serialized');?>
	<?php echo form_checkbox(array('name'=>'is_serialized','id'=>'is_serialized','value'=>1,'checked'=> isset($is_serialized)?  ( ($is_serialized)? 1 : 0) : 0)).' | ';?>
	<?php echo form_label($this->lang->line('items_no_description_items').' '.':', 'no_description');?>
	<?php echo form_checkbox(array('name'=>'no_description','id'=>'no_description','value'=>1,'checked'=> isset($no_description)?  ( ($no_description)? 1 : 0) : 0)).' | ';?>
	<?php echo form_label($this->lang->line('items_search_custom_items').' '.':', 'search_custom');//GARRISON ADDED 4/21/2013?>
	<?php echo form_checkbox(array('name'=>'search_custom','id'=>'search_custom','value'=>1,'checked'=> isset($search_custom)?  ( ($search_custom)? 1 : 0) : 0)).' | ';//GARRISON ADDED 4/21/2013?>
	<input type="hidden" name="search_section_state" id="search_section_state" value="<?php echo isset($search_section_state)?  ( ($search_section_state)? 'block' : 'none') : 'none';?>" />
	</form>
</div>
<?php echo $this->pagination->create_links();?>
<div id="table_action_header">
	<ul>
		<li class="float_left"><span><?php echo anchor("$controller_name/delete",$this->lang->line("common_delete"),array('id'=>'delete')); ?></span></li>
		<li class="float_left"><span><?php echo anchor("$controller_name/bulk_edit/width:$form_width",$this->lang->line("items_bulk_edit"),array('id'=>'bulk_edit','title'=>$this->lang->line('items_edit_multiple_items'))); ?></span></li>
		<li class="float_left"><span><?php echo anchor("$controller_name/generate_barcodes",$this->lang->line("items_generate_barcodes"),array('id'=>'generate_barcodes', 'target' =>'_blank','title'=>$this->lang->line('items_generate_barcodes'))); ?></span></li>
		<li class="float_right">
		<img src='<?php echo base_url()?>images/spinner_small.gif' alt='spinner' id='spinner' />
		<?php echo form_open("$controller_name/search",array('id'=>'search_form')); ?>
		<input type="text" name ='search' id='search'/>
		</form>
		</li>
	</ul>
</div>

<div id="table_holder">
<?php echo $manage_table; ?>
</div>
<div id="feedback_bar"></div>
<?php $this->load->view("partial/footer"); ?>