<?php
/**
 * @var object $item_info
 * @var array $categories
 * @var int $selected_category
 * @var bool $standard_item_locked
 * @var bool $item_kit_disabled
 * @var int $allow_temp_item
 * @var array $suppliers
 * @var int $selected_supplier
 * @var bool $use_destination_based_tax
 * @var float $default_tax_1_rate
 * @var float $default_tax_2_rate
 * @var string $tax_category
 * @var int $tax_category_id
 * @var bool $include_hsn
 * @var string $hsn_code
 * @var array $stock_locations
 * @var bool $logo_exists
 * @var string $image_path
 * @var string $selected_low_sell_item
 * @var int $selected_low_sell_item_id
 * @var string $controller_name
 * @var array $config
 */
?>

<?= form_open("items/save/$item_info->item_id", ['id' => 'item_form', 'enctype' => 'multipart/form-data']) ?>

    <ul id="error_message_box" class="alert alert-warning d-none"></ul>

    <label for="item_number" class="form-label"><?= lang('Items.item_number'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="item_number-icon"><i class="bi bi-upc-scan"></i></span>
        <input type="text" class="form-control" name="item_number" id="item_number" aria-describedby="item_number-icon" value="<?= $item_info->item_number ?>">
    </div>

    <label for="name" class="form-label"><?= lang('Items.name'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="name-icon"><i class="bi bi-tag"></i></span>
        <input type="text" class="form-control" name="name" id="name" aria-describedby="name-icon" value="<?= $item_info->name ?>">
    </div>

    <label for="category" class="form-label"><?= lang('Items.category'); ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="category-icon"><i class="bi bi-bookmark"></i></span>
        <?php if ($config['category_dropdown']) { ?>
            <select class="form-select" name="category" id="category" required>
                <?php foreach ($categories as $key => $value) { ?>
                    <option value="<?= $key ?>" <?= $selected_category == $key ? 'selected' : '' ?>><?= $value ?></option>
                <?php } ?>
            </select>
        <?php } else { ?>
            <input type="text" class="form-control" name="category" id="category" aria-describedby="category-icon" value="<?= $item_info->category ?>" required>
        <?php } ?>
    </div>

    <div id="attributes">
        <script type="text/javascript">
            $('#attributes').load('<?= "items/attributes/$item_info->item_id" ?>');
        </script>
    </div>

    <label for="stock_type" class="form-label"><?= lang('Items.stock_type') ?><?= !empty($basic_version) ? '<sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup>' : '' ?></label>
    <div class="mb-3">
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="stock_type" id="stock_type_stock" value="0" <?= $item_info->stock_type == HAS_STOCK ? 'checked' : '' ?> <?= !empty($basic_version) ? 'required' : '' ?>>
            <label class="form-check-label" for="stock_type_stock"><?= lang('Items.stock') ?></label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="stock_type" id="stock_type_nonstock" value="1" <?= $item_info->stock_type == HAS_NO_STOCK ? 'checked' : '' ?> <?= !empty($basic_version) ? 'required' : '' ?>>
            <label class="form-check-label" for="stock_type_nonstock"><?= lang('Items.nonstock') ?></label>
        </div>
    </div>

    <label for="item_type" class="form-label"><?= lang('Items.type') ?><?= !empty($basic_version) ? '<sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup>' : '' ?></label>
    <div class="mb-3">
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="item_type" id="item_type_standard" value="0" <?= $item_info->item_type == ITEM ? 'checked' : '' ?> <?= $standard_item_locked ? 'disabled readonly' : '' ?> <?= !empty($basic_version) ? 'required' : '' ?>>
            <label class="form-check-label" for="item_type_standard"><?= lang('Items.standard') ?></label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="item_type" id="item_type_kit" value="1" <?= $item_info->item_type == ITEM_KIT ? 'checked' : '' ?> <?= $item_kit_disabled ? 'disabled readonly' : '' ?> <?= !empty($basic_version) ? 'required' : '' ?>>
            <label class="form-check-label" for="item_type_kit"><?= lang('Items.kit') ?></label>
        </div>
        <?php if ($config['derive_sale_quantity'] == '1') { ?>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="item_type" id="item_type_amount_entry" value="2" <?= $item_info->item_type == ITEM_AMOUNT_ENTRY ? 'checked' : '' ?> <?= !empty($basic_version) ? 'required' : '' ?>>
                <label class="form-check-label" for="item_type_amount_entry"><?= lang('Items.amount_entry') ?></label>
            </div>
        <?php } ?>
        <?php if ($allow_temp_item == 1) { ?>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="item_type" id="item_type_temp" value="3" <?= $item_info->item_type == ITEM_TEMP ? 'checked' : '' ?> <?= !empty($basic_version) ? 'required' : '' ?>>
                <label class="form-check-label" for="item_type_temp"><?= lang('Items.temp') ?></label>
            </div>
        <?php } ?>
    </div>

    <label for="supplier_id" class="form-label"><?= lang('Items.supplier'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text"><i class="bi bi-truck"></i></span>
        <select class="form-select" name="supplier_id" id="supplier_id">
            <?php foreach ($suppliers as $key => $value) { ?>
                <option value="<?= $key ?>" <?= $selected_supplier == $key ? 'selected' : '' ?>><?= $value ?></option>
            <?php } ?>
        </select>
    </div>

    <label for="cost_price" class="form-label"><?= lang('Items.cost_price') ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
    <div class="input-group mb-3">
        <?php if (!is_right_side_currency_symbol()): ?>
            <span class="input-group-text" id="cost_price-icon"><?= esc($config['currency_symbol']) ?></span>
        <?php endif; ?>
        <input type="number" step="any" class="form-control" name="cost_price" id="cost_price" aria-describedby="cost_price-icon" value="<?= to_currency_no_money($item_info->cost_price) ?>" required>
        <?php if (is_right_side_currency_symbol()): ?>
            <span class="input-group-text" id="cost_price-icon"><?= esc($config['currency_symbol']) ?></span>
        <?php endif; ?>
    </div>

    <label for="unit_price" class="form-label"><?= lang('Items.unit_price') ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
    <div class="input-group mb-3">
        <?php if (!is_right_side_currency_symbol()): ?>
            <span class="input-group-text" id="unit_price-icon"><?= esc($config['currency_symbol']) ?></span>
        <?php endif; ?>
        <input type="number" step="any" class="form-control" name="unit_price" id="unit_price" aria-describedby="unit_price-icon" value="<?= to_currency_no_money($item_info->unit_price) ?>" required>
        <?php if (is_right_side_currency_symbol()): ?>
            <span class="input-group-text" id="unit_price-icon"><?= esc($config['currency_symbol']) ?></span>
        <?php endif; ?>
    </div>

    <?php if (!$use_destination_based_tax): ?>
        <label for="tax_name_1" class="form-label"><?= lang('Items.tax_1') ?></label>
        <div class="input-group mb-3">
            <span class="input-group-text" id="tax_name_1-icon"><i class="bi bi-piggy-bank"></i></span>
            <input type="text" class="form-control w-25" name="tax_names[]" id="tax_name_1" aria-describedby="tax_name_1-icon" value="<?= $item_tax_info[0]['name'] ?? $config['default_tax_1_name'] ?>">
            <input type="number" step="any" min="0" max="100" class="form-control" name="tax_percents[]" id="tax_percent_name_1" aria-describedby="tax_percent_name_1-icon" value="<?= isset($item_tax_info[0]['percent']) ? to_tax_decimals($item_tax_info[0]['percent']) : to_tax_decimals($default_tax_1_rate) ?>">
            <span class="input-group-text" id="tax_percent_name_1-icon"><i class="bi bi-percent"></i></span>
        </div>

        <label for="tax_name_2" class="form-label"><?= lang('Items.tax_2') ?></label>
        <div class="input-group mb-3">
            <span class="input-group-text" id="tax_name_2-icon"><i class="bi bi-piggy-bank"></i></span>
            <input type="text" class="form-control w-25" name="tax_names[]" id="tax_name_2" aria-describedby="tax_name_2-icon" value="<?= $item_tax_info[1]['name'] ?? $config['default_tax_2_name'] ?>">
            <input type="number" step="any" min="0" max="100" class="form-control" name="tax_percents[]" id="tax_percent_name_2" aria-describedby="tax_percent_name_2-icon" value="<?= isset($item_tax_info[1]['percent']) ? to_tax_decimals($item_tax_info[1]['percent']) : to_tax_decimals($default_tax_2_rate) ?>">
            <span class="input-group-text" id="tax_percent_name_2-icon"><i class="bi bi-percent"></i></span>
        </div>
    <?php endif; ?>

    <?php if ($use_destination_based_tax): ?>
        <label for="tax_category" class="form-label"><?= lang('Taxes.tax_category') ?></label>
        <div class="input-group mb-3">
            <span class="input-group-text" id="tax_category-icon"><i class="bi bi-piggy-bank"></i></span>
            <input type="hidden" name="tax_category_id" id="tax_category_id" value="<?= $tax_category_id ?>">
            <input type="text" class="form-control" name="tax_category" id="tax_category" aria-describedby="tax_category-icon" value="<?= $tax_category ?>">
        </div>
    <?php endif; ?>

    <?php if ($include_hsn): ?>
        <label for="hsn_code" class="form-label"><?= lang('Items.hsn_code'); ?></label>
        <div class="input-group mb-3">
            <span class="input-group-text" id="hsn_code-icon"><i class="bi bi-123"></i></span>
            <input type="text" class="form-control" name="hsn_code" id="hsn_code" aria-describedby="hsn_code-icon" value="<?= $hsn_code ?>">
        </div>
    <?php endif; ?>

    <?php foreach ($stock_locations as $key => $location_detail): ?>
        <label for="quantity_<?= $key ?>" class="form-label"><?= lang('Items.quantity') . ' ' . $location_detail['location_name'] ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
        <div class="input-group mb-3">
            <span class="input-group-text" id="quantity_<?= $key ?>-icon"><i class="bi bi-boxes"></i></span>
            <input type="text" class="form-control" name="quantity_<?= $key ?>" id="quantity_<?= $key ?>" aria-describedby="quantity_<?= $key ?>-icon" value="<?= isset($item_info->item_id) ? to_quantity_decimals($location_detail['quantity']) : to_quantity_decimals(0) ?>" required>
        </div>
    <?php endforeach; ?>

    <label for="receiving_quantity" class="form-label"><?= lang('Items.receiving_quantity') ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="receiving_quantity-icon"><i class="bi bi-truck"></i></span>
        <input type="text" class="form-control" name="receiving_quantity" id="receiving_quantity" aria-describedby="receiving_quantity-icon" value="<?= isset($item_info->item_id) ? to_quantity_decimals($item_info->receiving_quantity) : to_quantity_decimals(0) ?>">
    </div>

    <label for="reorder_level" class="form-label"><?= lang('Items.reorder_level') ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="reorder_level-icon"><i class="bi bi-list-ol"></i></span>
        <input type="text" class="form-control" name="reorder_level" id="reorder_level" aria-describedby="reorder_level-icon" value="<?= isset($item_info->item_id) ? to_quantity_decimals($item_info->reorder_level) : to_quantity_decimals(0) ?>">
    </div>

    <label for="description" class="form-label"><?= lang('Items.description'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text"><i class="bi bi-chat"></i></span>
        <textarea class="form-control" name="description" id="description" rows="6"><?= $item_info->description ?></textarea>
    </div>

    <label for="items_image" class="form-label"><?= lang('Items.image'); ?></label>
    <div id="items_image" class="w-100 fileinput <?= $logo_exists ? 'fileinput-exists' : 'fileinput-new'; ?>" data-provides="fileinput">
        <div class="input-group mb-3" aria-describedby="company-logo-desc">
            <span class="input-group-text"><i class="bi bi-image"></i></span>
            <div class="fileinput-new form-control rounded-end mb-0" style="height: 200px; cursor: default;"></div>
            <div class="fileinput-exists fileinput-preview img-thumbnail form-control rounded-end mb-0 bg-light mh-100" style="height: 200px; cursor: default; background-size: 40px 40px; background-position: 0 0, 20px 20px; background-image: linear-gradient(45deg, white 25%, transparent 25%, transparent 75%, white 75%, white), linear-gradient(45deg, white 25%, transparent 25%, transparent 75%, white 75%, white);">
                <img class="mh-100 mw-100" data-src="holder.js/100%x100%" alt="<?= esc(lang('Config.company_logo')) ?>" src="<?= $image_path ?>">
            </div>
        </div>
        <div type="button" class="btn btn-secondary btn-file me-2">
            <span class="fileinput-new"><i class="bi bi-hand-index me-2"></i><?= lang('Items.select_image') ?></span>
            <span class="fileinput-exists"><i class="bi bi-images me-2"></i><?= lang('Items.change_image') ?></span>
            <input type="file" name="items_image" accept="image/*">
        </div>
        <a type="button" class="btn btn-outline-secondary fileinput-exists" data-dismiss="fileinput">
            <i class="bi bi-eraser me-2"></i><?= lang('Items.remove_image') ?>
        </a>
    </div>

    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="allow_alt_description" id="allow_alt_description" value="1" <?= $item_info->allow_alt_description == 1 ? 'checked' : '' ?>>
        <label class="form-check-label" for="allow_alt_description"><?= lang('Items.allow_alt_description') ?></label>
    </div>

    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="is_serialized" id="is_serialized" value="1" <?= $item_info->is_serialized == 1 ? 'checked' : '' ?>>
        <label class="form-check-label" for="is_serialized"><?= lang('Items.is_serialized') ?></label>
    </div>

    <?php if ($config['multi_pack_enabled'] == '1'): ?>
        <label for="qty_per_pack" class="form-label"><?= lang('Items.qty_per_pack') ?></label>
        <div class="input-group mb-3">
            <span class="input-group-text" id="qty_per_pack-icon"><i class="bi bi-123"></i></span>
            <input type="text" class="form-control" name="qty_per_pack" id="qty_per_pack" aria-describedby="qty_per_pack-icon" value="<?= isset($item_info->item_id) ? to_quantity_decimals($item_info->qty_per_pack) : to_quantity_decimals(0) ?>">
        </div>

        <label for="pack_name" class="form-label"><?= lang('Items.pack_name') ?></label>
        <div class="input-group mb-3">
            <span class="input-group-text" id="pack_name-icon"><i class="bi bi-box2-heart"></i></span>
            <input type="text" class="form-control" name="pack_name" id="pack_name" aria-describedby="pack_name-icon" value="<?= $item_info->pack_name ?>">
        </div>

        <label for="low_sell_item_name" class="form-label"><?= lang('Items.low_sell_item') ?></label>
        <div class="input-group mb-3">
            <span class="input-group-text" id="pack_name-icon"><i class="bi bi-thermometer-low"></i></span>
            <input type="hidden" name="low_sell_item_id" value="<?= $selected_low_sell_item_id ?>">
            <input type="text" class="form-control" name="low_sell_item_name" id="low_sell_item_name" aria-describedby="low_sell_item_name-icon" value="<?= $selected_low_sell_item ?>">
        </div>
    <?php endif; ?>

    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="is_deleted" id="is_deleted" value="1" <?= $item_info->deleted == 1 ? 'checked' : '' ?>>
        <label class="form-check-label text-danger" for="is_deleted"><?= lang('Items.is_deleted') ?></label>
    </div>

<?= form_close() ?>

<script type="text/javascript">
    // Validation and submit handling
    $(document).ready(function() {
        $('#new').click(function() {
            let stay_open = true;
            $('#item_form').submit();
        });

        $('#submit').click(function() {
            let stay_open = false;
        });

        $("input[name='tax_category']").change(function() {
            !$(this).val() && $(this).val('');
        });

        var fill_tax_category_value = function(event, ui) {
            event.preventDefault();
            $("input[name='tax_category_id']").val(ui.item.value);
            $("input[name='tax_category']").val(ui.item.label);
        };

        $('#tax_category').autocomplete({
            source: "<?= 'taxes/suggestTaxCategories' ?>",
            minChars: 0,
            delay: 15,
            cacheLength: 1,
            appendTo: '.modal-content',
            select: fill_tax_category_value,
            focus: fill_tax_category_value
        });

        var fill_low_sell_value = function(event, ui) {
            event.preventDefault();
            $("input[name='low_sell_item_id']").val(ui.item.value);
            $("input[name='low_sell_item_name']").val(ui.item.label);
        };

        $('#low_sell_item_name').autocomplete({
            source: "<?= 'items/suggestLowSell' ?>",
            minChars: 0,
            delay: 15,
            cacheLength: 1,
            appendTo: '.modal-content',
            select: fill_low_sell_value,
            focus: fill_low_sell_value
        });

        $('#category').autocomplete({
            source: "<?= 'items/suggestCategory' ?>",
            delay: 10,
            appendTo: '.modal-content'
        });

        $('a.fileinput-exists').click(function() {
            $.ajax({
                type: 'GET',
                url: '<?= "$controller_name/removeLogo/$item_info->item_id" ?>',
                dataType: 'json'
            })
        });

        $.validator.addMethod('valid_chars', function(value, element) {
            return value.match(/(\||_)/g) == null;
        }, "<?= lang('Attributes.attribute_value_invalid_chars') ?>");

        var init_validation = function() {
            $('#item_form').validate($.extend({
                submitHandler: function(form, event) { // Event is not used as a parameter here
                    $(form).ajaxSubmit({
                        success: function(response) {
                            let stay_open = dialog_support.clicked_id() != 'submit';
                            if (stay_open) {
                                // Set action of item_form to url without item id, so a new one can be created
                                $('#item_form').attr('action', "<?= 'items/save/' ?>");
                                // Use a whitelist of fields to minimize unintended side effects
                                $(':text, :password, :file, #description, #item_form').not('.quantity, #reorder_level, #tax_name_1, #receiving_quantity, ' +
                                    '#tax_percent_name_1, #category, #reference_number, #name, #cost_price, #unit_price, #taxed_cost_price, #taxed_unit_price, #definition_name, [name^="attribute_links"]').val('');
                                // De-select any checkboxes, radios and drop-down menus
                                $(':input', '#item_form').removeAttr('checked').removeAttr('selected');
                            } else {
                                dialog_support.hide();
                            }
                            table_support.handle_submit('<?= 'items' ?>', response, stay_open);
                            init_validation();
                        },
                        dataType: 'json'
                    });
                },

                errorLabelContainer: '#error_message_box',

                rules: {
                    name: 'required',
                    category: 'required',
                    item_number: {
                        required: false,
                        remote: {
                            url: "<?= esc("$controller_name/checkItemNumber") ?>",
                            type: 'POST',
                            data: {
                                'item_id': "<?= $item_info->item_id ?>"
                                // item_number should be passed into the function by default
                            }
                        }
                    },
                    cost_price: {
                        required: true,
                        remote: "<?= esc("$controller_name/checkNumeric") ?>"
                    },
                    unit_price: {
                        required: true,
                        remote: "<?= esc("$controller_name/checkNumeric") ?>"
                    },
                    <?php foreach ($stock_locations as $key => $location_detail) { ?>
                        <?= 'quantity_' . $key ?>: {
                            required: true,
                            remote: "<?= esc("$controller_name/checkNumeric") ?>"
                        },
                    <?php } ?>
                    receiving_quantity: {
                        required: true,
                        remote: "<?= esc("$controller_name/checkNumeric") ?>"
                    },
                    reorder_level: {
                        required: true,
                        remote: "<?= esc("$controller_name/checkNumeric") ?>"
                    },
                    tax_percent: {
                        required: false,
                        remote: "<?= esc("$controller_name/checkNumeric") ?>"
                    }
                },

                messages: {
                    name: "<?= lang('Items.name_required') ?>",
                    item_number: "<?= lang('Items.item_number_duplicate') ?>",
                    category: "<?= lang('Items.category_required') ?>",
                    cost_price: {
                        required: "<?= lang('Items.cost_price_required') ?>",
                        number: "<?= lang('Items.cost_price_number') ?>"
                    },
                    unit_price: {
                        required: "<?= lang('Items.unit_price_required') ?>",
                        number: "<?= lang('Items.unit_price_number') ?>"
                    },
                    <?php foreach ($stock_locations as $key => $location_detail) { ?>
                        <?= esc("quantity_$key", 'js') ?>: {
                            required: "<?= lang('Items.quantity_required') ?>",
                            number: "<?= lang('Items.quantity_number') ?>"
                        },
                    <?php } ?>
                    receiving_quantity: {
                        required: "<?= lang('Items.quantity_required') ?>",
                        number: "<?= lang('Items.quantity_number') ?>"
                    },
                    reorder_level: {
                        required: "<?= lang('Items.reorder_level_required') ?>",
                        number: "<?= lang('Items.reorder_level_number') ?>"
                    },
                    tax_percent: {
                        number: "<?= lang('Items.tax_percent_number') ?>"
                    }
                }
            }, form_support.error))
        };

        init_validation();
    });
</script>
