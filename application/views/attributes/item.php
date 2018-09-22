
<div class="form-group form-group-sm">
	<?php echo form_label($this->lang->line("attributes_definition_name"), "definition_name_label", array('class' => 'control-label col-xs-3')); ?>
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

            echo form_hidden("attribute_ids[$definition_id]", $definition_value['attribute_id']);
            $attribute_value = $definition_value['attribute_value'];
            if ($definition_value['definition_type'] == DATETIME)
            {
	            $value = (empty($attribute_value) || empty($attribute_value->attribute_datetime)) ? DEFAULT_DATETIME : strtotime($attribute_value->attribute_datetime);
	            echo form_input(array(
                    'name' => "attribute_links[$definition_id]",
                    'value' => to_datetime($value),
                    'class' => 'form-control input-sm datetime',
                    'data-definition-id' => $definition_id,
                    'readonly' => 'true'));
            }
            else if ($definition_value['definition_type'] == DROPDOWN)
            {
                $selected_value = empty($selected_value) ? $definition_value['selected_value'] : $selected_value->attribute_id;
                echo form_dropdown("attribute_links[$definition_id]", $definition_value['values'], $selected_value, "class='form-control' data-definition-id='$definition_id'");
            }
            else if ($definition_value['definition_type'] == TEXT)
            {
                $value = (empty($attribute_value) || empty($attribute_value->attribute_value)) ? $definition_value['selected_value'] : $attribute_value->attribute_value;
                echo form_input("attribute_links[$definition_id]", $value, "class='form-control' data-definition-id='$definition_id'");
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
        <?php $this->load->view('partial/datepicker_locale'); ?>

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
                $.get('<?php echo site_url('attributes/suggest_attribute/');?>' + this.element.data('definition-id') + '?term=' + request.term, function(data) {
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
            $("input[name*='attribute_links'").each(function(index, element) {
                var definition_id = $(this).data('definition-id');
                result[definition_id] = $(element).val();
            });
            return result;
        }

        $('#definition_name').change(function() {
            var attribute_values = definition_values();
            var definition_id = $("option:selected", this).val();
            attribute_values[definition_id] = '';
            $('#attributes').load('<?php echo site_url("items/attributes/$item_id");?>', {
                'definition_ids': JSON.stringify(attribute_values)
            }, enable_delete);
        });
    })();
</script>
