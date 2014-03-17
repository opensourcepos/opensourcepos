<div id="edit_sale_wrapper">
	<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
	<ul id="error_message_box"></ul>
	
	<fieldset id="sale_basic_info">
	<?php echo form_open("sales/save/".$sale_info['sale_id'],array('id'=>'sales_edit_form')); ?>
	<legend><?php echo $this->lang->line("sales_basic_information"); ?></legend>
	
	<div class="field_row clearfix">
	<?php echo form_label($this->lang->line('sales_receipt').':', 'customer'); ?>
		<div class='form_field'>
			<?php echo anchor('sales/receipt/'.$sale_info['sale_id'], $this->lang->line('sales_receipt_number') .$sale_info['sale_id'], array('target' => '_blank'));?>
		</div>
	</div>
	
	<div class="field_row clearfix">
	<?php echo form_label($this->lang->line('sales_date').':', 'date', array('class'=>'required')); ?>
		<div class='form_field'>
			<?php echo form_input(array('name'=>'date','value'=>date('m/d/Y', strtotime($sale_info['sale_time'])), 'id'=>'date'));?>
		</div>
	</div>
	
	<div class="field_row clearfix">
	<?php echo form_label($this->lang->line('sales_customer').':', 'customer'); ?>
		<div class='form_field'>
			<?php echo form_input(array('name' => 'customer_id', 'value' => $selected_customer, 'id' => 'customer_id'));?>
		</div>
	</div>
	
	<div class="field_row clearfix">
	<?php echo form_label($this->lang->line('sales_employee').':', 'employee'); ?>
		<div class='form_field'>
			<?php echo form_dropdown('employee_id', $employees, $sale_info['employee_id'], 'id="employee_id"');?>
		</div>
	</div>
	
	<div class="field_row clearfix">
	<?php echo form_label($this->lang->line('sales_comment').':', 'comment'); ?>
		<div class='form_field'>
			<?php echo form_textarea(array('name'=>'comment','value'=>$sale_info['comment'],'rows'=>'4','cols'=>'23', 'id'=>'comment'));?>
		</div>
	</div>
	
	<?php
	echo form_submit(array(
		'name'=>'submit',
		'value'=>$this->lang->line('common_submit'),
		'class'=> 'submit_button float_right')
	);
	?>
	</form>
	
	<?php echo form_open("sales/delete/".$sale_info['sale_id'],array('id'=>'sales_delete_form')); ?>
		<?php echo form_hidden('sale_id', $sale_info['sale_id']);?>
		<?php
		echo form_submit(array(
			'name'=>'submit',
			'value'=>$this->lang->line('sales_delete_entire_sale'),
			'class'=>'delete_button float_right')
		);
		?>
	</form>
	</fieldset>
</div>

<script type="text/javascript" language="javascript">

$(document).ready(function()
{	
	$('#date').datePicker({startDate: '01/01/1970'});
	$("#sales_delete_form").submit(function()
	{
		if (!confirm('<?php echo $this->lang->line("sales_delete_confirmation"); ?>'))
		{
			return false;
		}
	});
	
	var format_item = function(row) {
    	var result = [row[0], "|", row[1]].join("");
    	// if more than one occurence
    	if (row[2] > 1 && row[3] && row[3].toString().trim()) {
			// display zip code
    		result += ' - ' + row[3];
    	}
		return result;
	};
	var autocompleter = $("#customer_id").autocomplete('<?php echo site_url("sales/customer_search"); ?>', {
    	minChars:0,
    	delay:15, 
    	max:100,
       	cacheLength: 1,
        formatItem: format_item,
        formatResult : format_item
    });

	// declare submitHandler as an object.. will be reused
	var submit_form = function(selected_customer) { 
		$(this).ajaxSubmit({
			success:function(response)
			{
				tb_remove();
				post_form_submit(response);
			},
			error: function(jqXHR, textStatus, errorThrown) {
				selected_customer && autocompleter.val(selected_customer);
				post_form_submit({message: errorThrown});
			},
			dataType:'json'
		});
	};
	$('#sales_edit_form').validate({
		submitHandler : function(form)
		{
			var selected_customer = autocompleter.val();
			var selected_customer_id = selected_customer.replace(/(\w)\|.*/, "$1");
			selected_customer_id && autocompleter.val(selected_customer_id);
			submit_form.call(form, selected_customer);
		},
		errorLabelContainer: "#error_message_box",
		wrapper: "li",
		rules: 
		{
			date: {
				required:true,
				date:true
			}
		},
		messages: 
		{
			date: {
				required: "<?= $this->lang->line('sales_date_required'); ?>",
				date: "<?= $this->lang->line('sales_date_type'); ?>"
			}
		}
	});
	$('#sales_delete_form').submit(function() {
		var id = $("input[name='sale_id']").val();
		$(this).ajaxSubmit({
			success:function(response)
			{
				tb_remove();
				set_feedback(response.message,'success_message',false);
				var $element = get_table_row(id).parent().parent();
				$element.find("td").animate({backgroundColor:"green"},1200,"linear")
				.end().animate({opacity:0},1200,"linear",function()
				{
					$element.next().remove();
					$(this).remove();
					//Re-init sortable table as we removed a row
					update_sortable_table();
				});
			},
			error: function(jqXHR, textStatus, errorThrown) {
				set_feedback(textStatus,'error_message',true);
			},
			dataType:'json'
		});
		return false;
	});
});
</script>