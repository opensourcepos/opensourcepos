<?php
/**
 * @var string $definition_id
 * @var object $definition_info
 * @var array $definition_group
 * @var array $definition_flags
 * @var array $selected_definition_flags
 * @var string $controller_name
 * @var array $definition_values
 */
?>

<?= form_open("attributes/saveDefinition/$definition_id", ['id' => 'attribute_form']) ?>

    <ul id="error_message_box" class="alert alert-warning d-none"></ul>

    <label for="definition_name" class="form-label"><?= lang('Attributes.definition_name'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="definition_name-icon"><i class="bi bi-star"></i></span>
        <input type="text" class="form-control" name="definition_name" id="definition_name" aria-describedby="definition_name-icon" value="<?= esc($definition_info->definition_name); ?>">
    </div>

    <label for="definition_type" class="form-label"><?= lang('Attributes.definition_type'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="definition_type-icon"><i class="bi bi-list"></i></span>
        <select class="form-select" name="definition_type" id="definition_type" aria-describedby="definition_type-icon">
            <?php foreach (DEFINITION_TYPES as $key => $label): ?>
                <option value="<?= $key ?>" <?= ($key === array_search($definition_info->definition_type, DEFINITION_TYPES)) ? 'selected' : '' ?>>
                    <?= $label ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <label for="definition_group" class="form-label"><?= lang('Attributes.definition_group'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="definition_group-icon"><i class="bi bi-collection"></i></span>
        <select class="form-select" name="definition_group" id="definition_group" aria-describedby="definition_group-icon" <?= empty($definition_group) ? 'disabled' : '' ?>>
            <?php foreach ($definition_group as $key => $label): ?>
                <option value="<?= $key ?>" <?= ($key == $definition_info->definition_fk) ? 'selected' : '' ?>>
                    <?= $label ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="toggle-hide d-none">
        <label for="definition_flags" class="form-label"><?= lang('Attributes.definition_flags'); ?></label>
        <div class="input-group mb-3">
            <span class="input-group-text" id="definition_flags-icon"><i class="bi bi-eyeglasses"></i></span>
            <select class="form-select" name="definition_flags[]" id="definition_flags" aria-describedby="definition_flags-icon" multiple>
                <?php foreach ($definition_flags as $key => $label): ?>
                    <option value="<?= $key ?>" <?= in_array($key, array_keys($selected_definition_flags)) ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="toggle-hide d-none">
        <label for="definition_unit" class="form-label"><?= lang('Attributes.definition_unit'); ?></label>
        <div class="input-group mb-3">
            <span class="input-group-text" id="definition_unit-icon"><i class="bi bi-flask"></i></span>
            <input type="text" class="form-control" name="definition_unit" id="definition_unit" aria-describedby="definition_unit-icon" value="<?= esc($definition_info->definition_unit); ?>">
        </div>
    </div>

    <div class="toggle-hide d-none">
        <label for="definition_value" class="form-label"><?= lang('Attributes.definition_values'); ?></label>
        <div class="input-group mb-3">
            <span class="input-group-text" id="definition_value-icon"><i class="bi bi-list"></i></span>
            <input type="text" class="form-control" name="definition_value" id="definition_value" aria-describedby="definition_value-icon">
            <button type="button" class="btn btn-outline-secondary" id="add_attribute_value"><i class="bi bi-plus-circle"></i></button>
        </div>
    </div>

    <div class="toggle-hide d-none">
        <ul class="list-group" id="definition_list_group"></ul>
    </div>

<?= form_close() ?>

<script type="text/javascript">
    // Validation and submit handling
    $(document).ready(function() {
        var values = [];
        var definition_id = <?= esc($definition_id, 'js') ?>;
        var is_new = definition_id == 0;

        var disable_definition_types = function() {
            var definition_type = $("#definition_type option:selected").text();

            if (definition_type == "DATE" || (definition_type == "GROUP" && !is_new) || definition_type == "DECIMAL") {
                $('#definition_type').prop("disabled", true);
            } else if (definition_type == "DROPDOWN" || definition_type == "CHECKBOX") {
                $("#definition_type option:contains('GROUP')").hide();
                $("#definition_type option:contains('DATE')").hide();
                $("#definition_type option:contains('DECIMAL')").hide();
            } else {
                $("#definition_type option:contains('GROUP')").hide();
            }
        }
        disable_definition_types();

        var disable_category_dropdown = function() {
            if (definition_id == -1) {
                $('#definition_name').prop("disabled", true);
                $('#definition_type').prop("disabled", true);
                $('#definition_group').parents('.toggle-hide').toggleClass("d-none", true);
                $('#definition_flags').parents('.toggle-hide').toggleClass("d-none", true);
            }
        }
        disable_category_dropdown();

        var show_hide_fields = function(event) {
            var is_dropdown = $('#definition_type').val() !== '1';
            var is_decimal = $('#definition_type').val() !== '2';
            var is_no_group = $('#definition_type').val() !== '0';
            var is_category_dropdown = definition_id == -1;

            $('#definition_value, #definition_list_group').parents('.toggle-hide').toggleClass('d-none', is_dropdown);
            $('#definition_unit').parents('.toggle-hide').toggleClass('d-none', is_decimal);

            // Appropriately show definition flags if not category_dropdown
            if (definition_id != -1) {
                $('#definition_flags').parents('.toggle-hide').toggleClass('d-none', !is_no_group);
            }
        };

        $('#definition_type').change(show_hide_fields);
        show_hide_fields();

        new TomSelect('#definition_flags', {
            plugins: ['checkbox_options', 'remove_button'],
            placeholder: '<?= lang('Common.none_selected_text') ?>',
            hidePlaceholder: true,
            closeAfterSelect: false,
        });

        var remove_attribute_value = function() {
            var value = $(this).parents("li").text();

            if (is_new) {
                values.splice($.inArray(value, values), 1);
            } else {
                $.post('<?= esc("$controller_name/DeleteDropdownAttributeValue/") ?>', {
                    definition_id: definition_id,
                    attribute_value: value
                });
            }
            $(this).parents("li").remove();
        };

        var add_attribute_value = function(value) {
            var is_event = typeof(value) !== 'string';

            if ($("#definition_value").val().match(/(\||_)/g) != null) {
                return;
            }

            if (is_event) {
                value = $('#definition_value').val();

                if (!value) {
                    return;
                }

                if (is_new) {
                    values.push(value);
                } else {
                    $.post('<?= "attributes/saveAttributeValue/" ?>', {
                        definition_id: definition_id,
                        attribute_value: value
                    });
                }
            }

            $('#definition_list_group').append('<li class="list-group-item list-group-item-action d-flex justify-content-between">' + DOMPurify.sanitize(value) + '<a href="javascript:void(0);"><span class="text-danger"><i class="bi bi-trash"></i></span></a></li>')
                .find(':last-child a').click(remove_attribute_value);
            $('#definition_value').val('');
        };

        $('#add_attribute_value').click(add_attribute_value);

        $('#definition_value').keypress(function(e) {
            if (e.which == 13) {
                add_attribute_value();
                return false;
            }
        });

        var definition_values = <?= json_encode(array_values($definition_values)) ?>;
        $.each(definition_values, function(index, element) {
            add_attribute_value(element);
        });

        $.validator.addMethod('valid_chars', function(value, element) {
            return value.match(/(\||_)/g) == null;
        }, "<?= lang('Attributes.attribute_value_invalid_chars') ?>");

        $('form').bind('submit', function() {
            $(this).find(':input').prop('disabled', false);
        });

        $('#attribute_form').validate($.extend({
            submitHandler: function(form) {
                $(form).ajaxSubmit({
                    beforeSerialize: function($form, options) {
                        is_new && $('<input>').attr({
                            id: 'definition_values',
                            type: 'hidden',
                            name: 'definition_values',
                            value: JSON.stringify(values)
                        }).appendTo($form);
                    },
                    success: function(response) {
                        dialog_support.hide();
                        table_support.handle_submit('<?= esc($controller_name) ?>', response);
                    },
                    dataType: 'json'
                });
            },
            rules: {
                definition_name: 'required',
                definition_value: 'valid_chars',
                definition_type: 'required'
            },
            messages: {
                definition_name: "<?= lang('Attributes.definition_name_required') ?>",
                definition_type: "<?= lang('Attributes.definition_type_required') ?>"
            }
        }, form_support.error));
    });
</script>
