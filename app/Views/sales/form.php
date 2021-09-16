<div id="required_fields_message"><?php echo lang('Common.fields_required_message'); ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open("sales/save/".$sale_info['sale_id'], ['id'=>'sales_edit_form', 'class'=>'form-horizontal')); ?>
	<fieldset id="sale_basic_info">
		<div class="form-group form-group-sm">
			<?php echo form_label(lang('Sales.receipt_number'), 'receipt_number', ['class'=>'control-label col-xs-3')); ?>
			<?php echo anchor('sales/receipt/'.$sale_info['sale_id'], 'POS ' . $sale_info['sale_id'], ['target'=>'_blank', 'class'=>'control-label col-xs-8', "style"=>"text-align:left"));?>
		</div>
		
		<div class="form-group form-group-sm">
			<?php echo form_label(lang('Sales.date'), 'date', ['class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<?php echo form_input (['name'=>'date','value'=>to_datetime(strtotime($sale_info['sale_time'])), 'class'=>'datetime form-control input-sm'));?>
			</div>
		</div>

		<?php
		if($this->config->get('invoice_enable') == TRUE)
		{
		?>
			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Sales.invoice_number'), 'invoice_number', ['class'=>'control-label col-xs-3')); ?>
				<div class='col-xs-8'>
					<?php if(!empty($sale_info["invoice_number"]) && isset($sale_info['customer_id']) && !empty($sale_info['email'])): ?>
						<?php echo form_input (['name'=>'invoice_number', 'size'=>10, 'value'=>$sale_info['invoice_number'], 'id'=>'invoice_number', 'class'=>'form-control input-sm'));?>
						<a id="send_invoice" href="javascript:void(0);"><?php echo lang('Sales.send_invoice');?></a>
					<?php else: ?>
						<?php echo form_input (['name'=>'invoice_number', 'value'=>$sale_info['invoice_number'], 'id'=>'invoice_number', 'class'=>'form-control input-sm'));?>
					<?php endif; ?>
				</div>
			</div>
		<?php
		}
		?>

		<?php
		if($balance_due)
		{
		?>
			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Sales.payment'), 'payment_new', ['class'=>'control-label col-xs-3')); ?>
				<div class='col-xs-4'>
					<?php echo form_dropdown('payment_type_new', $new_payment_options, $payment_type_new, ['id'=>'payment_types_new', 'class'=>'form-control')); ?>
				</div>
				<div class='col-xs-4'>
					<div class="input-group input-group-sm">
						<?php if(!currency_side()): ?>
							<span class="input-group-addon input-sm"><b><?php echo $this->config->get('currency_symbol'); ?></b></span>
						<?php endif; ?>
						<?php echo form_input (['name'=>'payment_amount_new', 'value'=>$payment_amount_new, 'id'=>'payment_amount_new', 'class'=>'form-control input-sm'));?>
						<?php if(currency_side()): ?>
							<span class="input-group-addon input-sm"><b><?php echo $this->config->get('currency_symbol'); ?></b></span>
						<?php endif; ?>
					</div>
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
				<?php echo form_label(lang('Sales.payment'), 'payment_'.$i, ['class'=>'control-label col-xs-3')); ?>
				<div class='col-xs-4'>
					<?php // no editing of Gift Card payments as it's a complex change ?>
					<?php echo form_hidden('payment_id_'.$i, $row->payment_id); ?>
					<?php if( !empty(strstr($row->payment_type, lang('Sales.giftcard'))) ): ?>
						<?php echo form_input (['name'=>'payment_type_'.$i, 'value'=>$row->payment_type, 'id'=>'payment_type_'.$i, 'class'=>'form-control input-sm', 'readonly'=>'true'));?>
					<?php else: ?>
						<?php echo form_dropdown('payment_type_'.$i, $payment_options, $row->payment_type, ['id'=>'payment_types_'.$i, 'class'=>'form-control')); ?>
					<?php endif; ?>
				</div>
				<div class='col-xs-4'>
					<div class="input-group input-group-sm">
						<?php if(!currency_side()): ?>
							<span class="input-group-addon input-sm"><b><?php echo $this->config->get('currency_symbol'); ?></b></span>
						<?php endif; ?>
						<?php echo form_input (['name'=>'payment_amount_'.$i, 'value'=>$row->payment_amount, 'id'=>'payment_amount_'.$i, 'class'=>'form-control input-sm', 'readonly'=>'true'));?>
						<?php if(currency_side()): ?>
							<span class="input-group-addon input-sm"><b><?php echo $this->config->get('currency_symbol'); ?></b></span>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Sales.refund'), 'refund_'.$i, ['class'=>'control-label col-xs-3')); ?>
				<div class='col-xs-4'>
					<?php // no editing of Gift Card payments as it's a complex change ?>
					<?php if( !empty(strstr($row->payment_type, lang('Sales.giftcard'))) ): ?>
						<?php echo form_input (['name'=>'refund_type_'.$i, 'value'=>lang('Sales.cash'), 'id'=>'refund_type_'.$i, 'class'=>'form-control input-sm', 'readonly'=>'true'));?>
					<?php else: ?>
						<?php echo form_dropdown('refund_type_'.$i, $payment_options, lang('Sales.cash'), ['id'=>'refund_types_'.$i, 'class'=>'form-control')); ?>
					<?php endif; ?>
				</div>
				<div class='col-xs-4'>
					<div class="input-group input-group-sm">
						<?php if(!currency_side()): ?>
							<span class="input-group-addon input-sm"><b><?php echo $this->config->get('currency_symbol'); ?></b></span>
						<?php endif; ?>
						<?php echo form_input (['name'=>'refund_amount_'.$i, 'value'=>$row->cash_refund, 'id'=>'refund_amount_'.$i, 'class'=>'form-control input-sm', 'readonly'=>'true'));?>
						<?php if(currency_side()): ?>
							<span class="input-group-addon input-sm"><b><?php echo $this->config->get('currency_symbol'); ?></b></span>
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
			<?php echo form_label(lang('Sales.customer'), 'customer', ['class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<?php echo form_input (['name'=>'customer_name', 'value'=>$selected_customer_name, 'id'=>'customer_name', 'class'=>'form-control input-sm'));?>
				<?php echo form_hidden('customer_id', $selected_customer_id);?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label(lang('Sales.employee'), 'employee', ['class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<?php echo form_input (['name'=>'employee_name', 'value'=>$selected_employee_name, 'id'=>'employee_name', 'class'=>'form-control input-sm'));?>
				<?php echo form_hidden('employee_id', $selected_employee_id);?>
			</div>
		</div>
		
		<div class="form-group form-group-sm">
			<?php echo form_label(lang('Sales.comment'), 'comment', ['class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<?php echo form_textarea (['name'=>'comment', 'value'=>$sale_info['comment'], 'id'=>'comment', 'class'=>'form-control input-sm'));?>
			</div>
		</div>
	</fieldset>
<?php echo form_close(); ?>

<script type="text/javascript">
$(document).ready(function()
{	
	<?php if(!empty($sale_info['email'])): ?>
		$('#send_invoice').click(function(event) {
			if (confirm("<?php echo lang('Sales.invoice_confirm') . ' ' . $sale_info['email'] ?>")) {
				$.get("<?php echo site_url($controller_name . '/send_pdf/' . $sale_info['sale_id']); ?>",
					function(response) {
						BootstrapDialog.closeAll();
						$.notify( { message: response.message }, { type: response.success ? 'success' : 'danger'} )
					}, 'json'
				);	
			}
		});
	<?php endif; ?>
	
	<?php echo view('partial/datepicker_locale'); ?>

	var fill_value_customer = function(event, ui) {
		event.preventDefault();
		$("input[name='customer_id']").val(ui.item.value);
		$("input[name='customer_name']").val(ui.item.label);
	};

	$('#customer_name').autocomplete( {
		source: "<?php echo site_url('customers/suggest'); ?>",
		minChars: 0,
		delay: 15, 
		cacheLength: 1,
		appendTo: '.modal-content',
		select: fill_value_customer,
		focus: fill_value_customer
	});

	var fill_value_employee = function(event, ui) {
		event.preventDefault();
		$("input[name='employee_id']").val(ui.item.value);
		$("input[name='employee_name']").val(ui.item.label);
	};

	$('#employee_name').autocomplete( {
		source: "<?php echo site_url('employees/suggest'); ?>",
		minChars: 0,
		delay: 15, 
		cacheLength: 1,
		appendTo: '.modal-content',
		select: fill_value_employee,
		focus: fill_value_employee
	});

	$('button#delete').click(function() {
		dialog_support.hide();
		table_support.do_delete("<?php echo site_url($controller_name); ?>", <?php echo $sale_info['sale_id']; ?>);
	});

	$('button#restore').click(function() {
		dialog_support.hide();
		table_support.do_restore("<?php echo site_url($controller_name); ?>", <?php echo $sale_info['sale_id']; ?>);
	});

	$('#sales_edit_form').validate($.extend( {
		submitHandler: function(form) {
			$(form).ajaxSubmit({
				success: function(response)
				{
					dialog_support.hide();
					table_support.handle_submit("<?php echo site_url($controller_name); ?>", response);
				},
				dataType: 'json'
			});
		},

		errorLabelContainer: '#error_message_box',

		rules:
		{
			invoice_number:
			{
				remote:
				{
					url: "<?php echo site_url($controller_name . '/check_invoice_number')?>",
					type: 'POST',
					data: {
						'sale_id': <?php echo $sale_info['sale_id']; ?>,
						'invoice_number': function() {
							return $('#invoice_number').val();
						}
					}
				}
			}
		},

		messages: 
		{
			invoice_number: "<?php echo lang('Sales.invoice_number_duplicate'); ?>"
		}
	}, form_support.error));
});
</script>
