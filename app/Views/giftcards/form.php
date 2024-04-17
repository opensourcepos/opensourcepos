<?php
/**
 * @var int $giftcard_id
 * @var string $selected_person_name
 * @var int $selected_person_id
 * @var string $giftcard_number
 * @var float $giftcard_value
 * @var string $controller_name
 * @var array $config
 */
?>
<div id="required_fields_message"><?= lang('Common.fields_required_message') ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?= form_open("giftcards/save/$giftcard_id", ['id' => 'giftcard_form', 'class' => 'form-horizontal']) ?>
	<fieldset id="giftcard_basic_info">
		<div class="form-group form-group-sm">
			<?= form_label(lang('Giftcards.person_id'), 'person_name', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-8'>
				<?= form_input ([
					'name' => 'person_name',
					'id' => 'person_name',
					'class' => 'form-control input-sm',
					'value' => $selected_person_name
				]) ?>
				<?= form_hidden('person_id', (string)$selected_person_id) ?>
			</div>
		</div>

		<?php
		$class = '';
		if($config['giftcard_number'] == 'series')
		{
			$class = ' required';
		}
		?>
		<div class="form-group form-group-sm">
			<?= form_label(lang('Giftcards.giftcard_number'), 'giftcard_number', ['class'=>"control-label col-xs-3$class"]) ?>
			<div class="col-xs-4'>
				<?= form_input ([
					'name' => 'giftcard_number',
					'id' => 'giftcard_number',
					'class' => 'form-control input-sm',
					'value' => $giftcard_number
				]) ?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?= form_label(lang('Giftcards.card_value'), 'giftcard_amount', ['class' => 'required control-label col-xs-3']) ?>
			<div class='col-xs-4'>
				<div class="input-group input-group-sm">
					<?php if (!is_right_side_currency_symbol()): ?>
						<span class="input-group-addon input-sm"><?= esc($config['currency_symbol']) ?></span>
					<?php endif; ?>
					<?= form_input ([
						'name' => 'giftcard_amount',
						'id' => 'giftcard_amount',
						'class' => 'form-control input-sm',
						'value'=>to_currency_no_money($giftcard_value)
					]) ?>
					<?php if (is_right_side_currency_symbol()): ?>
						<span class="input-group-addon input-sm"><b><?= esc($config['currency_symbol']) ?></span>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</fieldset>
<?= form_close() ?>

<script type="application/javascript">
//validation and submit handling
$(document).ready(function()
{
	$("input[name='person_name']").change(function() {
		!$(this).val() && $(this).val('');
	});

	var fill_value = function(event, ui) {
		event.preventDefault();
		$("input[name='person_id']").val(ui.item.value);
		$("input[name='person_name']").val(ui.item.label);
	};

	$('#person_name').autocomplete({
		source: "<?= esc("customers/suggest") ?>",
		minChars: 0,
		delay: 15,
	   	cacheLength: 1,
		appendTo: '.modal-content',
		select: fill_value,
		focus: fill_value
	});

	$('#giftcard_form').validate($.extend({
		submitHandler: function(form) {
			$(form).ajaxSubmit({
				success: function(response)
				{
					dialog_support.hide();
					table_support.handle_submit("<?= esc($controller_name) ?>", response);
				},
				error: function(jqXHR, textStatus, errorThrown)
				{
					table_support.handle_submit("<?= esc($controller_name) ?>", {message: errorThrown});
				},
				dataType: 'json'
			});
		},

		errorLabelContainer: '#error_message_box',

		rules:
		{
			<?php
			if($config['giftcard_number'] == 'series')
			{
			?>
			giftcard_number:
 			{
 				required: true,
 				number: true
 			},
 			<?php
			}
			?>
			giftcard_amount:
			{
				required: true,
				remote:
				{
					url: "<?= esc("$controller_name/checkNumberGiftcard") ?>",
					type: 'POST',
					data: {
						'amount': $('#giftcard_amount').val()
					},
					dataFilter: function(data) {
						var response = JSON.parse(data);
						$('#giftcard_amount').val(response.giftcard_amount);
						return response.success;
					}
				}
			}
		},

		messages:
		{
			<?php
			if($config['giftcard_number'] == 'series')
			{
			?>
				giftcard_number:
				{
					required: "<?= lang('Giftcards.number_required') ?>",
					number: "<?= lang('Giftcards.number') ?>"
				},
 			<?php
			}
			?>
			giftcard_amount:
			{
				required: "<?= lang('Giftcards.value_required') ?>",
				remote: "<?= lang('Giftcards.value') ?>"
			}
		}
	}, form_support.error));
});
</script>
