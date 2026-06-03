<?php
/**
 * @var object $item_kit_info
 * @var string $selected_kit_item
 * @var int $selected_kit_item_id
 * @var array $item_kit_items
 * @var string $controller_name
 */
?>

<?= form_open("item_kits/save/$item_kit_info->item_kit_id", ['id' => 'item_kit_form']) ?>

    <ul id="error_message_box" class="alert alert-warning d-none"></ul>

    <label for="item_kit_number" class="form-label"><?= lang('Item_kits.item_kit_number'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="item_kit_number-icon"><i class="bi bi-upc-scan"></i></span>
        <input type="text" class="form-control" name="item_kit_number" id="item_kit_number" aria-describedby="item_kit_number-icon" value="<?= $item_kit_info->item_kit_number ?>">
    </div>

    <label for="name" class="form-label"><?= lang('Item_kits.name'); ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="name-icon"><i class="bi bi-tags"></i></span>
        <input type="text" class="form-control" name="name" id="name" aria-describedby="name-icon" value="<?= $item_kit_info->name ?>" required>
    </div>

    <label for="item_name" class="form-label"><?= lang('Item_kits.find_kit_item'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="item_name-icon"><i class="bi bi-tag"></i></span>
        <input type="hidden" name="kit_item_id" value="<?= (string)$selected_kit_item_id ?>">
        <input type="text" class="form-control" name="item_name" id="item_name" aria-describedby="item_name-icon" value="<?= $selected_kit_item ?>">
    </div>

    <label for="kit_discount_type" class="form-label"><?= lang('Item_kits.discount_type') ?></label>
    <div class="mb-3">
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="kit_discount_type" id="kit_discount_type_percent" value="0" <?= $item_kit_info->kit_discount_type == PERCENT ? 'checked' : '' ?>>
            <label class="form-check-label" for="kit_discount_type_percent"><?= lang('Item_kits.discount_percent') ?></label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="kit_discount_type" id="kit_discount_type_fixed" value="1" <?= $item_kit_info->kit_discount_type == FIXED ? 'checked' : '' ?>>
            <label class="form-check-label" for="kit_discount_type_fixed"><?= lang('Item_kits.discount_fixed') ?></label>
        </div>
    </div>

    <label for="kit_discount" class="form-label"><?= lang('Item_kits.discount'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="kit_discount-icon"><i class="bi bi-patch-minus"></i></span>
        <input type="number" step="any" class="form-control" name="kit_discount" id="kit_discount" aria-describedby="kit_discount-icon" value="<?= $item_kit_info->kit_discount_type === FIXED ? to_currency_no_money($item_kit_info->kit_discount) : to_decimals($item_kit_info->kit_discount) ?>">
    </div>

    <label for="price_option" class="form-label"><?= lang('Item_kits.price_option') ?> <?= !empty($basic_version) ? '<sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup>' : '' ?></label>
    <div class="mb-3">
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="price_option" id="price_option_kit_and_components" value="0" <?= $item_kit_info->price_option == PRICE_ALL ? 'checked' : '' ?> <?= !empty($basic_version) ? 'required' : '' ?>>
            <label class="form-check-label" for="price_option_kit_and_components"><?= lang('Item_kits.kit_and_components') ?></label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="price_option" id="price_option_kit_only" value="1" <?= $item_kit_info->price_option == PRICE_KIT ? 'checked' : '' ?> <?= !empty($basic_version) ? 'required' : '' ?>>
            <label class="form-check-label" for="price_option_kit_only"><?= lang('Item_kits.kit_only') ?></label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="price_option" id="price_option_kit_and_stock" value="2" <?= $item_kit_info->price_option == PRICE_KIT_ITEMS ? 'checked' : '' ?> <?= !empty($basic_version) ? 'required' : '' ?>>
            <label class="form-check-label" for="price_option_kit_and_stock"><?= lang('Item_kits.kit_and_stock') ?></label>
        </div>
    </div>

    <label for="print_option" class="form-label"><?= lang('Item_kits.print_option') ?> <?= !empty($basic_version) ? '<sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup>' : '' ?></label>
    <div class="mb-3">
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="print_option" id="print_option_all" value="0" <?= $item_kit_info->print_option == PRINT_ALL ? 'checked' : '' ?> <?= !empty($basic_version) ? 'required' : '' ?>>
            <label class="form-check-label" for="print_option_all"><?= lang('Item_kits.all') ?></label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="print_option" id="print_option_priced_only" value="1" <?= $item_kit_info->print_option == PRINT_PRICED ? 'checked' : '' ?> <?= !empty($basic_version) ? 'required' : '' ?>>
            <label class="form-check-label" for="print_option_priced_only"><?= lang('Item_kits.priced_only') ?></label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="print_option" id="print_option_kit_only" value="2" <?= $item_kit_info->print_option == PRINT_KIT ? 'checked' : '' ?> <?= !empty($basic_version) ? 'required' : '' ?>>
            <label class="form-check-label" for="print_option_kit_only"><?= lang('Item_kits.kit_only') ?></label>
        </div>
    </div>

    <label for="description" class="form-label"><?= lang('Item_kits.description'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text"><i class="bi bi-chat"></i></span>
        <textarea class="form-control" name="description" id="description" rows="6"><?= $item_kit_info->description ?></textarea>
    </div>

    <label for="item" class="form-label"><?= lang('Item_kits.add_item'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="item-icon"><i class="bi bi-tag"></i></span>
        <input type="text" class="form-control" name="item" id="item" aria-describedby="item-icon">
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle text-nowrap" id="item_kit_items">
            <thead class="table-secondary">
                <tr>
                    <th scope="col"><?= lang('Common.delete') ?></th>
                    <th scope="col"><?= lang('Item_kits.sequence') ?></th>
                    <th scope="col"><?= lang('Item_kits.item') ?></th>
                    <th scope="col"><?= lang('Item_kits.quantity') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($item_kit_items as $item_kit_item): ?>
                    <tr>
                        <td class="text-center"><a class="text-danger" href="#" onclick="return delete_item_kit_row(this);"><i class="bi bi-trash"></i></a></td>
                        <td class="text-center"><input class="quantity form-control" id="item_seq_<?= $item_kit_item['item_id'] ?>" name="item_kit_seq[<?= $item_kit_item['item_id'] ?>]" value="<?= parse_decimals($item_kit_item['kit_sequence'], 0) ?>"></td>
                        <td><?= esc($item_kit_item['name']) ?></td>
                        <td class="text-center"><input class="quantity form-control" id="item_qty_<?= $item_kit_item['item_id'] ?>" name="item_kit_qty[<?= $item_kit_item['item_id'] ?>]" value="<?= to_quantity_decimals($item_kit_item['quantity']) ?>"></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?= form_close() ?>

<script type="text/javascript">
    // Validation and submit handling
    $(document).ready(function() {
        $('#item').autocomplete({
            source: '<?= "items/suggest" ?>',
            minChars: 0,
            autoFocus: false,
            delay: 10,
            appendTo: '.modal-content',
            select: function(e, ui) {
                if ($('#item_kit_item_' + ui.item.value).length == 1) {
                    $('#item_kit_item_' + ui.item.value).val(parseFloat($('#item_kit_item_' + ui.item.value).val()) + 1);
                } else {
                    $('#item_kit_items').append('<tr>' +
                        '<td class="text-center"><a class="text-danger" href="#" onclick="return delete_item_kit_row(this);"><i class="bi bi-trash"></i></a></td>' +
                        '<td class="text-center"><input class="quantity form-control" id="item_seq_' + ui.item.value + '" name="item_kit_seq[' + ui.item.value + ']" value="0"></td>' +
                        '<td>' + DOMPurify.sanitize(ui.item.label) + '</td>' +
                        '<td class="text-center"><input class="quantity form-control" id="item_qty_' + ui.item.value + '" name="item_kit_qty[' + ui.item.value + ']" value="1"></td>' +
                        '</tr>');
                }
                $('#item').val('');
                return false;
            }
        });

        $("input[name='item_name']").change(function() {
            if (!$("input[name='item_name']").val()) {
                $("input[name='kit_item_id']").val('');
            }
        });

        var fill_value = function(event, ui) {
            event.preventDefault();
            $("input[name='kit_item_id']").val(ui.item.value);
            $("input[name='item_name']").val(DOMPurify.sanitize(ui.item.label));
        };


        $('#item_name').autocomplete({
            source: "<?= 'items/suggestKits' ?>",
            minChars: 0,
            delay: 15,
            cacheLength: 1,
            appendTo: '.modal-content',
            select: fill_value,
            focus: fill_value
        });

        $('#item_kit_form').validate($.extend({
            submitHandler: function(form) {
                $(form).ajaxSubmit({
                    success: function(response) {
                        dialog_support.hide();
                        table_support.handle_submit("<?= esc($controller_name) ?>", response);
                    },
                    dataType: 'json'
                });
            },

            errorLabelContainer: '#error_message_box',

            rules: {
                name: 'required',
                category: 'required',
                item_kit_number: {
                    required: false,
                    remote: {
                        url: '<?= esc("$controller_name/checkItemNumber") ?>',
                        type: 'POST',
                        data: {
                            'item_kit_id': "<?= $item_kit_info->item_kit_id ?>",
                            'item_kit_number': function() {
                                return $('#item_kit_number').val();
                            }
                        }
                    }
                }
            },

            messages: {
                name: "<?= lang('Items.name_required') ?>",
                category: "<?= lang('Items.category_required') ?>",
                item_kit_number: "<?= lang('Item_kits.item_number_duplicate') ?>"
            }
        }, form_support.error));
    });

    function delete_item_kit_row(link) {
        $(link).parent().parent().remove();
        return false;
    }
</script>
