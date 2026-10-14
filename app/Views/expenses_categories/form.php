<?php
/**
 * @var object $category_info
 * @var string $controller_name
 */
?>

<?= form_open("expenses_categories/save/$category_info->expense_category_id", ['id' => 'expense_category_edit_form']) ?>

    <ul id="error_message_box" class="alert alert-warning d-none"></ul>

    <label for="category_name" class="form-label"><?= lang('Expenses_categories.name'); ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="category_name-icon"><i class="bi bi-bookmark"></i></span>
        <input type="text" class="form-control" name="category_name" id="category_name" aria-describedby="category_name-icon" value="<?= $category_info->category_name; ?>" required>
    </div>

    <label for="category_description" class="form-label"><?= lang('Expenses_categories.description'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="category_description-icon"><i class="bi bi-card-text"></i></span>
        <textarea class="form-control" name="category_description" id="category_description" rows="10" aria-describedby="category_description-icon"><?= $category_info->category_description ?></textarea>
    </div>

<?= form_close() ?>

<script type="text/javascript">
    // Validation and submit handling
    $(document).ready(function() {
        $('#expense_category_edit_form').validate($.extend({
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
                category_name: 'required'
            },

            messages: {
                category_name: "<?= lang('Expenses_categories.category_name_required') ?>"
            }
        }, form_support.error));
    });
</script>
