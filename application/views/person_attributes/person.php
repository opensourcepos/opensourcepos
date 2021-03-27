<div class="form-group form-group-sm">
	<?php echo form_label($this->lang->line("person_attributes_definition_name"), "definition_name_label", array('class' => 'control-label col-xs-3')); ?>
	<div class='col-xs-8'>
		<?php echo form_dropdown('definition_name', $definition_names, -1, array('id' => 'definition_name', 'class' => 'form-control')); ?>
	</div>

</div>

<?php
foreach($definition_values as $definition_id => $definition_value)
{
?>

<div class="form-group form-group-sm">
	<?php echo form_label($definition_value['definition_name'], $definition_value['definition_name'], array('class' => 'control-label col-xs-3')); ?>
	<div class='col-xs-8'>
		<div class="input-group">
			<?php
				echo form_hidden("person_attribute_ids[$definition_id]", $definition_value['person_attribute_id']);
				$person_attribute_value = $definition_value['person_attribute_value'];

				if ($definition_value['definition_type'] == DATE)
				{
					$value = (empty($person_attribute_value) || empty($person_attribute_value->person_attribute_date)) ? NOW : strtotime($person_attribute_value->person_attribute_date);
					echo form_input(array(
						'name' => "person_attribute_links[$definition_id]",
						'value' => to_date($value),
						'class' => 'form-control input-sm datetime',
						'data-definition-id' => $definition_id,
						'readonly' => 'true'));
				}
				else if ($definition_value['definition_type'] == DROPDOWN)
				{
					$selected_value = $definition_value['selected_value'];
					echo form_dropdown("person_attribute_links[$definition_id]", $definition_value['values'], $selected_value, "class='form-control' data-definition-id='$definition_id'");
				}
				else if ($definition_value['definition_type'] == TEXT)
				{
					$value = (empty($person_attribute_value) || empty($person_attribute_value->person_attribute_value)) ? $definition_value['selected_value'] : $person_attribute_value->person_attribute_value;
					echo form_input("person_attribute_links[$definition_id]", $value, "class='form-control valid_chars' data-definition-id='$definition_id'");
				}
				else if ($definition_value['definition_type'] == DECIMAL)
				{
					$value = (empty($person_attribute_value) || empty($person_attribute_value->person_attribute_decimal)) ? $definition_value['selected_value'] : $person_attribute_value->person_attribute_decimal;
					echo form_input("person_attribute_links[$definition_id]", $value, "class='form-control valid_chars' data-definition-id='$definition_id'");
				}
				else if ($definition_value['definition_type'] == CHECKBOX)
				{
					$value = (empty($person_attribute_value) || empty($person_attribute_value->person_attribute_value)) ? $definition_value['selected_value'] : $person_attribute_value->person_attribute_value;

				//Sends 0 if the box is unchecked instead of not sending anything.
					echo form_input(array(
						'type' => 'hidden',
						'name' => "person_attribute_links[$definition_id]",
						'id' => "person_attribute_links[$definition_id]",
						'value' => 0,
						'data-definition-id' => $definition_id
					));
					echo form_checkbox(array(
						'name' => "person_attribute_links[$definition_id]",
						'id' => "person_attribute_links[$definition_id]",
						'value' => 1,
						'checked' => ($value ? 1 : 0),
						'class' => 'checkbox-inline',
						'data-definition-id' => $definition_id
					));
				}
			?>
			<span class="input-group-addon input-sm btn btn-default remove_person_attribute_btn"><span class="glyphicon glyphicon-trash"></span></span>
		</div>
	</div>
</div>

<?php
}
?>

<script type="text/javascript">
(function() {
		<?php $this->load->view('partial/datepicker_locale', array('config' => '{ minView: 2, format: "'.dateformat_bootstrap($this->config->item('dateformat') . '"}'))); ?>

		var enable_delete = function() {
			$('.remove_person_attribute_btn').click(function() {
				$(this).parents('.form-group').remove();
			});
		};

		enable_delete();

		$("input[name*='person_attribute_links']").change(function() {
			var definition_id = $(this).data('definition-id');
			$("input[name='person_attribute_ids[" + definition_id + "]']").val('');
		}).autocomplete({
			source: function(request, response) {
				$.get('<?php echo site_url('person_attributes/suggest_person_attribute/');?>' + this.element.data('definition-id') + '?term=' + request.term, function(data) {
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
			$("[name*='person_attribute_links'").each(function() {
				var definition_id = $(this).data('definition-id');
				result[definition_id] = $(this).val();
			});
			return result;
		};

		var refresh = function() {
			var definition_id = $("#definition_name option:selected").val();
			var person_attribute_values = definition_values();
			person_attribute_values[definition_id] = '';
			$('#person_attributes').load('<?php echo site_url($controller_name . "/person_attributes/$person_id");?>', {
				'definition_ids': JSON.stringify(person_attribute_values)
			}, enable_delete);
		};

		$('#definition_name').change(function() {
			refresh();
		});
	})();
</script>