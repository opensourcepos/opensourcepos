<?php
/**
 * @var object $item_info
 * @var array $stock_locations
 * @var array $item_quantities
 * @var string $controller_name
 */
?>
<div id="required_fields_message"><?= lang('Common.fields_required_message') ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?= form_open("items/saveInventory/$item_info->item_id", ['id' => 'item_form', 'class' => 'form-horizontal']) ?>
	<fieldset id="inv_item_basic_info">
		<div class="form-group form-group-sm">
			<?= form_label(lang('Items.item_number'), 'name', ['class' => 'control-label col-xs-3']) ?>
			<div class="col-xs-8">
				<div class="input-group">
					<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-barcode"></span></span>
					<?= form_input ([
						'name' => 'item_number',
						'id' => 'item_number',
						'class' => 'form-control input-sm',
						'disabled' => '',
						'value' => $item_info->item_number
					]) ?>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?= form_label(lang('Items.name'), 'name', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-8'>
				<?= form_input ([
					'name' => 'name',
					'id' => 'name',
					'class' => 'form-control input-sm',
					'disabled' => '',
					'value' => $item_info->name
				]) ?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?= form_label(lang('Items.category'), 'category', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-8'>
				<div class="input-group">
					<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-tag"></span></span>
					<?= form_input ([
							'name' => 'category',
							'id' => 'category',
							'class' => 'form-control input-sm',
							'disabled' => '',
							'value' => $item_info->category
					]) ?>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?= form_label(lang('Items.stock_location'), 'stock_location', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-8'>
				<?= form_dropdown('stock_location', $stock_locations, current($stock_locations), ['onchange' => 'fill_quantity(this.value)', 'class' => 'form-control']) ?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?= form_label(lang('Items.current_quantity'), 'quantity', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-4'>
				<?= form_input ([
					'name' => 'quantity',
					'id' => 'quantity',
					'class' => 'form-control input-sm',
					'disabled' => '',
					'value' => to_quantity_decimals(current($item_quantities))
				]) ?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?= form_label(lang('Items.add_minus'), 'quantity', ['class' => 'required control-label col-xs-3']) ?>
			<div class='col-xs-4'>
				<?= form_input ([
					'name' => 'newquantity',
					'id' => 'newquantity',
					'class' => 'form-control input-sm'
				]) ?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?= form_label(lang('Items.inventory_comments'), 'description', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-8'>
				<?= form_textarea ([
					'name' => 'trans_comment',
					'id' => 'trans_comment',
					'class' => 'form-control input-sm'
				]) ?>
			</div>
		</div>
	</fieldset>
<?= form_close() ?>

<script type="application/javascript">
//validation and submit handling
$(document).ready(function()
{
	$('#item_form').validate($.extend({
		submitHandler: function(form) {
			$(form).ajaxSubmit({
				success: function(response)
				{
					dialog_support.hide();
					table_support.handle_submit("<?= esc($controller_name) ?>", response);
				},
				dataType: 'json'
			});
		},

		errorLabelContainer: '#error_message_box',

		rules:
		{
			newquantity:
			{
				required: true,
				number: true
			}
   		},

		messages:
		{
			newquantity:
			{
				required: "<?= lang('Items.quantity_required') ?>",
				number: "<?= lang('Items.quantity_number') ?>"
			}
		}
	}, form_support.error));
});

function fill_quantity(val)
{
	var item_quantities = <?= json_encode(esc($item_quantities, 'raw')) ?>;
	document.getElementById('quantity').value = parseFloat(item_quantities[val]).toFixed(<?= quantity_decimals() ?>);
}
</script>
