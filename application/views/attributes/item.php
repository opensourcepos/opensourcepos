<?php
foreach($definition_values as $definition_value)
{
	if ($definition_value['definition_type'] == CATEGORY)
	{
		continue;
	}
?>

	<div class="form-group form-group-sm">
		<?php echo form_label($definition_value['definition_name'], $definition_value['definition_name'], array('class'=>'control-label col-xs-3')); ?>
		<div class='col-xs-8'>
			<div class="input-group">
				<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-tag"></span></span>
				<?php
				$definition_id = $definition_value['definition_id'];
				$definition_name = 'definition_' . $definition_id;
				if ($definition_value['definition_type'] == DATE)
				{
					echo form_input(array(
						'name' => $definition_name,
						'value' => date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), strtotime($definition_value['attribute_value'])),
						'class' => 'form-control input-sm',
						'data-definition-id' => $definition_value['definition_id'],
						'readonly' => 'true'));
				}
				else if ($definition_value['definition_type'] == DROPDOWN)
				{
					$values = $this->Attribute->get_definition_values($definition_id);
					$selected_value = $this->Attribute->get_link_value($item_id, $definition_id);
					echo form_dropdown($definition_name, $values, (empty($selected_value) ? NULL : $selected_value->attribute_id), "class='form-control' data-definition-id='$definition_id'");
				}
				else if ($definition_value['definition_type'] == TEXT)
				{
					$attribute_value = $this->Attribute->get_attribute_value($item_id, $definition_id);
					$value = (empty($attribute_value) || empty($attribute_value->attribute_value)) ? NULL : $attribute_value->attribute_value;
					$id = (empty($attribute_value) || empty($attribute_value->attribute_id)) ? NULL : $attribute_value->attribute_id;
					echo form_input($definition_name, $value, "class='form-control' data-attribute-id='$id'");
				}
				?>
			</div>
		</div>
	</div>

	<script type="text/javascript">
		(function() {
			$('input[name="<?php echo $definition_name; ?>"]').autocomplete({
					source: '<?php echo site_url("attributes/suggest_attribute/$definition_id");?>',
					appendTo: '.modal-content',
					select: function (a, ui) {
						$(this).data('attribute_id', ui.item.value);
						$(this).val(ui.item.label);
					},
					delay:10
			});

			$("input[name*='definition'][type='text']").change(function() {
				$.post('<?php echo site_url("attributes/save_attribute_value/");?>' + $(this).val(), {
					item_id: <?php echo $item_id; ?>,
					definition_id: <?php echo $definition_id; ?>,
					attribute_id: $(this).data('attribute-id')
				});
			});
		})();
	</script>

<?php
}
?>
