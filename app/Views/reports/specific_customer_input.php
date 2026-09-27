<?php
/**
 * @var string $specific_input_name
 * @var array $specific_input_data
 * @var array $sale_type_options
 * @var array $payment_type
 */
?>

<?= view('partial/header') ?>

<?php
$title_info['config_title'] = lang('Reports.report_input');
echo view('configs/config_header', $title_info);
?>

<?php
if (isset($error)) {
    echo '<div class="alert alert-dismissible alert-danger">' . esc($error) . '</div>';
}
?>

<?= form_open('#', ['id' => 'item_form', 'enctype' => 'multipart/form-data']) ?>

    <div class="row">
        <div class="col-12 col-lg-6">
            <label for="daterangepicker" class="form-label"><?= lang('Reports.date_range') ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="daterangepicker-icon"><i class="bi bi-calendar2-range"></i></span>
                <input type="text" class="form-select" name="daterangepicker" id="daterangepicker" aria-describedby="daterangepicker-icon">
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <label for="specific_input_data" class="form-label"><?= $specific_input_name ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-person"></i></span>
                <select class="form-select" name="specific_input_data" id="specific_input_data" required>
                    <?php foreach ($specific_input_data as $key => $value): ?>
                        <option value="<?= $key ?>"><?= $value ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <label for="input_type" class="form-label"><?= lang('Reports.sale_type') ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
            <div class="input-group mb-3 report_sale_type">
                <span class="input-group-text"><i class="bi bi-receipt"></i></span>
                <select class="form-select" name="sale_type" id="input_type" required>
                    <?php foreach ($sale_type_options as $key => $value): ?>
                        <option value="<?= $key ?>" <?= $key === 'complete' ? 'selected' : '' ?>><?= $value ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <label for="input_payment_type" class="form-label"><?= lang('Reports.payment_type') ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
            <div class="input-group mb-3 report_sale_type">
                <span class="input-group-text"><i class="bi bi-receipt"></i></span>
                <select class="form-select" name="payment_type" id="input_payment_type" required>
                    <?php foreach ($payment_type as $key => $value): ?>
                        <option value="<?= $key ?>"><?= $value ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-primary" name="generate_report" id="generate_report"><?= lang('Common.submit'); ?></button>
    </div>

<?= form_close() ?>

<?= view('partial/footer') ?>

<script type="text/javascript">
    new TomSelect('#specific_input_data',{
        plugins: ['dropdown_input'],
        placeholder: 'Type to search...',
    });

    $(document).ready(function() {
        <?= view('partial/daterangepicker') ?>

        $("#generate_report").click(function() {
            window.location = [window.location, start_date, end_date, $('#specific_input_data').val(), $("#input_type").val(), $('#input_payment_type').val() || 0].join("/");
        });

    });
</script>
