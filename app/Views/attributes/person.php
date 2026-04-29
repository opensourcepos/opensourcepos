<?php
/**
 * @var array $definition_names
 * @var array $definition_values
 * @var int $person_id
 * @var array $config
 */

use App\Models\Attribute;
?>

<div class="form-group form-group-sm">
    <?= form_label(lang('Attributes.definition_name'), 'definition_name_label', ['class' => 'control-label col-xs-3']) ?>
    <div class="col-xs-8">
        <?= form_dropdown([
            'name'     => 'definition_name',
            'options'  => $definition_names,
            'selected' => -1,
            'class'    => 'form-control',
            'id'       => 'definition_name'
        ]) ?>
    </div>
</div>

<?php foreach ($definition_values as $definitionId => $definitionValue) { ?>

    <div class="form-group form-group-sm">
        <?= form_label(esc($definitionValue['definition_name']), esc($definitionValue['definition_name']), ['class' => 'control-label col-xs-3']) ?>
        <div class="col-xs-8">
            <div class="input-group">
                <?php
                echo form_hidden("attribute_ids[$definitionId]", strval($definitionValue['attribute_id']));
                $attributeValue = $definitionValue['attribute_value'];

                switch ($definitionValue['definition_type']) {
                    case DATE:
                        $value = (empty($attributeValue) || empty($attributeValue->attribute_date)) ? NOW : strtotime($attributeValue->attribute_date);
                        echo form_input([
                            'name'               => "attribute_links[$definitionId]",
                            'value'              => to_date($value),
                            'class'              => 'form-control input-sm datetime',
                            'data-definition-id' => $definitionId,
                            'readonly'           => 'true'
                        ]);
                        break;
                    case DROPDOWN:
                        $selectedValue = $definitionValue['selected_value'];
                        echo form_dropdown([
                            'name'               => "attribute_links[$definitionId]",
                            'options'            => $definitionValue['values'],
                            'selected'           => $selectedValue,
                            'class'              => 'form-control',
                            'data-definition-id' => $definitionId
                        ]);
                        break;
                    case TEXT:
                        $value = (empty($attributeValue) || empty($attributeValue->attribute_value)) ? $definitionValue['selected_value'] : $attributeValue->attribute_value;
                        echo form_input([
                            'name'               => "attribute_links[$definitionId]",
                            'value'              => esc($value),
                            'class'              => 'form-control valid_chars',
                            'data-definition-id' => $definitionId
                        ]);
                        break;
                    case DECIMAL:
                        $value = (empty($attributeValue) || empty($attributeValue->attribute_decimal)) ? $definitionValue['selected_value'] : $attributeValue->attribute_decimal;
                        echo form_input([
                            'name'               => "attribute_links[$definitionId]",
                            'value'              => to_decimals((float)$value),
                            'class'              => 'form-control valid_chars',
                            'data-definition-id' => $definitionId
                        ]);
                        break;
                    case CHECKBOX:
                        $value = (empty($attributeValue) || empty($attributeValue->attribute_value)) ? $definitionValue['selected_value'] : $attributeValue->attribute_value;

                        // Sends 0 if the box is unchecked instead of not sending anything.
                        echo form_input([
                            'type'               => 'hidden',
                            'name'               => "attribute_links[$definitionId]",
                            'id'                 => "attribute_links[$definitionId]",
                            'value'              => 0,
                            'data-definition-id' => $definitionId
                        ]);
                        echo form_checkbox([
                            'name'               => "attribute_links[$definitionId]",
                            'id'                 => "attribute_links[$definitionId]",
                            'value'              => 1,
                            'checked'            => $value == 1,
                            'class'              => 'checkbox-inline',
                            'data-definition-id' => $definitionId
                        ]);
                        break;
                }
                ?>
                <span class="input-group-addon input-sm btn btn-default remove_attribute_btn">
                    <span class="glyphicon glyphicon-trash"></span>
                </span>
            </div>
        </div>
    </div>

<?php } ?>

<script type="text/javascript">
    (function() {
        <?= view('partial/datepicker_locale', ['format' => dateformat_bootstrap($config['dateformat'])]) ?>

        var enableDelete = function() {
            $('.remove_attribute_btn').click(function() {
                $(this).parents('.form-group').remove();
            });
        };

        enableDelete();

        $("input[name*='attribute_links']").change(function() {
            var definitionId = $(this).data('definition-id');
            $("input[name='attribute_ids[" + definitionId + "]']").val('');
        }).autocomplete({
            source: function(request, response) {
                $.get('<?= 'attributes/suggestAttribute/' ?>' + this.element.data('definition-id') + '?term=' + request.term, function(data) {
                    return response(data);
                }, 'json');
            },
            appendTo: '.modal-content',
            select: function(event, ui) {
                event.preventDefault();
                $(this).val(ui.item.label);
            },
            delay: 10
        });

        var getDefinitionValues = function() {
            var result = {};
            $("[name*='attribute_links']").each(function() {
                var definitionId = $(this).data('definition-id');
                var element = $(this);

                // For checkboxes, use the visible checkbox, not the hidden input
                if (element.attr('type') === 'hidden' && element.siblings('input[type="checkbox"]').length > 0) {
                    // Skip hidden inputs that have a corresponding checkbox
                    return;
                }

                // For checkboxes, get the checked state
                if (element.attr('type') === 'checkbox') {
                    result[definitionId] = element.prop('checked') ? '1' : '0';
                } else {
                    result[definitionId] = element.val();
                }
            });
            return result;
        };

        var refresh = function() {
            var definitionId = $("#definition_name option:selected").val();
            var attributeValues = getDefinitionValues();
            attributeValues[definitionId] = '';
            $('#person_attributes').load(window.location.href, {
                'definition_ids': JSON.stringify(attributeValues)
            }, enableDelete);
        };

        $('#definition_name').change(function() {
            refresh();
        });
    })();
</script>