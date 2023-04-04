<?php
/**
 * @var array $suppliers
 * @var array $allow_alt_description_choices
 * @var array $serialization_choices
 * @var string $controller_name
 */
?>
<div id="required_fields_message"><?php echo lang('Items.edit_fields_you_want_to_update') ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open('items/bulk_update/', ['id' => 'item_form', 'class' => 'form-horizontal']) ?>
	<fieldset id="bulk_item_basic_info">
		<div class="form-group form-group-sm">
			<?php echo form_label(lang('Items.name'), 'name', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-8'>
				<?php echo form_input ([
					'name' => 'name',
					'id' => 'name',
					'class' => 'form-control input-sm'
				]) ?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label(lang('Items.category'), 'category', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-8'>
				<div class="input-group">
					<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-tag"></span></span>
					<?php echo form_input ([
						'name' => 'category',
						'id' => 'category',
						'class' => 'form-control input-sm'
					]) ?>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label(lang('Items.supplier'), 'supplier', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-8'>
				<?php echo form_dropdown('supplier_id', $suppliers, '', ['class' => 'form-control']) ?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label(lang('Items.cost_price'), 'cost_price', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-4'>
				<div class="input-group input-group-sm">
					<?php if (!currency_side()): ?>
						<span class="input-group-addon input-sm"><b><?php echo esc($config['currency_symbol']) ?></b></span>
					<?php endif; ?>
					<?php echo form_input ([
						'name' => 'cost_price',
						'id' => 'cost_price',
						'class' => 'form-control input-sm'
					]) ?>
					<?php if (currency_side()): ?>
						<span class="input-group-addon input-sm"><b><?php echo esc($config['currency_symbol']) ?></b></span>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="form-group form-group">
			<?php echo form_label(lang('Items.unit_price'), 'unit_price', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-4'>
				<div class="input-group input-group-sm">
					<?php if (!currency_side()): ?>
						<span class="input-group-addon input-sm"><b><?php echo esc($config['currency_symbol']) ?></b></span>
					<?php endif; ?>
					<?php echo form_input ([
						'name' => 'unit_price',
						'id' => 'unit_price',
						'class' => 'form-control input-sm'
					]) ?>
					<?php if (currency_side()): ?>
						<span class="input-group-addon input-sm"><b><?php echo esc($config['currency_symbol']) ?></b></span>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label(lang('Items.tax_1'), 'tax_percent_1', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-4'>
				<?php echo form_input ([
					'name' => 'tax_names[]',
					'id' => 'tax_name_1',
					'class' => 'form-control input-sm',
					'value' => esc($config['default_tax_1_name'])
				]) ?>
			</div>
			<div class="col-xs-4">
				<div class="input-group input-group-sm">
					<?php echo form_input ([
						'name' => 'tax_percents[]',
						'id' => 'tax_percent_name_1',
						'class' => 'form-control input-sm',
						'value'=>to_tax_decimals($config['default_tax_1_rate'])
					]) ?>
					<span class="input-group input-group-addon"><b>%</b></span>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label(lang('Items.tax_2'), 'tax_percent_2', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-4'>
				<?php echo form_input ([
					'name' => 'tax_names[]',
					'id' => 'tax_name_2',
					'class' => 'form-control input-sm',
					'value' => esc($config['default_tax_2_name'])
				]) ?>
			</div>
			<div class="col-xs-4">
				<div class="input-group input-group-sm">
					<?php echo form_input ([
						'name' => 'tax_percents[]',
						'id' => 'tax_percent_name_2',
						'class' => 'form-control input-sm',
						'value' => to_tax_decimals($config['default_tax_2_rate'])
					]) ?>
					<span class="input-group input-group-addon"><b>%</b></span>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label(lang('Items.reorder_level'), 'reorder_level', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-4'>
				<?php echo form_input ([
					'name' => 'reorder_level',
					'id' => 'reorder_level',
					'class' => 'form-control input-sm'
				]) ?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label(lang('Items.description'), 'description', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-8'>
				<?php echo form_textarea ([
					'name' => 'description',
					'id' => 'description',
					'class' => 'form-control input-sm'
				]) ?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label(lang('Items.allow_alt_description'), 'allow_alt_description', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-8'>
				<?php echo form_dropdown('allow_alt_description', $allow_alt_description_choices, '', ['class' => 'form-control']) ?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label(lang('Items.is_serialized'), 'is_serialized', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-8'>
				<?php echo form_dropdown('is_serialized', $serialization_choices, '', ['class' => 'form-control']) ?>
			</div>
		</div>
	</fieldset>
<?php echo form_close() ?>

<script type="text/javascript">
//validation and submit handling
$(document).ready(function()
{
	$('#category').autocomplete({
		source: "<?php echo 'items/suggest_category' ?>",
		appendTo: '.modal-content',
		delay: 10
	});

	var confirm_message = false;
	$('#tax_percent_name_2, #tax_name_2').prop('disabled', true),
	$('#tax_percent_name_1, #tax_name_1').blur(function() {
		var disabled = !($('#tax_percent_name_1').val() + $('#tax_name_1').val());
		$('#tax_percent_name_2, #tax_name_2').prop('disabled', disabled);
		confirm_message =  disabled ? '' : "<?php echo lang('Items.confirm_bulk_edit_wipe_taxes') ?>";
	});

	$('#item_form').validate($.extend({
		submitHandler: function(form) {
			if(!confirm_message || confirm(confirm_message))
			{
				$(form).ajaxSubmit({
					beforeSubmit: function(arr, $form, options) {
						arr.push({name: 'item_ids', value: table_support.selected_ids().join(":")});
					},
					success: function(response)
					{
						dialog_support.hide();
						table_support.handle_submit("<?php echo esc($controller_name) ?>", response);
					},
					dataType: 'json'
				});
			}
		},

		errorLabelContainer: '#error_message_box',

		rules:
		{
			unit_price:
			{
				number: true
			},
			tax_percent:
			{
				number: true
			},
			quantity:
			{
				number: true
			},
			reorder_level:
			{
				number: true
			}
		},

		messages:
		{
			unit_price:
			{
				number: "<?php echo lang('Items.unit_price_number') ?>"
			},
			tax_percent:
			{
				number: "<?php echo lang('Items.tax_percent_number') ?>"
			},
			quantity:
			{
				number: "<?php echo lang('Items.quantity_number') ?>"
			},
			reorder_level:
			{
				number: "<?php echo lang('Items.reorder_level_number') ?>"
			}
		}
	}, form_support.error));
});
</script>
