<?php
/**
 * @var object $item_info
 * @var array $categories
 * @var int $selected_category
 * @var bool $standard_item_locked
 * @var bool $item_kit_disabled
 * @var int $allow_temp_item
 * @var array $suppliers
 * @var int $selected_supplier
 * @var bool $use_destination_based_tax
 * @var float $default_tax_1_rate
 * @var float $default_tax_2_rate
 * @var string $tax_category
 * @var int $tax_category_id
 * @var bool $include_hsn
 * @var string $hsn_code
 * @var array $stock_locations
 * @var bool $logo_exists
 * @var string $image_path
 * @var string $selected_low_sell_item
 * @var int $selected_low_sell_item_id
 * @var string $controller_name
 * @var array $config
 */
?>
<div id="required_fields_message"><?= lang('Common.fields_required_message') ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?= form_open("items/save/$item_info->item_id", ['id' => 'item_form', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal']) ?>
	<fieldset id="item_basic_info">
		<div class="form-group form-group-sm">
			<?= form_label(lang('Items.item_number'), 'item_number', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-8'>
				<div class="input-group">
					<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-barcode"></span></span>
					<?= form_input ([
						'name' => 'item_number',
						'id' => 'item_number',
						'class' => 'form-control input-sm',
						'value' => $item_info->item_number
					]) ?>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?= form_label(lang('Items.name'), 'name', ['class' => 'required control-label col-xs-3']) ?>
			<div class='col-xs-8'>
				<?= form_input ([
					'name' => 'name',
					'id' => 'name',
					'class' => 'form-control input-sm',
					'value' => $item_info->name
				]) ?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?= form_label(lang('Items.category'), 'category', ['class' => 'required control-label col-xs-3']) ?>
			<div class='col-xs-8'>
				<div class="input-group">
					<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-tag"></span></span>
					<?php
						if($config['category_dropdown'])
						{
							echo form_dropdown('category', $categories, $selected_category, ['class' => 'form-control']);
						}
						else
						{
							echo form_input ([
								'name' => 'category',
								'id' => 'category',
								'class' => 'form-control input-sm',
								'value' => $item_info->category
							]);
						}
					?>
				</div>
			</div>
		</div>

		<div id="attributes">
			<script type="text/javascript">
				$('#attributes').load('<?= "items/attributes/$item_info->item_id" ?>');
			</script>
		</div>

		<div class="form-group form-group-sm">
			<?= form_label(lang('Items.stock_type'), 'stock_type', !empty($basic_version) ? ['class' => 'required control-label col-xs-3'] : ['class' => 'control-label col-xs-3']) ?>
			<div class="col-xs-8">
				<label class="radio-inline">
					<?= form_radio ([
						'name' => 'stock_type',
						'type' => 'radio',
						'id' => 'stock_type',
						'value' => 0,
						'checked' => $item_info->stock_type == HAS_STOCK
					]) ?> <?= lang('Items.stock') ?>
				</label>
				<label class="radio-inline">
					<?= form_radio ([
						'name' => 'stock_type',
						'type' => 'radio',
						'id' => 'stock_type',
						'value' => 1,
						'checked' => $item_info->stock_type == HAS_NO_STOCK
					]) ?><?= lang('Items.nonstock') ?>
				</label>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?= form_label(lang('Items.type'), 'item_type', !empty($basic_version) ? ['class' => 'required control-label col-xs-3'] : ['class' => 'control-label col-xs-3']) ?>
			<div class="col-xs-8">
				<label class="radio-inline">
					<?php
						$radio_button = [
							'name' => 'item_type',
							'type' => 'radio',
							'id' => 'item_type',
							'value' => 0,
							'checked' => $item_info->item_type == ITEM
						];

						if($standard_item_locked)
						{
							$radio_button['disabled'] = true;
						}
						echo form_radio($radio_button) ?> <?= lang('Items.standard') ?>
				</label>
				<label class="radio-inline">
					<?php
						$radio_button = [
							'name' => 'item_type',
							'type' => 'radio',
							'id' => 'item_type',
							'value' => 1,
							'checked' => $item_info->item_type == ITEM_KIT
						];

						if($item_kit_disabled)
						{
							$radio_button['disabled'] = true;
						}
						echo form_radio($radio_button) ?> <?= lang('Items.kit') ?>
				</label>
				<?php
				if($config['derive_sale_quantity'] == '1')
				{
				?>
					<label class="radio-inline">
						<?= form_radio ([
							'name' => 'item_type',
							'type' => 'radio',
							'id' => 'item_type',
							'value' => 2,
							'checked' => $item_info->item_type == ITEM_AMOUNT_ENTRY
						]) ?><?= lang('Items.amount_entry') ?>
					</label>
				<?php
				}
				?>
				<?php
				if($allow_temp_item == 1)
				{
				?>
					<label class="radio-inline">
						<?= form_radio ([
							'name' => 'item_type',
							'type' => 'radio',
							'id' => 'item_type',
							'value' => 3,
							'checked' => $item_info->item_type == ITEM_TEMP
						]) ?> <?= lang('Items.temp') ?>
					</label>
				<?php
				}
				?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?= form_label(lang('Items.supplier'), 'supplier', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-8'>
				<?= form_dropdown('supplier_id', $suppliers, $selected_supplier, ['class' => 'form-control']) ?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?= form_label(lang('Items.cost_price'), 'cost_price', ['class' => 'required control-label col-xs-3']) ?>
			<div class="col-xs-4">
				<div class="input-group input-group-sm">
					<?php if(!is_right_side_currency_symbol()): ?>
						<span class="input-group-addon input-sm"><b><?= esc($config['currency_symbol']) ?></b></span>
					<?php endif; ?>
					<?= form_input ([
						'name' => 'cost_price',
						'id' => 'cost_price',
						'class' => 'form-control input-sm',
						'onClick' => 'this.select();',
						'value' => to_currency_no_money($item_info->cost_price)
					]) ?>
					<?php if(is_right_side_currency_symbol()): ?>
						<span class="input-group-addon input-sm"><b><?= esc($config['currency_symbol']) ?></b></span>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?= form_label(lang('Items.unit_price'), 'unit_price', ['class' => 'required control-label col-xs-3']) ?>
			<div class='col-xs-4'>
				<div class="input-group input-group-sm">
					<?php if (!is_right_side_currency_symbol()): ?>
						<span class="input-group-addon input-sm"><b><?= esc($config['currency_symbol']) ?></b></span>
					<?php endif; ?>
					<?= form_input ([
						'name' => 'unit_price',
						'id' => 'unit_price',
						'class' => 'form-control input-sm',
						'onClick' => 'this.select();',
						'value' => to_currency_no_money($item_info->unit_price)
					]) ?>
					<?php if (is_right_side_currency_symbol()): ?>
						<span class="input-group-addon input-sm"><b><?= esc($config['currency_symbol']) ?></b></span>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<?php
		if(!$use_destination_based_tax)
		{
		?>
			<div class="form-group form-group-sm">
				<?= form_label(lang('Items.tax_1'), 'tax_percent_1', ['class' => 'control-label col-xs-3']) ?>
				<div class='col-xs-4'>
					<?= form_input ([
						'name' => 'tax_names[]',
						'id' => 'tax_name_1',
						'class' => 'form-control input-sm',
						'value' => isset($item_tax_info[0]['name']) ? esc($item_tax_info[0]['name']) : esc($config['default_tax_1_name'])
					]) ?>
				</div>
				<div class="col-xs-4">
					<div class="input-group input-group-sm">
						<?= form_input ([
							'name' => 'tax_percents[]',
							'id' => 'tax_percent_name_1',
							'class' => 'form-control input-sm',
							'value' => isset($item_tax_info[0]['percent']) ? to_tax_decimals($item_tax_info[0]['percent']) : to_tax_decimals($default_tax_1_rate)
						]) ?>
						<span class="input-group-addon input-sm"><b>%</b></span>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Items.tax_2'), 'tax_percent_2', ['class' => 'control-label col-xs-3']) ?>
				<div class='col-xs-4'>
					<?= form_input ([
						'name' => 'tax_names[]',
						'id' => 'tax_name_2',
						'class' => 'form-control input-sm',
						'value' => $item_tax_info[1]['name'] ?? $config['default_tax_2_name']
					]) ?>
				</div>
				<div class="col-xs-4">
					<div class="input-group input-group-sm">
						<?= form_input ([
							'name' => 'tax_percents[]',
							'class' => 'form-control input-sm',
							'id' => 'tax_percent_name_2',
							'value' => isset($item_tax_info[1]['percent']) ? to_tax_decimals($item_tax_info[1]['percent']) : to_tax_decimals($default_tax_2_rate)
						]) ?>
						<span class="input-group-addon input-sm"><b>%</b></span>
					</div>
				</div>
			</div>
		<?php
		}
		?>

		<?php if($use_destination_based_tax): ?>
			<div class="form-group form-group-sm">
				<?= form_label(lang('Taxes.tax_category'), 'tax_category', ['class' => 'control-label col-xs-3']) ?>
				<div class='col-xs-8'>
					<div class="input-group input-group-sm">
						<?= form_input ([
							'name' => 'tax_category',
							'id' => 'tax_category',
							'class' => 'form-control input-sm',
							'size' => '50',
							'value' => $tax_category
						]) ?><?= form_hidden('tax_category_id', $tax_category_id) ?>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<?php if($include_hsn): ?>
			<div class="form-group form-group-sm">
				<?= form_label(lang('Items.hsn_code'), 'category', ['class' => 'control-label col-xs-3']) ?>
				<div class='col-xs-8'>
					<div class="input-group">
						<?= form_input ([
							'name' => 'hsn_code',
							'id' => 'hsn_code',
							'class' => 'form-control input-sm',
							'value' => $hsn_code
						]) ?>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<?php
		foreach($stock_locations as $key => $location_detail)
		{
		?>
			<div class="form-group form-group-sm">
				<?= form_label(lang('Items.quantity') . ' ' . $location_detail['location_name'], "quantity_$key", ['class' => 'required control-label col-xs-3']) ?>
				<div class='col-xs-4'>
					<?= form_input ([
						'name' => "quantity_$key",
						'id' => "quantity_$key",
						'class' => 'required quantity form-control',
						'onClick' => 'this.select();',
						'value' => isset($item_info->item_id) ? to_quantity_decimals($location_detail['quantity']) : to_quantity_decimals(0)
					]) ?>
				</div>
			</div>
		<?php
		}
		?>

		<div class="form-group form-group-sm">
			<?= form_label(lang('Items.receiving_quantity'), 'receiving_quantity', ['class' => 'required control-label col-xs-3']) ?>
			<div class='col-xs-4'>
				<?= form_input ([
					'name' => 'receiving_quantity',
					'id' => 'receiving_quantity',
					'class' => 'required form-control input-sm',
					'onClick' => 'this.select();',
					'value' => isset($item_info->item_id) ? to_quantity_decimals($item_info->receiving_quantity) : to_quantity_decimals(0)
				]) ?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?= form_label(lang('Items.reorder_level'), 'reorder_level', ['class' => 'required control-label col-xs-3']) ?>
			<div class='col-xs-4'>
				<?= form_input ([
					'name' => 'reorder_level',
					'id' => 'reorder_level',
					'class' => 'form-control input-sm',
					'onClick' => 'this.select();',
					'value' => isset($item_info->item_id) ? to_quantity_decimals($item_info->reorder_level) : to_quantity_decimals(0)
				]) ?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?= form_label(lang('Items.description'), 'description', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-8'>
				<?= form_textarea ([
						'name' => 'description',
						'id' => 'description',
						'class' => 'form-control input-sm',
						'value' => $item_info->description
				]) ?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?= form_label(lang('Items.image'), 'items_image', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-8'>
				<div class="fileinput <?= $logo_exists ? 'fileinput-exists' : 'fileinput-new' ?>" data-provides="fileinput">
					<div class="fileinput-new thumbnail" style="width: 100px; height: 100px;"></div>
					<div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 100px; max-height: 100px;">
						<img data-src="holder.js/100%x100%" alt="<?= lang('Items.image') ?>"
							 src="<?= $image_path ?>"
							 style="max-height: 100%; max-width: 100%;">
					</div>
					<div>
						<span class="btn btn-default btn-sm btn-file">
							<span class="fileinput-new"><?= lang('Items.select_image') ?></span>
							<span class="fileinput-exists"><?= lang('Items.change_image') ?></span>
							<input type="file" name="items_image" accept="image/*">
						</span>
						<a href="#" class="btn btn-default btn-sm fileinput-exists" data-dismiss="fileinput"><?= lang('Items.remove_image') ?></a>
					</div>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?= form_label(lang('Items.allow_alt_description'), 'allow_alt_description', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-1'>
				<?= form_checkbox ([
					'name' => 'allow_alt_description',
					'id' => 'allow_alt_description',
					'value' => 1,
					'checked' => $item_info->allow_alt_description == 1
				]) ?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?= form_label(lang('Items.is_serialized'), 'is_serialized', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-1'>
				<?= form_checkbox ([
					'name' => 'is_serialized',
					'id' => 'is_serialized',
					'value' => 1,
					'checked' => $item_info->is_serialized == 1
				]) ?>
			</div>
		</div>

		<?php
		if($config['multi_pack_enabled'] == '1')
		{
			?>
			<div class="form-group form-group-sm">
				<?= form_label(lang('Items.qty_per_pack'), 'qty_per_pack', ['class' => 'control-label col-xs-3']) ?>
				<div class='col-xs-4'>
					<?= form_input ([
						'name' => 'qty_per_pack',
						'id' => 'qty_per_pack',
						'class' => 'form-control input-sm',
						'value' => isset($item_info->item_id) ? to_quantity_decimals($item_info->qty_per_pack) : to_quantity_decimals(0)
					]) ?>
				</div>
			</div>
			<div class="form-group form-group-sm">
				<?= form_label(lang('Items.pack_name'), 'name', ['class' => 'control-label col-xs-3']) ?>
				<div class='col-xs-8'>
					<?= form_input ([
						'name' => 'pack_name',
						'id' => 'pack_name',
						'class' => 'form-control input-sm',
						'value' => $item_info->pack_name
					]) ?>
				</div>
			</div>
			<div class="form-group  form-group-sm">
				<?= form_label(lang('Items.low_sell_item'), 'low_sell_item_name', ['class' => 'control-label col-xs-3']) ?>
				<div class='col-xs-8'>
					<div class="input-group input-group-sm">
						<?= form_input ([
							'name' => 'low_sell_item_name',
							'id' => 'low_sell_item_name',
							'class' => 'form-control input-sm',
							'value' => $selected_low_sell_item
						]) ?><?= form_hidden('low_sell_item_id', $selected_low_sell_item_id) ?>
					</div>
				</div>
			</div>
			<?php
		}
		?>

		<div class="form-group form-group-sm">
			<?= form_label(lang('Items.is_deleted'), 'is_deleted', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-1'>
				<?= form_checkbox ([
					'name' => 'is_deleted',
					'id' => 'is_deleted',
					'value'=>1,
					'checked' => $item_info->deleted == 1
				]) ?>
			</div>
		</div>

	</fieldset>
<?= form_close() ?>

<script type="text/javascript">
//validation and submit handling
$(document).ready(function()
{
	$('#new').click(function() {
		let stay_open = true;
		$('#item_form').submit();
	});

	$('#submit').click(function() {
		let stay_open = false;
	});

	$("input[name='tax_category']").change(function() {
		!$(this).val() && $(this).val('');
	});

	var fill_tax_category_value = function(event, ui) {
		event.preventDefault();
		$("input[name='tax_category_id']").val(ui.item.value);
		$("input[name='tax_category']").val(ui.item.label);
	};

	$('#tax_category').autocomplete({
		source: "<?= 'taxes/suggestTaxCategories' ?>",
		minChars: 0,
		delay: 15,
		cacheLength: 1,
		appendTo: '.modal-content',
		select: fill_tax_category_value,
		focus: fill_tax_category_value
	});

	var fill_low_sell_value = function(event, ui) {
		event.preventDefault();
		$("input[name='low_sell_item_id']").val(ui.item.value);
		$("input[name='low_sell_item_name']").val(ui.item.label);
	};

	$('#low_sell_item_name').autocomplete({
		source: "<?= 'items/suggestLowSell' ?>",
		minChars: 0,
		delay: 15,
		cacheLength: 1,
		appendTo: '.modal-content',
		select: fill_low_sell_value,
		focus: fill_low_sell_value
	});

	$('#category').autocomplete({
		source: "<?= 'items/suggestCategory' ?>",
		delay: 10,
		appendTo: '.modal-content'
	});

	$('a.fileinput-exists').click(function() {
		$.ajax({
			type: 'GET',
			url: '<?= "$controller_name/removeLogo/$item_info->item_id" ?>',
			dataType: 'json'
		})
	});

	$.validator.addMethod('valid_chars', function(value, element) {
		return value.match(/(\||_)/g) == null;
	}, "<?= lang('Attributes.attribute_value_invalid_chars') ?>");

	var init_validation = function() {
		$('#item_form').validate($.extend({
			submitHandler: function(form, event) {//event is not used as a parameter here
				$(form).ajaxSubmit({
					success: function(response) {
						let stay_open = dialog_support.clicked_id() != 'submit';
						if(stay_open)
						{
							// set action of item_form to url without item id, so a new one can be created
							$('#item_form').attr('action', "<?= 'items/save/' ?>");
							// use a whitelist of fields to minimize unintended side effects
							$(':text, :password, :file, #description, #item_form').not('.quantity, #reorder_level, #tax_name_1, #receiving_quantity, ' +
								'#tax_percent_name_1, #category, #reference_number, #name, #cost_price, #unit_price, #taxed_cost_price, #taxed_unit_price, #definition_name, [name^="attribute_links"]').val('');
							// de-select any checkboxes, radios and drop-down menus
							$(':input', '#item_form').removeAttr('checked').removeAttr('selected');
						}
						else
						{
							dialog_support.hide();
						}
						table_support.handle_submit('<?= 'items' ?>', response, stay_open);
						init_validation();
					},
					dataType: 'json'
				});
			},

			errorLabelContainer: '#error_message_box',

			rules:
			{
				name: 'required',
				category: 'required',
				item_number:
				{
					required: false,
					remote:
					{
						url: "<?= esc("$controller_name/checkItemNumber") ?>",
						type: 'POST',
						data: {
							'item_id' : "<?= $item_info->item_id ?>"
							// item_number should be passed into the function by default
						}
					}
				},
				cost_price:
				{
					required: true,
					remote: "<?= esc("$controller_name/checkNumeric") ?>"
				},
				unit_price:
				{
					required: true,
					remote: "<?= esc("$controller_name/checkNumeric") ?>"
				},
				<?php
				foreach($stock_locations as $key=>$location_detail)
				{
					?>
					<?= 'quantity_' . $key ?>:
					{
						required: true,
						remote: "<?= esc("$controller_name/checkNumeric") ?>"
					},
					<?php
				}
				?>
				receiving_quantity:
				{
					required: true,
					remote: "<?= esc("$controller_name/checkNumeric") ?>"
				},
				reorder_level:
				{
					required: true,
					remote: "<?= esc("$controller_name/checkNumeric") ?>"
				},
				tax_percent:
				{
					required: false,
					remote: "<?= esc("$controller_name/checkNumeric") ?>"
				}
			},

			messages:
			{
				name: "<?= lang('Items.name_required') ?>",
				item_number: "<?= lang('Items.item_number_duplicate') ?>",
				category: "<?= lang('Items.category_required') ?>",
				cost_price:
				{
					required: "<?= lang('Items.cost_price_required') ?>",
					number: "<?= lang('Items.cost_price_number') ?>"
				},
				unit_price:
				{
					required: "<?= lang('Items.unit_price_required') ?>",
					number: "<?= lang('Items.unit_price_number') ?>"
				},
				<?php
				foreach($stock_locations as $key => $location_detail)
				{
				?>
				<?= esc("quantity_$key", 'js') ?>:
					{
						required: "<?= lang('Items.quantity_required') ?>",
						number: "<?= lang('Items.quantity_number') ?>"
					},
				<?php
				}
				?>
				receiving_quantity:
				{
					required: "<?= lang('Items.quantity_required') ?>",
					number: "<?= lang('Items.quantity_number') ?>"
				},
				reorder_level:
				{
					required: "<?= lang('Items.reorder_level_required') ?>",
					number: "<?= lang('Items.reorder_level_number') ?>"
				},
				tax_percent:
				{
					number: "<?= lang('Items.tax_percent_number') ?>"
				}
			}
		}, form_support.error))
	};

	init_validation();
});
</script>
