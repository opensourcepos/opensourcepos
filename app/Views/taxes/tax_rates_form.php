<?php
/**
 * @var int $tax_rate_id
 * @var array $tax_code_options
 * @var array $rate_tax_code_id
 * @var array $tax_category_options
 * @var array $rate_tax_category_id
 * @var array $tax_jurisdiction_options
 * @var array $rate_jurisdiction_id
 * @var float $tax_rate
 * @var array $rounding_options
 * @var array $tax_rounding_code
 */
?>

<?= form_open("taxes/save/$tax_rate_id", ['id' => 'tax_code_form']) ?>

    <ul id="error_message_box" class="alert alert-warning d-none"></ul>

    <label for="rate_tax_code_id" class="form-label"><?= lang('Taxes.tax_code'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="rate_tax_code_id-icon"><i class="bi bi-code"></i></span>
        <select class="form-select" name="rate_tax_code_id" id="rate_tax_code_id">
            <?php foreach ($tax_code_options as $id => $label): ?>
                <option value="<?= $id ?>" <?= $id == $rate_tax_code_id ? 'selected' : '' ?>><?= esc($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <label for="rate_tax_category_id" class="form-label"><?= lang('Taxes.tax_category'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="rate_tax_category_id-icon"><i class="bi bi-bookmark"></i></span>
        <select class="form-select" name="rate_tax_category_id" id="rate_tax_category_id">
            <?php foreach ($tax_category_options as $id => $label): ?>
                <option value="<?= $id ?>" <?= $id == $rate_tax_category_id ? 'selected' : '' ?>><?= esc($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <label for="rate_jurisdiction_id" class="form-label"><?= lang('Taxes.tax_jurisdiction'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="rate_jurisdiction_id-icon"><i class="bi bi-globe"></i></span>
        <select class="form-select" name="rate_jurisdiction_id" id="rate_jurisdiction_id">
            <?php foreach ($tax_jurisdiction_options as $id => $label): ?>
                <option value="<?= $id ?>" <?= $id == $rate_jurisdiction_id ? 'selected' : '' ?>><?= esc($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <label for="tax_rate" class="form-label"><?= lang('Taxes.tax_rate'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="tax_rate-icon"><i class="bi bi-bank"></i></span>
        <input type="text" class="form-control" name="tax_rate" id="tax_rate" aria-describedby="tax_rate-icon" value="<?= $tax_rate ?>">
        <span class="input-group-text" id="tax_rate-icon"><i class="bi bi-percent"></i></span>
    </div>

    <label for="tax_rounding_code" class="form-label"><?= lang('Taxes.tax_rounding'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="tax_rounding_code-icon"><i class="bi bi-arrow-repeat"></i></span>
        <select class="form-select" name="tax_rounding_code" id="tax_rounding_code">
            <?php foreach ($rounding_options as $id => $label): ?>
                <option value="<?= $id ?>" <?= $id == $tax_rounding_code ? 'selected' : '' ?>><?= esc($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

<?= form_close() ?>

<script type="text/javascript">
    // Validation and submit handling
    $(document).ready(function() {

        $('#tax_code_form').validate($.extend({
            submitHandler: function(form) {
                $(form).ajaxSubmit({
                    success: function(response) {
                        dialog_support.hide();
                        table_support.handle_submit('<?= 'taxes' ?>', response);
                    },
                    dataType: 'json'
                });
            },
            rules: {},
            messages: {}
        }, form_support.error));


    });

    function delete_tax_rate_row(link) {
        $(link).parent().parent().remove();
        return false;
    }
</script>
