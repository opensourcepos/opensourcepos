<?php
/**
 * @var string $controller_name
 * @var object $person_info
 * @var array $categories
 */
?>

<?= form_open("$controller_name/save/$person_info->person_id", ['id' => 'supplier_form']) ?>

    <ul id="error_message_box" class="alert alert-warning d-none"></ul>

    <label for="company_name" class="form-label"><?= lang('Suppliers.company_name'); ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="company_name-icon"><i class="bi bi-building"></i></span>
        <input type="text" class="form-control" name="company_name" id="company_name" aria-describedby="company_name-icon" value="<?= html_entity_decode($person_info->company_name) ?>" required>
    </div>

    <label for="category" class="form-label"><?= lang('Suppliers.category'); ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
    <div class="input-group mb-3">
        <span class="input-group-text"><i class="bi bi-truck"></i></span>
        <select class="form-select" name="category" id="category" required>
            <?php foreach ($categories as $key => $label): ?>
                <option value="<?= $key ?>" <?= $person_info->category == $key ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <label for="agency_name" class="form-label"><?= lang('Suppliers.agency_name'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="agency_name-icon"><i class="bi bi-building"></i></span>
        <input type="text" class="form-control" name="agency_name" id="agency_name" aria-describedby="agency_name-icon" value="<?= $person_info->agency_name ?>">
    </div>

    <?= view('people/form_basic_info') ?>

    <label for="account_number" class="form-label"><?= lang('Suppliers.account_number'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="account_number-icon"><i class="bi bi-hash"></i></span>
        <input type="text" class="form-control" name="account_number" id="account_number" aria-describedby="account_number-icon" value="<?= $person_info->account_number ?>">
    </div>

    <label for="tax_id" class="form-label"><?= lang('Suppliers.tax_id'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="tax_id-icon"><i class="bi bi-bank"></i></span>
        <input type="text" class="form-control" name="tax_id" id="tax_id" aria-describedby="tax_id-icon" value="<?= $person_info->tax_id ?>">
    </div>

<?= form_close() ?>

<script type="text/javascript">
    // Validation and submit handling
    $(document).ready(function() {
        $('#supplier_form').validate($.extend({
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
                company_name: 'required',
                first_name: 'required',
                last_name: 'required',
                email: 'email'
            },

            messages: {
                company_name: "<?= lang('Suppliers.company_name_required') ?>",
                first_name: "<?= lang('Common.first_name_required') ?>",
                last_name: "<?= lang('Common.last_name_required') ?>",
                email: "<?= lang('Common.email_invalid_format') ?>"
            }
        }, form_support.error));
    });
</script>
