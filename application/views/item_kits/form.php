<div id="required_fields_message"><?= $this->lang->line('common_fields_required_message'); ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?= form_open('item_kits/save/' . $item_kit_info->item_kit_id, array('id' => 'item_kit_form', 'class' => 'form-horizontal')); ?>
<fieldset id="item_kit_basic_info">
	<div class="form-group form-group-sm">
		<?= form_label($this->lang->line('item_kits_item_kit_number'), 'item_kit_number', array('class' => 'control-label col-xs-3')); ?>
		<div class='col-xs-8'>
			<div class="input-group">
				<span class="input-group-addon input-sm"><i class="bi bi-upc"></i></span>
				<?= form_input(
					array(
						'name' => 'item_kit_number',
						'id' => 'item_kit_number',
						'class' => 'form-control input-sm',
						'value' => $item_kit_info->item_kit_number
					)
				); ?>
			</div>
		</div>
	</div>

	<div class="form-group form-group-sm">
		<?= form_label($this->lang->line('item_kits_name'), 'name', array('class' => 'required control-label col-xs-3')); ?>
		<div class='col-xs-8'>
			<?= form_input(
				array(
					'name' => 'name',
					'id' => 'name',
					'class' => 'form-control input-sm',
					'value' => $item_kit_info->name
				)
			); ?>
		</div>
	</div>

	<div class="form-group  form-group-sm">
		<?= form_label($this->lang->line('item_kits_find_kit_item'), 'item_name', array('class' => 'control-label col-xs-3')); ?>
		<div class='col-xs-8'>
			<div class="input-group input-group-sm">
				<?= form_input(
					array(
						'name' => 'item_name',
						'id' => 'item_name',
						'class' => 'form-control input-sm',
						'size' => '50',
						'value' => $selected_kit_item
					)
				); ?>
				<?= form_hidden('kit_item_id', $selected_kit_item_id); ?>

			</div>
		</div>
	</div>

	<div class="form-group form-group-sm">
		<?= form_label($this->lang->line('item_kits_discount_type'), 'kit_discount_type', array('class' => 'control-label col-xs-3')); ?>
		<div class="col-xs-8">
			<label class="radio-inline">
				<?= form_radio(
					array(
						'name' => 'kit_discount_type',
						'type' => 'radio',
						'value' => 0,
						'checked' => $item_kit_info->kit_discount_type == PERCENT
					)
				); ?> <?= $this->lang->line('item_kits_discount_percent'); ?>
			</label>
			<label class="radio-inline">
				<?= form_radio(
					array(
						'name' => 'kit_discount_type',
						'type' => 'radio',
						'value' => 1,
						'checked' => $item_kit_info->kit_discount_type == FIXED
					)
				); ?> <?= $this->lang->line('item_kits_discount_fixed'); ?>
			</label>
		</div>
	</div>

	<div class="form-group form-group-sm">
		<?= form_label($this->lang->line('item_kits_discount'), 'kit_discount', array('class' => 'control-label col-xs-3')); ?>
		<div class='col-xs-3'>
			<div class="input-group input-group-sm">
				<?= form_input(
					array(
						'name' => 'kit_discount',
						'size' => '5',
						'maxlength' => '5',
						'id' => 'kit_discount',
						'class' => 'form-control input-sm',
						'value' => $item_kit_info->kit_discount
					)
				); ?>
			</div>
		</div>
	</div>

	<div class="form-group form-group-sm">
		<?= form_label($this->lang->line('item_kits_price_option'), 'price_option', !empty($basic_version) ? array('class' => 'required control-label col-xs-3') : array('class' => 'control-label col-xs-3')); ?>
		<div class="col-xs-8">
			<label class="radio-inline">
				<?= form_radio(
					array(
						'name' => 'price_option',
						'type' => 'radio',
						'value' => 0,
						'checked' => $item_kit_info->price_option == PRICE_ALL
					)
				); ?> <?= $this->lang->line('item_kits_kit_and_components'); ?>
			</label>
			<label class="radio-inline">
				<?= form_radio(
					array(
						'name' => 'price_option',
						'type' => 'radio',
						'value' => 1,
						'checked' => $item_kit_info->price_option == PRICE_KIT
					)
				); ?> <?= $this->lang->line('item_kits_kit_only'); ?>
			</label>
			<label class="radio-inline">
				<?= form_radio(
					array(
						'name' => 'price_option',
						'type' => 'radio',
						'value' => 2,
						'checked' => $item_kit_info->price_option == PRICE_KIT_ITEMS
					)
				); ?> <?= $this->lang->line('item_kits_kit_and_stock'); ?>
			</label>

		</div>
	</div>

	<div class="form-group form-group-sm">
		<?= form_label($this->lang->line('item_kits_print_option'), 'print_option', !empty($basic_version) ? array('class' => 'required control-label col-xs-3') : array('class' => 'control-label col-xs-3')); ?>
		<div class="col-xs-8">
			<label class="radio-inline">
				<?= form_radio(
					array(
						'name' => 'print_option',
						'type' => 'radio',
						'value' => 0,
						'checked' => $item_kit_info->print_option == PRINT_ALL
					)
				); ?> <?= $this->lang->line('item_kits_all'); ?>
			</label>
			<label class="radio-inline">
				<?= form_radio(
					array(
						'name' => 'print_option',
						'type' => 'radio',
						'value' => 1,
						'checked' => $item_kit_info->print_option == PRINT_PRICED
					)
				); ?> <?= $this->lang->line('item_kits_priced_only'); ?>
			</label>
			<label class="radio-inline">
				<?= form_radio(
					array(
						'name' => 'print_option',
						'type' => 'radio',
						'value' => 2,
						'checked' => $item_kit_info->print_option == PRINT_KIT
					)
				); ?> <?= $this->lang->line('item_kits_kit_only'); ?>
			</label>

		</div>
	</div>


	<div class="form-group form-group-sm">
		<?= form_label($this->lang->line('item_kits_description'), 'description', array('class' => 'control-label col-xs-3')); ?>
		<div class='col-xs-8'>
			<?= form_textarea(
				array(
					'name' => 'description',
					'id' => 'description',
					'class' => 'form-control input-sm',
					'value' => $item_kit_info->description
				)
			); ?>
		</div>
	</div>

	<div class="form-group form-group-sm">
		<?= form_label($this->lang->line('item_kits_add_item'), 'item', array('class' => 'control-label col-xs-3')); ?>
		<div class='col-xs-8'>
			<?= form_input(
				array(
					'name' => 'item',
					'id' => 'item',
					'class' => 'form-control input-sm'
				)
			); ?>
		</div>
	</div>

	<table id="item_kit_items" class="table table-striped table-hover">
		<thead>
			<tr>
				<th width="10%"><?= $this->lang->line('common_delete'); ?></th>
				<th width="10%"><?= $this->lang->line('item_kits_sequence'); ?></th>
				<th width="60%"><?= $this->lang->line('item_kits_item'); ?></th>
				<th width="20%"><?= $this->lang->line('item_kits_quantity'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ($item_kit_items as $item_kit_item) {
			?>
				<tr>
					<td><a href='#' onclick='return delete_item_kit_row(this);'><i class="bi bi-trash"></i></a></td>
					<td><input class='quantity form-control input-sm' id='item_seq_<?= $item_kit_item['item_id'] ?>' name=item_kit_seq[<?= $item_kit_item['item_id'] ?>] value="<?= to_quantity_decimals($item_kit_item['kit_sequence']) ?>" /></td>
					<td><?= $item_kit_item['name']; ?></td>
					<td><input class='quantity form-control input-sm' id='item_qty_<?= $item_kit_item['item_id'] ?>' name=item_kit_qty[<?= $item_kit_item['item_id'] ?>] value="<?= to_quantity_decimals($item_kit_item['quantity']) ?>" /></td>
				</tr>
			<?php
			}
			?>
		</tbody>
	</table>
</fieldset>

<?= form_close(); ?>

<script type="text/javascript">
	//validation and submit handling
	$(document).ready(function() {
		$('#item').autocomplete({
			source: "<?= site_url('items/suggest'); ?>",
			minChars: 0,
			autoFocus: false,
			delay: 10,
			appendTo: '.modal-content',
			select: function(e, ui) {
				if ($('#item_kit_item_' + ui.item.value).length == 1) {
					$('#item_kit_item_' + ui.item.value).val(parseFloat($('#item_kit_item_' + ui.item.value).val()) + 1);
				} else {
					$('#item_kit_items').append('<tr>' +
						"<td><a href='#' onclick='return delete_item_kit_row(this);'><i class='bi bi-trash'></i></a></td>" +
						"<td><input class='quantity form-control input-sm' id='item_seq_" + ui.item.value + "' name=item_kit_seq[" + ui.item.value + "] value='0'/></td>" +
						'<td>' + ui.item.label + '</td>' +
						"<td><input class='quantity form-control input-sm' id='item_qty_" + ui.item.value + "' name=item_kit_qty[" + ui.item.value + "] value='1'/></td>" +
						'</tr>');
				}
				$('#item').val('');
				return false;
			}
		});

		$("input[name='item_name']").change(function() {
			if (!$("input[name='item_name']").val()) {
				$("input[name='kit_item_id']").val('');
			}
		});

		var fill_value = function(event, ui) {
			event.preventDefault();
			$("input[name='kit_item_id']").val(ui.item.value);
			$("input[name='item_name']").val(ui.item.label);
		};


		$('#item_name').autocomplete({
			source: "<?= site_url('items/suggest_kits'); ?>",
			minChars: 0,
			delay: 15,
			cacheLength: 1,
			appendTo: '.modal-content',
			select: fill_value,
			focus: fill_value
		});

		$('#item_kit_form').validate($.extend({
			submitHandler: function(form) {
				$(form).ajaxSubmit({
					success: function(response) {
						dialog_support.hide();
						table_support.handle_submit("<?= site_url($controller_name); ?>", response);
					},
					dataType: 'json'
				});
			},

			errorLabelContainer: '#error_message_box',

			rules: {
				name: 'required',
				category: 'required',
				item_kit_number: {
					required: false,
					remote: {
						url: "<?= site_url($controller_name . '/check_item_number') ?>",
						type: 'POST',
						data: {
							'item_kit_id': "<?= $item_kit_info->item_kit_id; ?>",
							'item_kit_number': function() {
								return $('#item_kit_number').val();
							}
						}
					}
				}
			},

			messages: {
				name: "<?= $this->lang->line('items_name_required'); ?>",
				category: "<?= $this->lang->line('items_category_required'); ?>",
				item_kit_number: "<?= $this->lang->line('item_kits_item_number_duplicate'); ?>"
			}
		}, form_support.error));
	});

	function delete_item_kit_row(link) {
		$(link).parent().parent().remove();
		return false;
	}
</script>