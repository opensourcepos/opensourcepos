<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open("sales/save/".$sale_info['sale_id'], array('id'=>'sales_edit_form', 'class'=>'form-horizontal')); ?>
	<fieldset id="sale_basic_info">
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('sales_receipt_number'), 'receipt_number', array('class'=>'control-label col-xs-3')); ?>
			<?php echo anchor('sales/receipt/'.$sale_info['sale_id'], 'POS ' . $sale_info['sale_id'], array('target'=>'_blank', 'class'=>'control-label col-xs-8', "style"=>"text-align:left"));?>
		</div>
		
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('sales_date'), 'date', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<?php echo form_input(array('name'=>'date','value'=>date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), strtotime($sale_info['sale_time'])), 'id'=>'datetime', 'class'=>'form-control input-sm'));?>
			</div>
		</div>
		
		<?php
		if($this->config->item('invoice_enable') == TRUE)
		{
		?>
			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('sales_invoice_number'), 'invoice_number', array('class'=>'control-label col-xs-3')); ?>
				<div class='col-xs-8'>
					<?php if(!empty($sale_info["invoice_number"]) && isset($sale_info['customer_id']) && !empty($sale_info['email'])): ?>
						<?php echo form_input(array('name'=>'invoice_number', 'size'=>10, 'value'=>$sale_info['invoice_number'], 'id'=>'invoice_number', 'class'=>'form-control input-sm'));?>
						<a id="send_invoice" href="javascript:void(0);"><?php echo $this->lang->line('sales_send_invoice');?></a>
					<?php else: ?>
						<?php echo form_input(array('name'=>'invoice_number', 'value'=>$sale_info['invoice_number'], 'id'=>'invoice_number', 'class'=>'form-control input-sm'));?>
					<?php endif; ?>
				</div>
			</div>
		<?php
		}
		?>

		<?php 
		$i = 0;
		foreach($payments as $row)
		{
		?>
			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('sales_payment'), 'payment_'.$i, array('class'=>'control-label col-xs-3')); ?>
				<div class='col-xs-4'>
						<?php // no editing of Gift Card payments as it's a complex change ?>
						<?php if( !empty(strstr($row->payment_type, $this->lang->line('sales_giftcard'))) ): ?>
							<?php echo form_input(array('name'=>'payment_type_'.$i, 'value'=>$row->payment_type, 'id'=>'payment_type_'.$i, 'class'=>'form-control input-sm', 'readonly'=>'true'));?>
						<?php else: ?>
							<?php echo form_dropdown('payment_type_'.$i, $payment_options, $row->payment_type, array('id'=>'payment_types_'.$i, 'class'=>'form-control')); ?>
						<?php endif; ?>
				</div>
				<div class='col-xs-4'>
					<div class="input-group input-group-sm">
						<?php if(!currency_side()): ?>
							<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
						<?php endif; ?>
						<?php echo form_input(array('name'=>'payment_amount_'.$i, 'value'=>$row->payment_amount, 'id'=>'payment_amount_'.$i, 'class'=>'form-control input-sm', 'readonly'=>'true'));?>
						<?php if (currency_side()): ?>
							<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
						<?php endif; ?>
					</div>
				</div>
			</div>
		<?php 
			++$i;
		}
		echo form_hidden('number_of_payments', $i);			
		?>
		
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('sales_customer'), 'customer', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<?php echo form_input(array('name'=>'customer_name', 'value'=>$selected_customer_name, 'id'=>'customer_name', 'class'=>'form-control input-sm'));?>
				<?php echo form_hidden('customer_id', $selected_customer_id);?>
			</div>
		</div>
		
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('sales_employee'), 'employee', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<?php echo form_dropdown('employee_id', $employees, $sale_info['employee_id'], 'id="employee_id" class="form-control"');?>
			</div>
		</div>
		
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('sales_comment'), 'comment', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<?php echo form_textarea(array('name'=>'comment', 'value'=>$sale_info['comment'], 'id'=>'comment', 'class'=>'form-control input-sm'));?>
			</div>
		</div>
	</fieldset>
<?php echo form_close(); ?>

<script type="text/javascript">
$(document).ready(function()
{	
	<?php if(!empty($sale_info['email'])): ?>
		$('#send_invoice').click(function(event) {
			if (confirm("<?php echo $this->lang->line('sales_invoice_confirm') . ' ' . $sale_info['email'] ?>")) {
				$.get('<?php echo site_url() . "/sales/send_pdf/" . $sale_info['sale_id']; ?>',
					function(response) {
						dialog_support.hide();
						table_support.handle_submit('<?php echo site_url('sales'); ?>', response);
					}, 'json'
				);	
			}
		});
	<?php endif; ?>
	
	<?php $this->load->view('partial/datepicker_locale'); ?>
	
	$('#datetime').datetimepicker({
		format: "<?php echo dateformat_bootstrap($this->config->item('dateformat')) . ' ' . dateformat_bootstrap($this->config->item('timeformat'));?>",
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
		language: '<?php echo current_language_code(); ?>'
	});

	var fill_value =  function(event, ui) {
		event.preventDefault();
		$("input[name='customer_id']").val(ui.item.value);
		$("input[name='customer_name']").val(ui.item.label);
	};

	$('#customer_name').autocomplete(
	{
		source: '<?php echo site_url("customers/suggest"); ?>',
		minChars: 0,
		delay: 15, 
		cacheLength: 1,
		appendTo: '.modal-content',
		select: fill_value,
		focus: fill_value
	});

	$('button#delete').click(function() {
		dialog_support.hide();
		table_support.do_delete('<?php echo site_url('sales'); ?>', <?php echo $sale_info['sale_id']; ?>);
	});

	$('button#restore').click(function() {
		dialog_support.hide();
		table_support.do_restore('<?php echo site_url('sales'); ?>', <?php echo $sale_info['sale_id']; ?>);
	});

	var submit_form = function()
	{
		$(this).ajaxSubmit(
		{
			success: function(response)
			{
				dialog_support.hide();
				table_support.handle_submit('<?php echo site_url('sales'); ?>', response);
			},
			dataType: 'json'
		});
	};

	$('#sales_edit_form').validate($.extend(
	{
		submitHandler: function(form)
		{
			submit_form.call(form);
		},
		rules:
		{
			invoice_number:
			{
				remote:
				{
					url: "<?php echo site_url($controller_name . '/check_invoice_number')?>",
					type: 'POST',
					data: $.extend(csrf_form_base(), {
						'sale_id' : <?php echo $sale_info['sale_id']; ?>,
						'invoice_number' : function() {
							return $('#invoice_number').val();
						}
					})
				}
			}
		},
		messages: 
		{
			invoice_number: '<?php echo $this->lang->line("sales_invoice_number_duplicate"); ?>'
		}
	}, form_support.error));
});
</script>
