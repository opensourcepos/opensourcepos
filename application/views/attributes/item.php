<?php
foreach($definition_values as $definition_value)
{
    ?>

<div class="form-group form-group-sm">
    <?php echo form_label($definition_value['definition_name'], $definition_value['definition_name'], array('class' => 'control-label col-xs-3')); ?>
    <div class='col-xs-8'>
        <div class="input-group">
            <span class="input-group-addon input-sm"><span class="glyphicon glyphicon-tag"></span></span>
            <?php
            $definition_id = $definition_value['definition_id'];

            if ($definition_value['definition_type'] == DATE)
            {
                echo form_input(array(
                    'name' => 'definition_name[]',
                    'value' => date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), strtotime($definition_value['attribute_value'])),
                    'class' => 'form-control input-sm',
                    'data-definition-id' => $definition_value['definition_id'],
                    'readonly' => 'true'));
            }
            else if ($definition_value['definition_type'] == DROPDOWN)
            {
                $values = $this->Attribute->get_definition_values($definition_id);
                $selected_value = $this->Attribute->get_link_value($item_id, $definition_id);
                echo form_dropdown('definition_name[]', $values, (empty($selected_value) ? NULL : $selected_value->attribute_id), "class='form-control' data-definition-id='$definition_id'");
            }
            else if ($definition_value['definition_type'] == TEXT)
            {
                $attribute_value = $this->Attribute->get_attribute_value($item_id, $definition_id);
                $value = (empty($attribute_value) || empty($attribute_value->attribute_value)) ? NULL : $attribute_value->attribute_value;
                $id = (empty($attribute_value) || empty($attribute_value->attribute_id)) ? NULL : $attribute_value->attribute_id;
                echo form_input('definition_name[]', $value, "class='form-control' data-attribute-id='$id'");
            }
            ?>
        </div>
    </div>
</div>

    <?php
}
?>


<div class="form-group form-group-sm">
	<?php echo form_label($this->lang->line("attributes_definition_name"), "definition_name_label", array('class' => 'control-label col-xs-3')); ?>
    <div class='col-xs-8'>
        <?php echo form_dropdown('definition_name', $definition_names, -1, array('id' => 'definition_name', 'class' => 'form-control')); ?>
    </div>

</div>

<script type="text/javascript">
    (function() {
        var definition_values = <?php echo json_encode($definition_values, JSON_FORCE_OBJECT); ?>;

        $('input[name*="definition"]').autocomplete({
                source: '<?php echo site_url("attributes/suggest_attribute/");?>' + $(this).data('definition-id'),
                appendTo: '.modal-content',
                select: function (a, ui) {
                    $(this).data('attribute_id', ui.item.value);
                    $(this).val(ui.item.label);
                },
                delay:10
        }).change(function() {
            var definition_id = $(this).data('definition-id');
            definition_values[definition_id] = $(this).data('attribute-id');
        });

        $("#definition_name").change(function() {
            var definition_id = $(this).val();
            definition_values[definition_id] = {};
            $("#attributes").load('<?php echo site_url("items/attributes/$item_id");?>', {
                'definition_ids': JSON.stringify(definition_values)
            });
        });

        $('#item_form').validate({
            submitHandler: function (form, event) {
                $(form).ajaxSubmit({
                    beforeSerialize: function ($form, options) {
                        $("<input>").attr({
                            id: 'definition_values',
                            type: 'hidden',
                            name: 'definition_values',
                            value: JSON.stringify(definition_values || [])
                        }).appendTo($form);
                    }
                })
            }
        });


    })();
</script>


