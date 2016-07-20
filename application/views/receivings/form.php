<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open("receivings/save/".$receiving_info['receiving_id'], array('id'=>'receivings_edit_form', 'class'=>'form-horizontal')); ?>
	<fieldset id="receiving_basic_info">
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('receivings_receipt_number'), 'supplier', array('class'=>'control-label col-xs-3')); ?>
			<?php echo anchor('receivings/receipt/'.$receiving_info['receiving_id'], 'RECV ' . $receiving_info['receiving_id'], array('target'=>'_blank', 'class'=>'control-label col-xs-8', "style"=>"text-align:left"));?>
		</div>
		
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('receivings_date'), 'date', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<?php echo form_input(array('name'=>'date','value'=>date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), strtotime($receiving_info['receiving_time'])), 'id'=>'datetime', 'class'=>'form-control input-sm', 'readonly'=>'true'));?>
			</div>
		</div>
		
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('receivings_supplier'), 'supplier', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<?php echo form_input(array('name' => 'supplier_name', 'value' => $selected_supplier_name, 'id' => 'supplier_name', 'class'=>'form-control input-sm'));?>
				<?php echo form_hidden('supplier_id', $selected_supplier_id);?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('receivings_reference'), 'reference', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<?php echo form_input(array('name' => 'reference', 'value' => $receiving_info['reference'], 'id' => 'reference', 'class'=>'form-control input-sm'));?>
			</div>
		</div>
		
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('receivings_employee'), 'employee', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<?php echo form_dropdown('employee_id', $employees, $receiving_info['employee_id'], 'id="employee_id" class="form-control"');?>
			</div>
		</div>
		
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('receivings_comments'), 'comment', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<?php echo form_textarea(array('name'=>'comment','value'=>$receiving_info['comment'], 'id'=>'comment', 'class'=>'form-control input-sm'));?>
			</div>
		</div>
	</fieldset>
<?php echo form_close(); ?>
		
<script type="text/javascript">
$(document).ready(function()
{
	<?php $this->load->view('partial/datepicker_locale'); ?>
	
	$('#datetime').datetimepicker(
	{
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

	var fill_value = function(event, ui)
	{
		event.preventDefault();
		$("input[name='supplier_id']").val(ui.item.value);
		$("input[name='supplier_name']").val(ui.item.label);
	};

	var autocompleter = $("#supplier_name").autocomplete(
	{
		source: '<?php echo site_url("suppliers/suggest"); ?>',
		minChars: 0,
		delay: 15, 
		cacheLength: 1,
		appendTo: '.modal-content',
		select: fill_value,
		focus: fill_value
    });

	$('button#delete').click(function()
	{
		dialog_support.hide();
		table_support.do_delete('<?php echo site_url('receivings'); ?>', <?php echo $receiving_info['receiving_id']; ?>);
	});

	// declare submitHandler as an object.. will be reused
	var submit_form = function()
	{
		$(this).ajaxSubmit(
		{
			success:function(response)
			{
				dialog_support.hide();
				table_support.handle_submit('<?php echo site_url('receivings'); ?>', response);
			},
			dataType:'json'
		});
	};

	$('#receivings_edit_form').validate($.extend(
	{
		submitHandler : function(form)
		{
			submit_form.call(form);
		}
	}, form_support.error));

});
</script>
