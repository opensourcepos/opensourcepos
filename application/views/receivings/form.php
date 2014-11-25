<div id="edit_sale_wrapper">
	<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
	<ul id="error_message_box"></ul>
	
	<fieldset id="receiving_basic_info">
	<?php echo form_open("receivings/save/".$receiving_info['receiving_id'],array('id'=>'recvs_edit_form')); ?>
	<legend><?php echo $this->lang->line("recvs_basic_information"); ?></legend>
	
	<div class="field_row clearfix">
	<?php echo form_label($this->lang->line('recvs_receipt_number').':', 'supplier'); ?>
		<div class='form_field'>
			<?php echo anchor('receivings/receipt/'.$receiving_info['receiving_id'], $this->lang->line('recvs_receipt_number') .$receiving_info['receiving_id'], array('target' => '_blank'));?>
		</div>
	</div>
	
	<div class="field_row clearfix">
	<?php echo form_label($this->lang->line('recvs_date').':', 'date', array('class'=>'required')); ?>
		<div class='form_field'>
			<?php echo form_input(array('name'=>'date','value'=>date('m/d/Y', strtotime($receiving_info['receiving_time'])), 'id'=>'date'));?>
		</div>
	</div>
	
	<div class="field_row clearfix">
	<?php echo form_label($this->lang->line('recvs_supplier').':', 'supplier'); ?>
		<div class='form_field'>
			<?php echo form_input(array('name' => 'supplier_id', 'value' => $selected_supplier, 'id' => 'supplier_id'));?>
		</div>
	</div>
	
	<div class="field_row clearfix">
	<?php echo form_label($this->lang->line('recvs_invoice_number').':', 'invoice_number'); ?>
		<div class='form_field'>
			<?php echo form_input(array('name' => 'invoice_number', 'value' => $receiving_info['invoice_number'], 'id' => 'invoice_number'));?>
		</div>
	</div>
	
	<div class="field_row clearfix">
	<?php echo form_label($this->lang->line('recvs_employee').':', 'employee'); ?>
		<div class='form_field'>
			<?php echo form_dropdown('employee_id', $employees, $receiving_info['employee_id'], 'id="employee_id"');?>
		</div>
	</div>
	
	<div class="field_row clearfix">
	<?php echo form_label($this->lang->line('recvs_comments').':', 'comment'); ?>
		<div class='form_field'>
			<?php echo form_textarea(array('name'=>'comment','value'=>$receiving_info['comment'],'rows'=>'4','cols'=>'23', 'id'=>'comment'));?>
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
	
	<?php echo form_open("receivings/delete/".$receiving_info['receiving_id'],array('id'=>'recvs_delete_form')); ?>
		<?php echo form_hidden('receiving_id', $receiving_info['receiving_id']);?>
		<?php
		echo form_submit(array(
			'name'=>'submit',
			'value'=>$this->lang->line('recvs_delete_entire_sale'),
			'class'=>'delete_button float_right')
		);
		?>
	</form>
	</fieldset>
</div>

<script type="text/javascript" language="javascript">

$(document).ready(function()
{	
	$.validator.addMethod("invoice_number", function(value, element) 
	{
		var id = $("input[name='receiving_id']").val();

		return JSON.parse($.ajax(
		{
			  type: 'POST',
			  url: '<?php echo site_url($controller_name . "/check_invoice_number")?>',
			  data: {'receiving_id' : id, 'invoice_number' : $(element).val() },
			  success: function(response) 
			  {
				  success=response.success;
			  },
			  async:false,
			  dataType: 'json'
        }).response).success;
    }, '<?php echo $this->lang->line("recvs_invoice_number_duplicate"); ?>');
	
	$('#date').datePicker({startDate: '<?php echo date("%Y/%M/%d");?>'});
	$("#recvs_delete_form").submit(function()
	{
		if (!confirm('<?php echo $this->lang->line("recvs_delete_confirmation"); ?>'))
		{
			return false;
		}
	});
	
	var format_item = function(row) 
	{
    	var result = [row[0], "|", row[1]].join("");
    	// if more than one occurence
    	if (row[2] > 1 && row[3] && row[3].toString().trim()) {
			// display zip code
    		result += ' - ' + row[3];
    	}
		return result;
	};
	var autocompleter = $("#supplier_id").autocomplete('<?php echo site_url("receivings/supplier_search"); ?>', 
	{
    	minChars:0,
    	delay:15, 
    	max:100,
       	cacheLength: 1,
        formatItem: format_item,
        formatResult : format_item
    });

	// declare submitHandler as an object.. will be reused
	var submit_form = function(selected_supplier) 
	{ 
		$(this).ajaxSubmit({
			success:function(response)
			{
				tb_remove();
				post_form_submit(response);
			},
			error: function(jqXHR, textStatus, errorThrown) {
				selected_supplier && autocompleter.val(selected_supplier);
				post_form_submit({message: errorThrown});
			},
			dataType:'json'
		});
	};
	$('#recvs_edit_form').validate(
	{
		submitHandler : function(form)
		{
			var selected_supplier = autocompleter.val();
			var selected_supplier_id = selected_supplier.replace(/(\w)\|.*/, "$1");
			selected_supplier_id && autocompleter.val(selected_supplier_id);
			submit_form.call(form, selected_supplier);
		},
		errorLabelContainer: "#error_message_box",
		wrapper: "li",
		rules: 
		{
			date: {
				required:true,
				date:true
			},
			invoice_number: {
				invoice_number: true
			}
		},
		messages: 
		{
			date: {
				required: "<?= $this->lang->line('recvs_date_required'); ?>",
				date: "<?= $this->lang->line('recvs_date_type'); ?>"
			}
		}
	});
	$('#recvs_delete_form').submit(function() 
	{
		var id = $("input[name='receiving_id']").val();
		$(this).ajaxSubmit(
		{
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