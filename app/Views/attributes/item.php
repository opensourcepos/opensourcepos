<?php
/**
 * @var array $definition_names
 * @var array $definition_values
 * @var int $item_id
 * @var array $config
 */
?>

<label for="definition_name" class="form-label"><?= lang('Attributes.definition_name'); ?></label>
<div class="input-group mb-3">
    <span class="input-group-text"><i class="bi bi-star"></i></span>
    <select class="form-select" name="definition_name" id="definition_name">
        <option value="-1" selected></option>
        <?php foreach ($definition_names as $key => $value): ?>
            <option value="<?= $key ?>"><?= $value ?></option>
        <?php endforeach; ?>
    </select>
</div>

<?php foreach ($definition_values as $definition_id => $definition_value) { ?>

<span class="attribute_added">
    <?php
    $attribute_value = $definition_value['attribute_value'];

    switch ($definition_value['definition_type']) {

        case DATE:
            $value = (empty($attribute_value) || empty($attribute_value->attribute_date)) ? NOW : strtotime($attribute_value->attribute_date);
            ?>
            <label for="attribute_links[<?= $definition_id ?>]" class="form-label"><?= esc($definition_value['definition_name']) ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="attribute_links[<?= $definition_id ?>]-icon"><i class="bi bi-calendar2"></i></span>
                <input type="hidden" name="attribute_ids[<?= $definition_id ?>]" value="<?= strval($definition_value['attribute_id']) ?>">
                <input type="text" class="form-select datetime" name="attribute_links[<?= $definition_id ?>]" id="attribute_links[<?= $definition_id ?>]" aria-describedby="attribute_links[<?= $definition_id ?>]-icon" value="<?= to_date($value) ?>"  data-definition-id="<?= $definition_id ?>" readonly>
                <button type="button" class="btn btn-outline-danger remove_attribute_btn"><i class="bi bi-trash"></i></button>
            </div>
            <?php
            break;

        case DROPDOWN:
            $selected_value = $definition_value['selected_value'];
            ?>
            <label for="attribute_links[<?= $definition_id ?>]" class="form-label"><?= esc($definition_value['definition_name']) ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="attribute_links[<?= $definition_id ?>]-icon"><i class="bi bi-menu-down"></i></span>
                <input type="hidden" name="attribute_ids[<?= $definition_id ?>]" value="<?= strval($definition_value['attribute_id']) ?>">
                <select class="form-select" name="attribute_links[<?= $definition_id ?>]" id="attribute_links[<?= $definition_id ?>]" data-definition-id="<?= $definition_id ?>">
                    <?php foreach ($definition_value['values'] as $key => $val): ?>
                        <option value="<?= $key ?>" <?= $selected_value == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="button" class="btn btn-outline-danger remove_attribute_btn"><i class="bi bi-trash"></i></button>
            </div>
            <?php
            break;

        case TEXT:
            $value = (empty($attribute_value) || empty($attribute_value->attribute_value)) ? $definition_value['selected_value'] : $attribute_value->attribute_value;
            ?>
            <label for="attribute_links[<?= $definition_id ?>]" class="form-label"><?= esc($definition_value['definition_name']) ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="attribute_links[<?= $definition_id ?>]-icon"><i class="bi bi-type"></i></span>
                <input type="hidden" name="attribute_ids[<?= $definition_id ?>]" value="<?= strval($definition_value['attribute_id']) ?>">
                <input type="text" name="attribute_links[<?= $definition_id ?>]" id="attribute_links[<?= $definition_id ?>]" value="<?= esc($value) ?>" class="form-control valid_chars" data-definition-id="<?= $definition_id ?>">
                <button type="button" class="btn btn-outline-danger remove_attribute_btn"><i class="bi bi-trash"></i></button>
            </div>
            <?php
            break;

        case DECIMAL:
            $value = (empty($attribute_value) || empty($attribute_value->attribute_decimal)) ? $definition_value['selected_value'] : $attribute_value->attribute_decimal;
            ?>
            <label for="attribute_links[<?= $definition_id ?>]" class="form-label"><?= esc($definition_value['definition_name']) ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="attribute_links[<?= $definition_id ?>]-icon"><i class="bi bi-dot"></i></span>
                <input type="hidden" name="attribute_ids[<?= $definition_id ?>]" value="<?= strval($definition_value['attribute_id']) ?>">
                <input type="text" name="attribute_links[<?= $definition_id ?>]" id="attribute_links[<?= $definition_id ?>]" value="<?= to_decimals((float)$value) ?>" class="form-control valid_chars" data-definition-id="<?= $definition_id ?>">
                <button type="button" class="btn btn-outline-danger remove_attribute_btn"><i class="bi bi-trash"></i></button>
            </div>
            <?php
            break;

        case CHECKBOX:
            $value = (empty($attribute_value) || empty($attribute_value->attribute_value)) ? $definition_value['selected_value'] : $attribute_value->attribute_value;
            ?>
            <div class="d-flex justify-content-between">
                <div class="form-check form-check-inline mb-3">
                    <input type="hidden" name="attribute_ids[<?= $definition_id ?>]" value="<?= strval($definition_value['attribute_id']) ?>">
                    <input type="hidden" name="attribute_links_h1[<?= $definition_id ?>]" id="attribute_links_h1<?= $definition_id ?>" value="0" data-definition-id="<?= $definition_id ?>">
                    <input type="checkbox" class="form-check-input" name="attribute_links[<?= $definition_id ?>]" id="attribute_links[<?= $definition_id ?>]" value="1" <?= $value == 1 ? 'checked' : '' ?> data-definition-id="<?= $definition_id ?>">
                    <label class="form-check-label" for="attribute_links[<?= $definition_id ?>]"><?= esc($definition_value['definition_name']) ?></label>
                </div>
                <button type="button" class="btn btn-outline-danger remove_attribute_btn"><i class="bi bi-trash"></i></button>
            </div>
            <?php
            break;
    }
    ?>
</span>

<?php } ?>

<script type="text/javascript">
    (function() {
        <?= view('partial/datepicker_locale', ['format' => dateformat_bootstrap($config['dateformat'])]) ?>

        var enable_delete = function() {
            $('.remove_attribute_btn').click(function() {
                $(this).parents('.attribute_added').remove();
            });
        };

        enable_delete();

        $("input[name*='attribute_links']").change(function() {
            var definition_id = $(this).data('definition-id');
            $("input[name='attribute_ids[" + definition_id + "]']").val('');
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

        var definition_values = function() {
            var result = {};
            $("[name*='attribute_links'").each(function() {
                var definition_id = $(this).data('definition-id');
                var element = $(this);

                // For checkboxes, use the visible checkbox, not the hidden input
                if (element.attr('type') === 'hidden' && element.siblings('input[type="checkbox"]').length > 0) {
                    // Skip hidden inputs that have a corresponding checkbox
                    return;
                }

                // For checkboxes, get the checked state
                if (element.attr('type') === 'checkbox') {
                    result[definition_id] = element.prop('checked') ? '1' : '0';
                } else {
                    result[definition_id] = element.val();
                }
            });
            return result;
        };

        var refresh = function() {
            var definition_id = $("#definition_name option:selected").val();
            var attribute_values = definition_values();
            attribute_values[definition_id] = '';
            $('#attributes').load('<?= "items/attributes/$item_id" ?>', {
                'definition_ids': JSON.stringify(attribute_values)
            }, enable_delete);
        };

        $('#definition_name').change(function() {
            refresh();
        });
    })();
</script>
