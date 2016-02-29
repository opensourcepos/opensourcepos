<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<div id="edit_sale_wrapper">
	<fieldset id="receiving_basic_info">
		<?php echo form_open("receivings/save/".$receiving_info['receiving_id'], array('id'=>'recvs_edit_form', 'class'=>'form-horizontal')); ?>		
			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('recvs_receipt_number'), 'supplier', array('class'=>'control-label col-xs-3')); ?>
				<div class='col-xs-6'>
					<?php echo anchor('receivings/receipt/'.$receiving_info['receiving_id'], $this->lang->line('recvs_receipt_number') .$receiving_info['receiving_id'], array('target' => '_blank'));?>
				</div>
			</div>
			
			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('recvs_date'), 'date', array('class'=>'control-label col-xs-3')); ?>
				<div class='col-xs-6'>
					<?php echo form_input(array('name'=>'date','value'=>date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), strtotime($receiving_info['receiving_time'])), 'id'=>'datetime', 'class'=>'form-control input-sm', 'readonly'=>'true'));?>
				</div>
			</div>
			
			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('recvs_supplier'), 'supplier', array('class'=>'control-label col-xs-3')); ?>
				<div class='col-xs-6'>
					<?php echo form_input(array('name' => 'supplier_id', 'value' => $selected_supplier, 'id' => 'supplier_id', 'class'=>'form-control input-sm'));?>
				</div>
			</div>
			
			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('recvs_invoice_number'), 'invoice_number', array('class'=>'control-label col-xs-3')); ?>
				<div class='col-xs-6'>
					<?php echo form_input(array('name' => 'invoice_number', 'value' => $receiving_info['invoice_number'], 'id' => 'invoice_number', 'class'=>'form-control input-sm'));?>
				</div>
			</div>
			
			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('recvs_employee'), 'employee', array('class'=>'control-label col-xs-3')); ?>
				<div class='col-xs-6'>
					<?php echo form_dropdown('employee_id', $employees, $receiving_info['employee_id'], 'id="employee_id" class="form-control"');?>
				</div>
			</div>
			
			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('recvs_comments'), 'comment', array('class'=>'control-label col-xs-3')); ?>
				<div class='col-xs-6'>
					<?php echo form_textarea(array('name'=>'comment','value'=>$receiving_info['comment'], 'id'=>'comment', 'class'=>'form-control input-sm'));?>
				</div>
			</div>

		<?php echo form_close(); ?>
		
		<?php echo form_open("receivings/delete/".$receiving_info['receiving_id'], array('id'=>'recvs_delete_form')); ?>
			<?php echo form_hidden('receiving_id', $receiving_info['receiving_id']);?>
			<?php echo form_submit(array(
				'name'=>'submit',
				'value'=>$this->lang->line('recvs_delete_entire_sale'),
				'class'=>'btn btn-danger btn-sm pull-right')
			);
			?>
		<?php echo form_close(); ?>
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
        }).responseText).success;
    }, '<?php echo $this->lang->line("recvs_invoice_number_duplicate"); ?>');
	
	<?php $this->load->view('partial/datepicker_locale'); ?>
	
	$('#datetime').datetimepicker({
		format: "<?php echo dateformat_bootstrap($this->config->item("dateformat")) . ' ' . dateformat_bootstrap($this->config->item("timeformat"));?>",
		startDate: "<?php echo date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), mktime(0, 0, 0, 1, 1, 2010));?>",
		<?php
		$t = $this->config->item('timeformat');
		$m = $t[strlen($t)-1];
		if( strpos($this->config->item('timeformat'), 'a') !== false || strpos($this->config->item('timeformat'), 'A') !== false )
		{ 
		?>
			showMeridian: true,
		<?php 
		}
		else
		{
		?>
			showMeridian: false,
		<?php 
		}
		?>
		minuteStep: 1,
		autoclose: true,
		todayBtn: true,
		todayHighlight: true,
		bootcssVer: 3,
		language: "<?php echo $this->config->item('language'); ?>"
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
		minChars: 0,
		delay: 15, 
		max: 100,
		cacheLength: 1,
		formatItem: format_item,
		formatResult: format_item
    });

	// declare submitHandler as an object.. will be reused
	var submit_form = function(selected_supplier) 
	{ 
		$(this).ajaxSubmit({
			success:function(response)
			{
				dialog_support.hide();
				post_form_submit(response);
			},
			error: function(jqXHR, textStatus, errorThrown) {
				selected_supplier && autocompleter.val(selected_supplier);
				post_form_submit({message: errorThrown});
			},
			dataType:'json'
		});
	};
	$('#recvs_edit_form').validate($.extend(
	{
		submitHandler : function(form)
		{
			var selected_supplier = autocompleter.val();
			var selected_supplier_id = selected_supplier.replace(/(\w)\|.*/, "$1");
			selected_supplier_id && autocompleter.val(selected_supplier_id);
			submit_form.call(form, selected_supplier);
		},
		rules:
		{
			invoice_number: {
				invoice_number: true
			}
		},
		messages: 
		{

		}
	}, dialog_support.error));
	$('#recvs_delete_form').submit(function() 
	{
		var id = $("input[name='receiving_id']").val();
		$(this).ajaxSubmit(
		{
			success:function(response)
			{
				if (confirm('<?php echo $this->lang->line("recvs_delete_confirmation"); ?>'))
				{
					dialog_support.hide();
					set_feedback(response.message, 'alert alert-dismissible alert-success', false);
					var $element = get_table_row(id).parent().parent();
					$element.find("td").animate({backgroundColor:"green"},1200,"linear")
					.end().animate({opacity:0},1200,"linear",function()
					{
						$element.next().remove();
						$(this).remove();
						//Re-init sortable table as we removed a row
						update_sortable_table();
					});
				}
				return false;
			},
			error: function(jqXHR, textStatus, errorThrown) {
				set_feedback(textStatus, 'alert alert-dismissible alert-danger', true);
			},
			dataType:'json'
		});
		return false;
	});
});
</script>
