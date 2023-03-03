<?php
/**
 * @var object $expenses_info
 * @var array $payment_options
 * @var array $expense_categories
 * @var array $employees
 * @var string $controller_name
 */
?>
<div id="required_fields_message"><?php echo lang('Common.fields_required_message') ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open("expenses/save/$expenses_info->expense_id", ['id' => 'expenses_edit_form', 'class' => 'form-horizontal']) ?>
	<fieldset id="item_basic_info">
		<div class="form-group form-group-sm">
			<?php echo form_label(lang('Expenses.info'), 'expenses_info', ['class' => 'control-label col-xs-3']) ?>
			<?php echo form_label(!empty($expenses_info->expense_id) ? lang('Expenses.expense_id') . " $expenses_info->expense_id" : '', 'expenses_info_id', ['class' => 'control-label col-xs-8', 'style' => 'text-align:left']) ?>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label(lang('Expenses.date'), 'date', ['class' => 'required control-label col-xs-3']) ?>
			<div class='col-xs-6'>
				<div class="input-group">
					<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-calendar"></span></span>
					<?php echo form_input ([
						'name' => 'date',
						'class' => 'form-control input-sm datetime',
 						'value' => to_datetime(strtotime($expenses_info->date)),
                        'readonly' => 'readonly'
						]
					) ?>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label(lang('Expenses.supplier_name'), 'supplier_name', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-6'>
				<?php echo form_input ([
						'name' => 'supplier_name',
						'id' => 'supplier_name',
						'class' => 'form-control input-sm',
						'value'=>lang('Expenses.start_typing_supplier_name')
					]);
					echo form_input ([
						'type' => 'hidden',
						'name' => 'supplier_id',
						'id' => 'supplier_id'
				]) ?>
			</div>
			<div class="col-xs-2">
				<a id="remove_supplier_button" class="btn btn-danger btn-sm" title="Remove Supplier">
					<span class="glyphicon glyphicon-remove"></span>
				</a>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label(lang('Expenses.supplier_tax_code'), 'supplier_tax_code', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-6'>
				<?php echo form_input ([
					'name' => 'supplier_tax_code',
					'id' => 'supplier_tax_code',
					'class' => 'form-control input-sm',
					'value' => esc($expenses_info->supplier_tax_code)
				]) ?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label(lang('Expenses.amount'), 'amount', ['class' => 'required control-label col-xs-3']) ?>
			<div class='col-xs-6'>
				<div class="input-group input-group-sm">
					<?php if (!currency_side()): ?>
						<span class="input-group-addon input-sm"><b><?php echo esc($config['currency_symbol']) ?></b></span>
					<?php endif; ?>
					<?php echo form_input ([
						'name' => 'amount',
						'id' => 'amount',
						'class' => 'form-control input-sm',
						'value' => to_currency_no_money($expenses_info->amount)
					]) ?>
					<?php if (currency_side()): ?>
						<span class="input-group-addon input-sm"><b><?php echo esc($config['currency_symbol']) ?></b></span>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label(lang('Expenses.tax_amount'), 'tax_amount', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-6'>
				<div class="input-group input-group-sm">
					<?php if (!currency_side()): ?>
						<span class="input-group-addon input-sm"><b><?php echo esc($config['currency_symbol']) ?></b></span>
					<?php endif; ?>
					<?php echo form_input ([
						'name' => 'tax_amount',
						'id' => 'tax_amount',
						'class' => 'form-control input-sm',
						'value' => to_currency_no_money($expenses_info->tax_amount)
					]) ?>
					<?php if (currency_side()): ?>
						<span class="input-group-addon input-sm"><b><?php echo esc($config['currency_symbol']) ?></b></span>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label(lang('Expenses.payment'), 'payment_type', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-6'>
				<?php echo form_dropdown('payment_type', esc($payment_options), esc($expenses_info->payment_type), ['class' => 'form-control', 'id' => 'payment_type']) ?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label(lang('Expenses_categories.name'), 'category', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-6'>
				<?php echo form_dropdown('expense_category_id', esc($expense_categories), $expenses_info->expense_category_id, ['class' => 'form-control', 'id' => 'category']) ?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label(lang('Expenses.employee'), 'employee', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-6'>
				<?php echo form_dropdown('employee_id', esc($employees), $expenses_info->employee_id, 'id="employee_id" class="form-control"') ?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label(lang('Expenses.description'), 'description', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-6'>
				<?php echo form_textarea ([
					'name' => 'description',
					'id' => 'description',
					'class' => 'form-control input-sm',
					'value' => esc($expenses_info->description)
				]) ?>
			</div>
		</div>

		<?php
		if(!empty($expenses_info->expense_id))
		{
		?>
			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Expenses.is_deleted').':', 'deleted', ['class' => 'control-label col-xs-3']) ?>
				<div class='col-xs-5'>
					<?php echo form_checkbox ([
						'name' => 'deleted',
						'id' => 'deleted',
						'value' => 1,
						'checked' => ($expenses_info->deleted) ? 1 : 0
					]) ?>
				</div>
			</div>
		<?php
		}
		?>
	</fieldset>
<?php echo form_close() ?>

<script type='text/javascript'>
//validation and submit handling
$(document).ready(function()
{
	<?php echo view('partial/datepicker_locale') ?>

	var amount_validator = function(field) {
		return {
			url: "<?php echo esc("$controller_name/ajax_check_amount") ?>",
			type: 'POST',
			dataFilter: function(data) {
				var response = JSON.parse(data);
				return response.success;
			}
		}
	}

	$('#supplier_name').click(function() {
		$(this).attr('value', '');
	});

	$('#supplier_name').autocomplete({
		source: '<?php echo esc(site_url("suppliers/suggest"), 'url') ?>',
		minChars:0,
		delay:10,
		select: function (event, ui) {
			$('#supplier_id').val(ui.item.value);
			$(this).val(ui.item.label);
			$(this).attr('readonly', 'readonly');
			$('#remove_supplier_button').css('display', 'inline-block');
			return false;
		}
	});

	$('#supplier_name').blur(function() {
		$(this).attr('value',"<?php echo lang('Expenses.start_typing_supplier_name') ?>");
	});

	$('#remove_supplier_button').css('display', 'none');

	$('#remove_supplier_button').click(function() {
		$('#supplier_id').val('');
		$('#supplier_name').removeAttr('readonly');
		$('#supplier_name').val('');
		$(this).css('display', 'none');
	});

	<?php
	if(!empty($expenses_info->expense_id))
	{
	?>
		$('#supplier_id').val('<?php echo $expenses_info->supplier_id ?>');
		$('#supplier_name').val('<?php echo esc($expenses_info->supplier_name, 'js') ?>').attr('readonly', 'readonly');
		$('#remove_supplier_button').css('display', 'inline-block');
	<?php
	}
	?>

	$('#expenses_edit_form').validate($.extend({
		submitHandler: function(form) {
			$(form).ajaxSubmit({
				success: function(response)
				{
					dialog_support.hide();
					table_support.handle_submit("<?php echo esc($controller_name) ?>", response);
				},
				dataType: 'json'
			});
		},

		errorLabelContainer: '#error_message_box',

		ignore: '',

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
				remote: amount_validator('#amount')
			},
			tax_amount:
			{
				remote: amount_validator('#tax_amount')
			}
		},

		messages:
		{
			category: "<?php echo lang('Expenses.category_required') ?>",
			date:
			{
				required: "<?php echo lang('Expenses.date_required') ?>"

			},
			amount:
			{
				required: "<?php echo lang('Expenses.amount_required') ?>",
				remote: "<?php echo lang('Expenses.amount_number') ?>"
			},
			tax_amount:
			{
				remote: "<?php echo lang('Expenses.tax_amount_number') ?>"
			}
		}
	}, form_support.error));
});
</script>
