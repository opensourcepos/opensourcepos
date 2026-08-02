<?php
/**
 * @var object $cash_ups_info
 * @var array $employees
 * @var string $controller_name
 * @var array $config
 */
?>

<?= form_open('cashups/save/' . $cash_ups_info->cashup_id, ['id' => 'cashups_edit_form'])    // TODO: String Interpolation ?>

    <ul id="error_message_box" class="alert alert-warning d-none"></ul>

    <div class="mb-3"><?= lang('Cashups.info') ?> <?= !empty($cash_ups_info->cashup_id) ? lang('Cashups.id') . " $cash_ups_info->cashup_id" : '' ?></div>

    <label for="open_date" class="form-label"><?= lang('Cashups.open_date'); ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="open_date-icon"><i class="bi bi-calendar2-check"></i></span>
        <input type="text" class="form-control datepicker" name="open_date" id="open_date" aria-describedby="open_date-icon" value="<?= to_datetime(strtotime($cash_ups_info->open_date)) ?>" required>
    </div>

    <label for="open_employee" class="form-label"><?= lang('Cashups.open_employee'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="open_employee-icon"><i class="bi bi-person"></i></span>
        <select class="form-select" name="open_employee_id" id="open_employee_id">
            <?php foreach ($employees as $id => $name): ?>
                <option value="<?= $id ?>" <?= $id == $cash_ups_info->open_employee_id ? 'selected' : '' ?>><?= esc($name) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <label for="open_amount_cash" class="form-label"><?= lang('Cashups.open_amount_cash'); ?></label>
    <div class="input-group mb-3">
        <?php if (!is_right_side_currency_symbol()): ?>
            <span class="input-group-text" id="tax_amount-icon"><?= esc($config['currency_symbol']) ?></span>
        <?php endif; ?>
        <input class="form-control" name="open_amount_cash" id="open_amount_cash" aria-describedby="open_amount_cash-icon" value="<?= to_currency_no_money($cash_ups_info->open_amount_cash) ?>">
        <?php if (is_right_side_currency_symbol()): ?>
            <span class="input-group-text" id="tax_amount-icon"><?= esc($config['currency_symbol']) ?></span>
        <?php endif; ?>
    </div>

    <label for="transfer_amount_cash" class="form-label"><?= lang('Cashups.transfer_amount_cash'); ?></label>
    <div class="input-group mb-3">
        <?php if (!is_right_side_currency_symbol()): ?>
            <span class="input-group-text" id="transfer_amount_cash-icon"><?= esc($config['currency_symbol']) ?></span>
        <?php endif; ?>
        <input class="form-control" name="transfer_amount_cash" id="transfer_amount_cash" aria-describedby="transfer_amount_cash-icon" value="<?= to_currency_no_money($cash_ups_info->transfer_amount_cash) ?>">
        <?php if (is_right_side_currency_symbol()): ?>
            <span class="input-group-text" id="transfer_amount_cash-icon"><?= esc($config['currency_symbol']) ?></span>
        <?php endif; ?>
    </div>

    <label for="close_date" class="form-label"><?= lang('Cashups.close_date'); ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="close_date-icon"><i class="bi bi-calendar2-x"></i></span>
        <input type="text" class="form-control datepicker" name="close_date" id="close_date" aria-describedby="close_date-icon" value="<?= to_datetime(strtotime($cash_ups_info->close_date)) ?>" required>
    </div>

    <label for="close_employee" class="form-label"><?= lang('Cashups.close_employee'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="close_employee-icon"><i class="bi bi-person"></i></span>
        <select class="form-select" name="close_employee_id" id="close_employee_id">
            <?php foreach ($employees as $id => $name): ?>
                <option value="<?= $id ?>" <?= $id == $cash_ups_info->close_employee_id ? 'selected' : '' ?>><?= esc($name) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <label for="closed_amount_cash" class="form-label"><?= lang('Cashups.closed_amount_cash'); ?></label>
    <div class="input-group mb-3">
        <?php if (!is_right_side_currency_symbol()): ?>
            <span class="input-group-text" id="closed_amount_cash-icon"><?= esc($config['currency_symbol']) ?></span>
        <?php endif; ?>
        <input class="form-control" name="closed_amount_cash" id="closed_amount_cash" aria-describedby="closed_amount_cash-icon" value="<?= to_currency_no_money($cash_ups_info->closed_amount_cash) ?>">
        <?php if (is_right_side_currency_symbol()): ?>
            <span class="input-group-text" id="closed_amount_cash-icon"><?= esc($config['currency_symbol']) ?></span>
        <?php endif; ?>
    </div>

    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="note" id="note" value="0" <?= $cash_ups_info->note == 1 ? 'checked' : '' ?>>
        <label class="form-check-label" for="note"><?= lang('Cashups.note') ?></label>
    </div>

    <label for="closed_amount_due" class="form-label"><?= lang('Cashups.closed_amount_due'); ?></label>
    <div class="input-group mb-3">
        <?php if (!is_right_side_currency_symbol()): ?>
            <span class="input-group-text" id="closed_amount_due-icon"><?= esc($config['currency_symbol']) ?></span>
        <?php endif; ?>
        <input class="form-control" name="closed_amount_due" id="closed_amount_due" aria-describedby="closed_amount_due-icon" value="<?= to_currency_no_money($cash_ups_info->closed_amount_due) ?>">
        <?php if (is_right_side_currency_symbol()): ?>
            <span class="input-group-text" id="closed_amount_due-icon"><?= esc($config['currency_symbol']) ?></span>
        <?php endif; ?>
    </div>

    <label for="closed_amount_card" class="form-label"><?= lang('Cashups.closed_amount_card'); ?></label>
    <div class="input-group mb-3">
        <?php if (!is_right_side_currency_symbol()): ?>
            <span class="input-group-text" id="closed_amount_card-icon"><?= esc($config['currency_symbol']) ?></span>
        <?php endif; ?>
        <input class="form-control" name="closed_amount_card" id="closed_amount_card" aria-describedby="closed_amount_card-icon" value="<?= to_currency_no_money($cash_ups_info->closed_amount_card) ?>">
        <?php if (is_right_side_currency_symbol()): ?>
            <span class="input-group-text" id="closed_amount_card-icon"><?= esc($config['currency_symbol']) ?></span>
        <?php endif; ?>
    </div>

    <label for="closed_amount_check" class="form-label"><?= lang('Cashups.closed_amount_check'); ?></label>
    <div class="input-group mb-3">
        <?php if (!is_right_side_currency_symbol()): ?>
            <span class="input-group-text" id="closed_amount_check-icon"><?= esc($config['currency_symbol']) ?></span>
        <?php endif; ?>
        <input class="form-control" name="closed_amount_check" id="closed_amount_check" aria-describedby="closed_amount_check-icon" value="<?= to_currency_no_money($cash_ups_info->closed_amount_check) ?>">
        <?php if (is_right_side_currency_symbol()): ?>
            <span class="input-group-text" id="closed_amount_check-icon"><?= esc($config['currency_symbol']) ?></span>
        <?php endif; ?>
    </div>

    <label for="closed_amount_total" class="form-label"><?= lang('Cashups.closed_amount_total'); ?></label>
    <div class="input-group mb-3">
        <?php if (!is_right_side_currency_symbol()): ?>
            <span class="input-group-text" id="closed_amount_total-icon"><?= esc($config['currency_symbol']) ?></span>
        <?php endif; ?>
        <input type="hidden" name="closed_amount_total" id="closed_amount_total" aria-describedby="closed_amount_total-icon" value="<?= to_currency_no_money($cash_ups_info->closed_amount_total) ?>">
        <input type="text" class="form-control" value="<?= to_currency_no_money($cash_ups_info->closed_amount_total) ?>" readonly disabled>
        <?php if (is_right_side_currency_symbol()): ?>
            <span class="input-group-text" id="closed_amount_total-icon"><?= esc($config['currency_symbol']) ?></span>
        <?php endif; ?>
    </div>

    <label for="description" class="form-label"><?= lang('Cashups.description'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text"><i class="bi bi-chat"></i></span>
        <textarea class="form-control" name="description" id="description" rows="6"><?= $cash_ups_info->description ?></textarea>
    </div>

    <?php if (!empty($cash_ups_info->cashup_id)): ?>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="deleted" id="deleted" value="1" <?= $cash_ups_info->deleted == 1 ? 'checked' : '' ?>>
            <label class="form-check-label text-danger" for="deleted"><?= lang('Cashups.is_deleted') ?></label>
        </div>
    <?php endif; ?>

<?= form_close() ?>

<script type="text/javascript">
    // Validation and submit handling
    $(document).ready(function() {
        <?= view('partial/datepicker_locale') ?>

        $('#open_date').datetimepicker({
            format: "<?= dateformat_bootstrap($config['dateformat']) . ' ' . dateformat_bootstrap($config['timeformat']) ?>",
            startDate: "<?= date($config['dateformat'] . ' ' . esc($config['timeformat'], 'js'), mktime(0, 0, 0, 1, 1, 2010)) ?>",
            <?php
            $t = $config['timeformat'];
            $m = $t[strlen($t) - 1];
            if (str_contains($config['timeformat'], 'a') || str_contains($config['timeformat'], 'A')) {
            ?>
                showMeridian: true,
            <?php } else { ?>
                showMeridian: false,
            <?php } ?>
            minuteStep: 1,
            autoclose: true,
            todayBtn: true,
            todayHighlight: true,
            bootcssVer: 3,
            language: '<?= current_language_code() ?>'
        });

        $('#close_date').datetimepicker({
            format: "<?= dateformat_bootstrap($config['dateformat']) . ' ' . dateformat_bootstrap($config['timeformat']) ?>",
            startDate: "<?= date($config['dateformat'] . ' ' . esc($config['timeformat'], 'js'), mktime(0, 0, 0, 1, 1, 2010)) ?>",
            <?php
            $t = $config['timeformat'];
            $m = $t[strlen($t) - 1];
            if (str_contains($config['timeformat'], 'a') || str_contains($config['timeformat'], 'A')) {
            ?>
                showMeridian: true,
            <?php } else { ?>
                showMeridian: false,
            <?php } ?>
            minuteStep: 1,
            autoclose: true,
            todayBtn: true,
            todayHighlight: true,
            bootcssVer: 3,
            language: '<?= current_language_code() ?>'
        });

        $('#open_amount_cash, #transfer_amount_cash, #closed_amount_cash, #closed_amount_due, #closed_amount_card, #closed_amount_check').keyup(function() {
            $.post("<?= esc("$controller_name/ajax_cashup_total") ?>", {
                    'open_amount_cash': $('#open_amount_cash').val(),
                    'transfer_amount_cash': $('#transfer_amount_cash').val(),
                    'closed_amount_due': $('#closed_amount_due').val(),
                    'closed_amount_cash': $('#closed_amount_cash').val(),
                    'closed_amount_card': $('#closed_amount_card').val(),
                    'closed_amount_check': $('#closed_amount_check').val()
                },
                function(response) {
                    $('#closed_amount_total').val(response.total);
                },
                'json'
            );
        });

        var submit_form = function() {
            $(this).ajaxSubmit({
                success: function(response) {
                    dialog_support.hide();
                    table_support.handle_submit('<?= esc('cashups') ?>', response);
                },
                dataType: 'json'
            });
        };

        $('#cashups_edit_form').validate($.extend({
            submitHandler: function(form) {
                submit_form.call(form);
            },
            rules: {

            },
            messages: {
                open_date: {
                    required: '<?= lang('Cashups.date_required') ?>'

                },
                close_date: {
                    required: '<?= lang('Cashups.date_required') ?>'

                },
                amount: {
                    required: '<?= lang('Cashups.amount_required') ?>',
                    number: '<?= lang('Cashups.amount_number') ?>'
                }
            }
        }, form_support.error));
    });
</script>
