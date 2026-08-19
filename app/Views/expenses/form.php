<?php
/**
 * @var object $expenses_info
 * @var array $payment_options
 * @var array $expense_categories
 * @var array $employees
 * @var string $controller_name
 * @var array $config
 */
?>

<?= form_open("expenses/save/$expenses_info->expense_id", ['id' => 'expenses_edit_form']) ?>

    <ul id="error_message_box" class="alert alert-warning d-none"></ul>

    <div class="mb-3"><?= lang('Expenses.info') ?> <?= !empty($expenses_info->expense_id) ? lang('Expenses.expense_id') . " $expenses_info->expense_id" : '' ?></div>

    <label for="datetime" class="form-label"><?= lang('Expenses.date'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="datetime-icon"><i class="bi bi-calendar2"></i></span>
        <input type="hidden" name="date" id="datetime" aria-describedby="datetime-icon" value="<?= to_datetime(strtotime($expenses_info->date)) ?>">
        <input type="text" class="form-control" value="<?= to_datetime(strtotime($expenses_info->date)) ?>" disabled readonly>
    </div>

    <label for="supplier_name" class="form-label"><?= lang('Expenses.supplier_name'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="supplier_name-icon"><i class="bi bi-truck"></i></span>
        <input type="hidden" name="supplier_id" id="supplier_id">
        <input type="text" class="form-control"  name="supplier_name" id="supplier_name" aria-describedby="supplier_name-icon" value="<?= lang('Expenses.start_typing_supplier_name') ?>">
        <button type="button" class="btn btn-outline-danger" id="remove_supplier_button" title="Remove Supplier"><i class="bi bi-x-circle"></i></button>
    </div>

    <label for="supplier_tax_code" class="form-label"><?= lang('Expenses.supplier_tax_code'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="supplier_tax_code-icon"><i class="bi bi-piggy-bank"></i></span>
        <input type="text" class="form-control" name="supplier_tax_code" id="supplier_tax_code" aria-describedby="supplier_tax_code-icon" value="<?= $expenses_info->supplier_tax_code ?>">
    </div>

    <label for="amount" class="form-label"><?= lang('Expenses.amount'); ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
    <div class="input-group mb-3">
        <?php if (!is_right_side_currency_symbol()): ?>
            <span class="input-group-text" id="amount-icon"><?= esc($config['currency_symbol']) ?></span>
        <?php endif; ?>
        <input class="form-control" name="amount" id="amount" aria-describedby="amount-icon" value="<?= to_currency_no_money($expenses_info->amount) ?>" required>
        <?php if (is_right_side_currency_symbol()): ?>
            <span class="input-group-text" id="amount-icon"><?= esc($config['currency_symbol']) ?></span>
        <?php endif; ?>
    </div>

    <label for="tax_amount" class="form-label"><?= lang('Expenses.tax_amount'); ?></label>
    <div class="input-group mb-3">
        <?php if (!is_right_side_currency_symbol()): ?>
            <span class="input-group-text" id="tax_amount-icon"><?= esc($config['currency_symbol']) ?></span>
        <?php endif; ?>
        <input class="form-control" name="tax_amount" id="tax_amount" aria-describedby="tax_amount-icon" value="<?= to_currency_no_money($expenses_info->tax_amount) ?>">
        <?php if (is_right_side_currency_symbol()): ?>
            <span class="input-group-text" id="tax_amount-icon"><?= esc($config['currency_symbol']) ?></span>
        <?php endif; ?>
    </div>

    <label for="payment_type" class="form-label"><?= lang('Expenses.payment'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="payment_type-icon"><i class="bi bi-wallet2"></i></span>
        <select class="form-select" name="payment_type" id="payment_type" aria-describedby="payment_type-icon">
            <?php foreach ($payment_options as $k => $v): ?>
                <option value="<?= $k ?>" <?= $k == $expenses_info->payment_type ? 'selected' : '' ?>>
                    <?= $v ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <label for="category" class="form-label"><?= lang('Expenses_categories.name'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="category-icon"><i class="bi bi-bookmark"></i></span>
        <select class="form-select" name="expense_category_id" id="category" aria-describedby="category-icon">
            <?php foreach ($expense_categories as $k => $v): ?>
                <option value="<?= $k ?>" <?= $k == $expenses_info->expense_category_id ? 'selected' : '' ?>>
                    <?= $v ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <label for="employee" class="form-label"><?= lang('Expenses.employee'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="employee-icon"><i class="bi bi-person"></i></span>
        <?php if ($can_assign_employee): ?>
            <select class="form-select" name="employee_id" id="employee_id">
                <?php foreach ($employees as $k => $v): ?>
                    <option value="<?= $k ?>" <?= $k == $expenses_info->employee_id ? 'selected' : '' ?>>
                        <?= esc($v) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php else: ?>
            <input type="hidden" name="employee_id" value="<?= $expenses_info->employee_id ?>">
            <input type="text" class="form-control" name="employee" id="employee" aria-describedby="employee-icon" value="<?= esc($employees[$expenses_info->employee_id] ?? '') ?>" disabled readonly>
        <?php endif; ?>
    </div>

    <label for="description" class="form-label"><?= lang('Expenses.description'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text"><i class="bi bi-chat"></i></span>
        <textarea class="form-control" name="description" id="description" rows="6"><?= $expenses_info->description ?></textarea>
    </div>

    <?php if (!empty($expenses_info->expense_id)): ?>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="deleted" id="deleted" value="1" <?= $expenses_info->deleted == 1 ? 'checked' : '' ?>>
            <label class="form-check-label text-danger" for="deleted"><?= lang('Expenses.is_deleted') ?></label>
        </div>
    <?php endif; ?>

<?= form_close() ?>

<script type="text/javascript">
    // Validation and submit handling
    $(document).ready(function() {
        <?= view('partial/datepicker_locale') ?>

        $('#supplier_name').click(function() {
            $(this).attr('value', '');
        });

        $('#supplier_name').autocomplete({
            source: '<?= "suppliers/suggest" ?>',
            minChars: 0,
            delay: 10,
            select: function(event, ui) {
                $('#supplier_id').val(ui.item.value);
                $(this).val(ui.item.label);
                $(this).attr('readonly', 'readonly');
                $('#remove_supplier_button').css('display', 'inline-block');
                return false;
            }
        });

        $('#supplier_name').blur(function() {
            $(this).attr('value', "<?= lang('Expenses.start_typing_supplier_name') ?>");
        });

        $('#remove_supplier_button').css('display', 'none');

        $('#remove_supplier_button').click(function() {
            $('#supplier_id').val('');
            $('#supplier_name').removeAttr('readonly');
            $('#supplier_name').val('');
            $(this).css('display', 'none');
        });

        <?php if ($expenses_info->expense_id != -1) { ?>
            $('#supplier_id').val('<?= $expenses_info->supplier_id ?>');
            $('#supplier_name').val('<?= esc($expenses_info->supplier_name, 'js') ?>').attr('readonly', 'readonly');
            $('#remove_supplier_button').css('display', 'inline-block');
        <?php } ?>

        $('#expenses_edit_form').validate($.extend({
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

            ignore: '',

            rules: {
                supplier_name: 'required',
                category: 'required',
                expense_category_id: 'required',
                date: {
                    required: true
                },
                amount: {
                    required: true,
                    remote: "<?= "$controller_name/checkNumeric" ?>"
                },
                tax_amount: {
                    remote: "<?= "$controller_name/checkNumeric" ?>"
                }
            },

            messages: {
                category: "<?= lang('Expenses.category_required') ?>",
                expense_category_id: "<?= lang('Expenses_categories.category_name_required') ?>",
                date: {
                    required: "<?= lang('Expenses.date_required') ?>"

                },
                amount: {
                    required: "<?= lang('Expenses.amount_required') ?>",
                    remote: "<?= lang('Expenses.amount_number') ?>"
                },
                tax_amount: {
                    remote: "<?= lang('Expenses.tax_amount_number') ?>"
                }
            }
        }, form_support.error));
    });
</script>
