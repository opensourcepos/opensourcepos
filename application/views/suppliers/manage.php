<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
$(document).ready(function() 
{ 
    init_table_sorting();
    enable_select_all();
    enable_row_selection();
    enable_search({suggest_url : '<?php echo site_url("$controller_name/suggest")?>', confirm_search_message : '<?php echo $this->lang->line("common_confirm_search")?>'});
    enable_email('<?php echo site_url("$controller_name/mailto")?>');
    enable_delete('<?php echo $this->lang->line($controller_name."_confirm_delete")?>','<?php echo $this->lang->line($controller_name."_none_selected")?>');
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
				6: { sorter: false} 
			} 

		}); 
	}
}

function post_person_form_submit(response)
{
	if(!response.success)
	{
		set_feedback(response.message, 'alert alert-dismissible alert-danger', true);	
	}
	else
	{
		//This is an update, just update one row
		if(jQuery.inArray(response.person_id,get_visible_checkbox_ids()) != -1)
		{
			update_row(response.person_id,'<?php echo site_url("$controller_name/get_row")?>');
			set_feedback(response.message, 'alert alert-dismissible alert-success', false);	
			
		}
		else //refresh entire table
		{
			do_search(true,function()
			{
				//highlight new row
				hightlight_row(response.person_id);
				set_feedback(response.message, 'alert alert-dismissible alert-success', false);		
			});
		}
	}
}
</script>

<div id="title_bar">
	<div id="title" class="float_left"><?php echo $this->lang->line('common_list_of').' '.$this->lang->line('module_'.$controller_name); ?></div>
	<?php echo anchor("$controller_name/view/-1",
		"<div class='btn btn-info btn-sm pull-right'><span>" . $this->lang->line($controller_name . '_new') . "</span></div>",
		array('class'=>'modal-dlg modal-btn-submit none', 'title'=>$this->lang->line($controller_name . '_new'))); ?>
</div>

<div id="pagination"><?= $links ?></div>

<?php echo form_open("$controller_name/search", array('id'=>'search_form', 'class'=>'form-group')); ?>
	<div id="table_action_header">
		<ul>
			<li class="float_left"><?php echo anchor("$controller_name/delete", '<div class="btn btn-default btn-sm"><span>' . $this->lang->line("common_delete") . '</span></div>', array('id'=>'delete')); ?></li>
			<li class="float_left"><span><a href="#" id="email"><div class="btn btn-default btn-sm"><?php echo $this->lang->line("common_email");?></div></a></span></li>
			<li class="float_right">
				<img src='<?php echo base_url()?>images/spinner_small.gif' alt='spinner' id='spinner' />
				<input type="text" name ='search' id='search', class='form-control input-sm' />
				<input type="hidden" name ='limit_from' id='limit_from' />
			</li>
		</ul>
	</div>
<?php echo form_close(); ?>

<div id="table_holder">
	<?php echo $manage_table; ?>
</div>

<?php $this->load->view("partial/footer"); ?>