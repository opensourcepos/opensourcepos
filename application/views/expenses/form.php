<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open('expenses/save/'.$expenses_info->expense_id, array('id'=>'expenses_edit_form', 'class'=>'form-horizontal')); ?>
	<fieldset id="item_basic_info">
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('expenses_info'), 'expenses_info', array('class'=>'control-label col-xs-3')); ?>
			<?php echo form_label(!empty($expenses_info->expense_id) ? $this->lang->line('expenses_expense_id') . ' ' . $expenses_info->expense_id : '', 'expenses_info_id', array('class'=>'control-label col-xs-8', 'style'=>'text-align:left')); ?>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('expenses_date'), 'date', array('class'=>'required control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<div class="input-group">
					<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-calendar"></span></span>
					<?php echo form_input(array(
							'name'=>'date',
							'id'=>'datetime',
							'class'=>'form-control input-sm datepicker',
							'value'=>date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), strtotime($expenses_info->date)))
							);?>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('expenses_supplier_name'), 'supplier_name', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<?php echo form_input(array(
						'name'=>'supplier_name',
						'id'=>'supplier_name',
						'class'=>'form-control input-sm',
						'value'=>$expenses_info->supplier_name)
						);?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('expenses_supplier_tax_code'), 'supplier_tax_code', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<?php echo form_input(array(
						'name'=>'supplier_tax_code',
						'id'=>'supplier_tax_code',
						'class'=>'form-control input-sm',
						'value'=>$expenses_info->supplier_tax_code)
						);?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('expenses_amount'), 'amount', array('class'=>'required control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<div class="input-group input-group-sm">
					<?php if (!currency_side()): ?>
						<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
					<?php endif; ?>
					<?php echo form_input(array(
							'name'=>'amount',
							'id'=>'amount',
							'class'=>'form-control input-sm',
							'value'=>to_currency_no_money($expenses_info->amount))
							);?>
					<?php if (currency_side()): ?>
						<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('expenses_tax_amount'), 'tax_amount', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<div class="input-group input-group-sm">
					<?php if (!currency_side()): ?>
						<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
					<?php endif; ?>
					<?php echo form_input(array(
							'name'=>'tax_amount',
							'id'=>'tax_amount',
							'class'=>'form-control input-sm',
							'value'=>to_currency_no_money($expenses_info->tax_amount))
							);?>
					<?php if (currency_side()): ?>
						<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('expenses_payment'), 'payment_type', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<?php echo form_dropdown('payment_type', $payment_options, $expenses_info->payment_type, array('class'=>'form-control', 'id'=>'payment_type'));?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('expenses_categories_name'), 'category', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<?php echo form_dropdown('expense_category_id', $expense_categories, $expenses_info->expense_category_id, array('class'=>'form-control', 'id'=>'category')); ?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('expenses_employee'), 'employee', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<?php echo form_dropdown('employee_id', $employees, $expenses_info->employee_id, 'id="employee_id" class="form-control"');?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('expenses_description'), 'description', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<?php echo form_textarea(array(
					'name'=>'description',
					'id'=>'description',
					'class'=>'form-control input-sm',
					'value'=>$expenses_info->description)
					);?>
			</div>
		</div>

		<?php
		if(!empty($expenses_info->expense_id))
		{
		?>
			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('expenses_is_deleted').':', 'deleted', array('class'=>'control-label col-xs-3')); ?>
				<div class='col-xs-5'>
					<?php echo form_checkbox(array(
						'name'=>'deleted',
						'id'=>'deleted',
						'value'=>1,
						'checked'=>($expenses_info->deleted) ? 1 : 0)
					);?>
				</div>
			</div>
		<?php
		}
		?>
	</fieldset>
<?php echo form_close(); ?>

<script type='text/javascript'>
//validation and submit handling
$(document).ready(function()
{
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

	var submit_form = function()
	{ 
		$(this).ajaxSubmit(
		{
			success: function(response)
			{
				dialog_support.hide();
				table_support.handle_submit('<?php echo site_url('expenses'); ?>', response);
			},
			dataType: 'json'
		});
	};

	$('#expenses_edit_form').validate($.extend(
	{
		submitHandler: function(form)
		{
			submit_form.call(form);
		},
		rules:
		{
			category: 'required',
			date:
			{
				required: true
			},
			amount:
			{
				required: true,
				number: true
			}
		},
		messages:
		{
			category: '<?php echo $this->lang->line('expenses_category_required'); ?>',
			date:
			{
				required: '<?php echo $this->lang->line('expenses_date_required'); ?>'

			},
			amount:
			{
				required: '<?php echo $this->lang->line('expenses_amount_required'); ?>',
				number: '<?php echo $this->lang->line('expenses_amount_number'); ?>'
			}
		}
	}, form_support.error));
});
</script>
