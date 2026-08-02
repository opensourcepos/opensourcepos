<?php
/**
 * @var array $suppliers
 * @var array $allow_alt_description_choices
 * @var array $serialization_choices
 * @var string $controller_name
 * @var array $config
 */
?>

<?= form_open('items/bulkUpdate/', ['id' => 'item_form']) ?>

    <div class="mb-3"><?= lang('Items.edit_fields_you_want_to_update') ?></div>

    <ul id="error_message_box" class="alert alert-warning d-none"></ul>

    <label for="name" class="form-label"><?= lang('Items.name') ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="name-icon"><i class="bi bi-tag"></i></span>
        <input type="text" class="form-control" name="name" id="name" aria-describedby="name-icon">
    </div>

    <label for="category" class="form-label"><?= lang('Items.category') ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="category-icon"><i class="bi bi-bookmark"></i></span>
        <input type="text" class="form-control" name="category" id="category" aria-describedby="category-icon">
    </div>

    <label for="supplier" class="form-label"><?= lang('Items.supplier'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text"><i class="bi bi-truck"></i></span>
        <select class="form-select" name="supplier_id" id="supplier">
            <?php foreach ($suppliers as $value => $label): ?>
                <option value="<?= $value ?>"><?= $label ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <label for="cost_price" class="form-label"><?= lang('Items.cost_price') ?></label>
    <div class="input-group mb-3">
        <?php if (!is_right_side_currency_symbol()): ?>
            <span class="input-group-text" id="cost_price-icon"><?= esc($config['currency_symbol']) ?></span>
        <?php endif; ?>
        <input type="number" step="any" class="form-control" name="cost_price" id="cost_price" aria-describedby="cost_price-icon">
        <?php if (is_right_side_currency_symbol()): ?>
            <span class="input-group-text" id="cost_price-icon"><?= esc($config['currency_symbol']) ?></span>
        <?php endif; ?>
    </div>

    <label for="unit_price" class="form-label"><?= lang('Items.unit_price') ?></label>
    <div class="input-group mb-3">
        <?php if (!is_right_side_currency_symbol()): ?>
            <span class="input-group-text" id="unit_price-icon"><?= esc($config['currency_symbol']) ?></span>
        <?php endif; ?>
        <input type="number" step="any" class="form-control" name="unit_price" id="unit_price" aria-describedby="unit_price-icon">
        <?php if (is_right_side_currency_symbol()): ?>
            <span class="input-group-text" id="unit_price-icon"><?= esc($config['currency_symbol']) ?></span>
        <?php endif; ?>
    </div>

    <label for="tax_name_1" class="form-label"><?= lang('Items.tax_1') ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="tax_name_1-icon"><i class="bi bi-piggy-bank"></i></span>
        <input type="text" class="form-control w-25" name="tax_names[]" id="tax_name_1" aria-describedby="tax_name_1-icon" value="<?= $config['default_tax_1_name'] ?>">
        <input type="number" step="any" min="0" max="100" class="form-control" name="tax_percents[]" id="tax_percent_name_1" aria-describedby="tax_percent_name_1-icon" value="<?= to_tax_decimals($config['default_tax_1_rate']) ?>">
        <span class="input-group-text" id="tax_percent_name_1-icon"><i class="bi bi-percent"></i></span>
    </div>

    <label for="tax_name_2" class="form-label"><?= lang('Items.tax_2') ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="tax_name_2-icon"><i class="bi bi-piggy-bank"></i></span>
        <input type="text" class="form-control w-25" name="tax_names[]" id="tax_name_2" aria-describedby="tax_name_2-icon" value="<?= $config['default_tax_2_name'] ?>">
        <input type="number" step="any" min="0" max="100" class="form-control" name="tax_percents[]" id="tax_percent_name_2" aria-describedby="tax_percent_name_2-icon" value="<?= to_tax_decimals($config['default_tax_2_rate']) ?>">
        <span class="input-group-text" id="tax_percent_name_2-icon"><i class="bi bi-percent"></i></span>
    </div>

    <label for="reorder_level" class="form-label"><?= lang('Items.reorder_level') ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="reorder_level-icon"><i class="bi bi-list-ol"></i></span>
        <input type="text" class="form-control" name="reorder_level" id="reorder_level" aria-describedby="reorder_level-icon">
    </div>

    <label for="description" class="form-label"><?= lang('Items.description'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text"><i class="bi bi-chat"></i></span>
        <textarea class="form-control" name="description" id="description" rows="6"></textarea>
    </div>

    <label for="allow_alt_description" class="form-label"><?= lang('Items.allow_alt_description'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text"><i class="bi bi-input-cursor-text"></i></span>
        <select class="form-select" name="allow_alt_description" id="allow_alt_description">
            <?php foreach ($allow_alt_description_choices as $value => $label): ?>
                <option value="<?= $value ?>"><?= $label ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <label for="is_serialized" class="form-label"><?= lang('Items.is_serialized'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text"><i class="bi bi-123"></i></span>
        <select class="form-select" name="is_serialized" id="is_serialized">
            <?php foreach ($serialization_choices as $value => $label): ?>
                <option value="<?= $value ?>"><?= $label ?></option>
            <?php endforeach; ?>
        </select>
    </div>

<?= form_close() ?>

<script type="text/javascript">
    // Validation and submit handling
    $(document).ready(function() {
        $('#category').autocomplete({
            source: "<?= 'items/suggestCategory' ?>",
            appendTo: '.modal-content',
            delay: 10
        });

        var confirm_message = false;
        $('#tax_percent_name_2, #tax_name_2').prop('disabled', true),
            $('#tax_percent_name_1, #tax_name_1').blur(function() {
                var disabled = !($('#tax_percent_name_1').val() + $('#tax_name_1').val());
                $('#tax_percent_name_2, #tax_name_2').prop('disabled', disabled);
                confirm_message = disabled ? '' : "<?= lang('Items.confirm_bulk_edit_wipe_taxes') ?>";
            });

        $('#item_form').validate($.extend({
            submitHandler: function(form) {
                if (!confirm_message || confirm(confirm_message)) {
                    $(form).ajaxSubmit({
                        beforeSubmit: function(arr, $form, options) {
                            arr.push({
                                name: 'item_ids',
                                value: table_support.selected_ids().join(":")
                            });
                        },
                        success: function(response) {
                            dialog_support.hide();
                            table_support.handle_submit("<?= esc($controller_name) ?>", response);
                        },
                        dataType: 'json'
                    });
                }
            },

            errorLabelContainer: '#error_message_box',

            rules: {
                unit_price: {
                    number: true
                },
                tax_percent: {
                    number: true
                },
                quantity: {
                    number: true
                },
                reorder_level: {
                    number: true
                }
            },

            messages: {
                unit_price: {
                    number: "<?= lang('Items.unit_price_number') ?>"
                },
                tax_percent: {
                    number: "<?= lang('Items.tax_percent_number') ?>"
                },
                quantity: {
                    number: "<?= lang('Items.quantity_number') ?>"
                },
                reorder_level: {
                    number: "<?= lang('Items.reorder_level_number') ?>"
                }
            }
        }, form_support.error));
    });
</script>
