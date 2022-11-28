<?php
/**
 * @var array $definition_names
 * @var array $definition_values
 * @var int $item_id
 */
?>
<div class="form-group form-group-sm">
	<?php echo form_label(lang('Attributes.definition_name'), 'definition_name_label', ['class' => 'control-label col-xs-3']) ?>
	<div class='col-xs-8'>
		<?php echo form_dropdown('definition_name', esc($definition_names, 'attr'), -1, ['id' => 'definition_name', 'class' => 'form-control']) ?>
	</div>

</div>

<?php
foreach($definition_values as $definition_id => $definition_value)
{
?>

<div class="form-group form-group-sm">
	<?php echo form_label(esc($definition_value['definition_name']), esc($definition_value['definition_name'], 'attr'), ['class' => 'control-label col-xs-3']) ?>
	<div class='col-xs-8'>
		<div class="input-group">
			<?php
				echo form_hidden(esc("attribute_ids[$definition_id]", 'attr'), esc($definition_value['attribute_id'], 'attr'));
				$attribute_value = $definition_value['attribute_value'];

				if ($definition_value['definition_type'] == DATE)
				{
					$value = (empty($attribute_value) || empty($attribute_value->attribute_date)) ? NOW : strtotime($attribute_value->attribute_date);
					echo form_input ([
						'name' => esc("attribute_links[$definition_id]", 'attr'),
						'value' => to_date($value),
						'class' => 'form-control input-sm datetime',
						'data-definition-id' => $definition_id,
						'readonly' => 'true'
					]);
				}
				else if ($definition_value['definition_type'] == DROPDOWN)	//TODO: === ?
				{
					$selected_value = $definition_value['selected_value'];
					echo form_dropdown(esc("attribute_links[$definition_id]", 'attr'), esc($definition_value['values'], 'attr'), esc($selected_value, 'attr'), "class='form-control' data-definition-id='$definition_id'");
				}
				else if ($definition_value['definition_type'] == TEXT)	//TODO: === ?
				{
					$value = (empty($attribute_value) || empty($attribute_value->attribute_value)) ? $definition_value['selected_value'] : $attribute_value->attribute_value;
					echo form_input(esc("attribute_links[$definition_id]"), esc($value, 'attr'), "class='form-control valid_chars' data-definition-id='$definition_id'");
				}
				else if ($definition_value['definition_type'] == DECIMAL)	//TODO: === ?
				{
					$value = (empty($attribute_value) || empty($attribute_value->attribute_decimal)) ? $definition_value['selected_value'] : $attribute_value->attribute_decimal;
					echo form_input(esc("attribute_links[$definition_id]"), esc($value, 'attr'), "class='form-control valid_chars' data-definition-id='$definition_id'");
				}
				else if ($definition_value['definition_type'] == CHECKBOX)	//TODO: === ?
				{
					$value = (empty($attribute_value) || empty($attribute_value->attribute_value)) ? $definition_value['selected_value'] : $attribute_value->attribute_value;

				//Sends 0 if the box is unchecked instead of not sending anything.
					echo form_input ([
						'type' => 'hidden',
						'name' => esc("attribute_links[$definition_id]", 'attr'),
						'id' => "attribute_links[$definition_id]",
						'value' => 0,
						'data-definition-id' => $definition_id
					]);
					echo form_checkbox ([
						'name' => esc("attribute_links[$definition_id]", 'attr'),
						'id' => "attribute_links[$definition_id]",
						'value' => 1,
						'checked' => ($value ? 1 : 0),
						'class' => 'checkbox-inline',
						'data-definition-id' => $definition_id
					]);
				}
			?>
			<span class="input-group-addon input-sm btn btn-default remove_attribute_btn"><span class="glyphicon glyphicon-trash"></span></span>
		</div>
	</div>
</div>

<?php
}
?>

<script type="text/javascript">
(function() {
		<?php echo view('partial/datepicker_locale', ['config' => '{ minView: 2, format: "'.dateformat_bootstrap(config('OSPOS')->settings['dateformat'] . '"}')]) ?>

		var enable_delete = function() {
			$('.remove_attribute_btn').click(function() {
				$(this).parents('.form-group').remove();
			});
		};

		enable_delete();

		$("input[name*='attribute_links']").change(function() {
			var definition_id = $(this).data('definition-id');
			$("input[name='attribute_ids[" + definition_id + "]']").val('');
		}).autocomplete({
			source: function(request, response) {
				$.get('<?php echo site_url('attributes/suggest_attribute/') ?>' + this.element.data('definition-id') + '?term=' + request.term, function(data) {
					return response(data);
				}, 'json');
			},
			appendTo: '.modal-content',
			select: function (event, ui) {
				event.preventDefault();
				$(this).val(ui.item.label);
			},
			delay: 10
		});

		var definition_values = function() {
			var result = {};
			$("[name*='attribute_links'").each(function() {
				var definition_id = $(this).data('definition-id');
				result[definition_id] = $(this).val();
			});
			return result;
		};

		var refresh = function() {
			var definition_id = $("#definition_name option:selected").val();
			var attribute_values = definition_values();
			attribute_values[definition_id] = '';
			$('#attributes').load('<?php echo esc(site_url("items/attributes/$item_id"), 'url') ?>', {
				'definition_ids': JSON.stringify(attribute_values)
			}, enable_delete);
		};

		$('#definition_name').change(function() {
			refresh();
		});
	})();
</script>