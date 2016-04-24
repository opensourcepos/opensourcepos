<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open("receivings/save/".$receiving_info['receiving_id'], array('id'=>'recvs_edit_form', 'class'=>'form-horizontal')); ?>
	<fieldset id="receiving_basic_info">
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('recvs_receipt_number'), 'supplier', array('class'=>'control-label col-xs-3')); ?>
			<?php echo anchor('receivings/receipt/'.$receiving_info['receiving_id'], 'RECV ' . $receiving_info['receiving_id'], array('target'=>'_blank', 'class'=>'control-label col-xs-8', "style"=>"text-align:left"));?>
		</div>
		
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('recvs_date'), 'date', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<?php echo form_input(array('name'=>'date','value'=>date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), strtotime($receiving_info['receiving_time'])), 'id'=>'datetime', 'class'=>'form-control input-sm', 'readonly'=>'true'));?>
			</div>
		</div>
		
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('recvs_supplier'), 'supplier', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<?php echo form_input(array('name' => 'supplier_id', 'value' => $selected_supplier_name, 'id' => 'supplier_id', 'class'=>'form-control input-sm'));?>
				<?php echo form_hidden('supplier_id', $selected_supplier_id);?>
			</div>
		</div>

		<?php
		if($this->config->item('invoice_enable') == TRUE)
		{
		?>
			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('recvs_invoice_number'), 'invoice_number', array('class'=>'control-label col-xs-3')); ?>
				<div class='col-xs-8'>
					<?php echo form_input(array('name' => 'invoice_number', 'value' => $receiving_info['invoice_number'], 'id' => 'invoice_number', 'class'=>'form-control input-sm'));?>
				</div>
			</div>
		<?php
		}
		?>
		
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('recvs_employee'), 'employee', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<?php echo form_dropdown('employee_id', $employees, $receiving_info['employee_id'], 'id="employee_id" class="form-control"');?>
			</div>
		</div>
		
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('recvs_comments'), 'comment', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<?php echo form_textarea(array('name'=>'comment','value'=>$receiving_info['comment'], 'id'=>'comment', 'class'=>'form-control input-sm'));?>
			</div>
		</div>
	</fieldset>
<?php echo form_close(); ?>
		
<?php echo form_open("receivings/delete/".$receiving_info['receiving_id'], array('id'=>'recvs_delete_form')); ?>
	<?php echo form_hidden('receiving_id', $receiving_info['receiving_id']);?>
<?php echo form_close(); ?>

<script type="text/javascript" language="javascript">
$(document).ready(function()
{
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

	var fill_value =  function(event, ui) {
		event.preventDefault();
		$("input[name='supplier_id']").val(ui.item.value);
		$("input[name='supplier_name']").val(ui.item.label);
	};

	var autocompleter = $("#supplier_id").autocomplete(
	{
		source: '<?php echo site_url("suppliers/suggest"); ?>',
		minChars: 0,
		delay: 15, 
		cacheLength: 1,
		appendTo: '.modal-content',
		select: fill_value,
		focus: fill_value
    });

	// declare submitHandler as an object.. will be reused
	var submit_form = function()
	{ 
		$(this).ajaxSubmit(
		{
			success:function(response)
			{
				dialog_support.hide();
				post_form_submit(response);
			},
			error: function(jqXHR, textStatus, errorThrown) {
				post_form_submit({message: errorThrown});
			},
			dataType:'json'
		});
	};

	$('#recvs_edit_form').validate($.extend(
	{
		submitHandler : function(form)
		{
			submit_form.call(form);
		},
		rules:
		{
			<?php
			if($this->config->item('invoice_enable') == TRUE)
			{
			?>
				invoice_number: {

					remote:
					{
						url: "<?php echo site_url($controller_name . '/check_invoice_number')?>",
						type: "POST",
						data:
						{
							"receiving_id" : <?php echo $receiving_info['receiving_id']; ?>,
							"invoice_number" : function()
							{
								return $("#invoice_number").val();
							}
						}
					}
				}
			<?php
			}
			?>
		},
		messages: 
		{
			<?php
			if($this->config->item('invoice_enable') == TRUE)
			{
			?>
				invoice_number: '<?php echo $this->lang->line("recvs_invoice_number_duplicate"); ?>'
			<?php
			}
			?>
		}
	}, dialog_support.error));
	
	$('#recvs_delete_form').submit(function() 
	{
		if (confirm('<?php echo $this->lang->line("recvs_delete_confirmation"); ?>'))
		{
			var id = $("input[name='receiving_id']").val();
			$(this).ajaxSubmit({
				success: function(response)
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
				},
				error: function(jqXHR, textStatus, errorThrown) {
					set_feedback(textStatus, 'alert alert-dismissible alert-danger', true);
				},
				dataType:'json'
			});
		}
		return false;
	});
});
</script>
