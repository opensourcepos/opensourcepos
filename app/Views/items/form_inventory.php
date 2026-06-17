<?php
/**
 * @var object $item_info
 * @var array $stock_locations
 * @var array $item_quantities
 * @var string $controller_name
 */
?>

<?= form_open("items/saveInventory/$item_info->item_id", ['id' => 'item_form']) ?>

    <ul id="error_message_box" class="alert alert-warning d-none"></ul>

    <label for="item_number" class="form-label"><?= lang('Items.item_number'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="item_number-icon"><i class="bi bi-upc-scan"></i></span>
        <input type="text" class="form-control" name="item_number" id="item_number" aria-describedby="item_number-icon" value="<?= $item_info->item_number ?>" disabled readonly>
    </div>

    <label for="name" class="form-label"><?= lang('Items.name'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="name-icon"><i class="bi bi-tag"></i></span>
        <input type="text" class="form-control" name="name" id="name" aria-describedby="name-icon" value="<?= $item_info->name ?>" disabled readonly>
    </div>

    <label for="category" class="form-label"><?= lang('Items.category'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="category-icon"><i class="bi bi-bookmark"></i></span>
        <input type="text" class="form-control" name="category" id="category" aria-describedby="category-icon" value="<?= $item_info->category ?>" disabled readonly>
    </div>

    <label for="stock_location" class="form-label"><?= lang('Items.stock_location'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text"><i class="bi bi-boxes"></i></span>
        <select class="form-select" name="stock_location" id="stock_location" onchange="fill_quantity(this.value)">
            <?php foreach ($stock_locations as $value => $label): ?>
                <option value="<?= $value ?>" <?= $value == current($stock_locations) ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <label for="quantity" class="form-label"><?= lang('Items.current_quantity'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="quantity-icon"><i class="bi bi-box"></i></span>
        <input type="text" class="form-control" name="quantity" id="quantity" aria-describedby="quantity-icon" value="<?= to_quantity_decimals(current($item_quantities)) ?>" disabled readonly>
    </div>

    <label for="newquantity" class="form-label"><?= lang('Items.add_minus'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="newquantity-icon"><i class="bi bi-plus-slash-minus"></i></span>
        <input type="number" step="1" class="form-control" name="newquantity" id="newquantity" aria-describedby="newquantity-icon">
    </div>

    <label for="trans_comment" class="form-label"><?= lang('Items.inventory_comments'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text"><i class="bi bi-chat"></i></span>
        <textarea class="form-control" name="trans_comment" id="trans_comment" rows="6"></textarea>
    </div>

<?= form_close() ?>

<script type="text/javascript">
    // Validation and submit handling
    $(document).ready(function() {
        $('#item_form').validate($.extend({
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
                newquantity: {
                    required: true,
                    number: true
                }
            },

            messages: {
                newquantity: {
                    required: "<?= lang('Items.quantity_required') ?>",
                    number: "<?= lang('Items.quantity_number') ?>"
                }
            }
        }, form_support.error));
    });

    function fill_quantity(val) {
        var item_quantities = <?= json_encode(esc($item_quantities, 'raw')) ?>;
        document.getElementById('quantity').value = parseFloat(item_quantities[val]).toFixed(<?= quantity_decimals() ?>);
    }
</script>
