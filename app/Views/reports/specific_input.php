<?php
/**
 * @var string $specific_input_name
 * @var array $specific_input_data
 * @var array $sale_type_options
 * @var array $config
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
            <label for="daterangepicker" class="form-label"><?= lang('Reports.date_range') ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="daterangepicker-icon"><i class="bi bi-calendar2-range"></i></span>
                <input type="text" class="form-select" name="daterangepicker" id="daterangepicker" aria-describedby="daterangepicker-icon" required>
            </div>
        </div>

        <?php if (isset($discount_type_options)) { ?>
            <div class="col-12 col-lg-6">
                <label for="reports_discount_type_label" class="form-label"><?= lang('Reports.discount_type') ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
                <div class="input-group mb-3" id="report_discount_type">
                    <span class="input-group-text"><i class="bi bi-patch-minus"></i></span>
                    <select class="form-select" name="discount_type" id="discount_type_id" required>
                        <?php foreach ($discount_type_options as $value => $label) { ?>
                            <option value="<?= $value ?>" <?= $value == $config['default_sales_discount_type'] ? 'selected' : '' ?>><?= $label ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
        <?php } ?>

        <div class="col-12 col-lg-6">
            <label for="specific_input_name_label" class="form-label"><?= $specific_input_name ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
            <div class="input-group mb-3 discount_percent">
                <span class="input-group-text"><i class="bi bi-dot"></i></span>
                <select class="form-select w-25" name="specific_input_data" id="specific_input_data" required>
                    <?php foreach ($specific_input_data as $value => $label) { ?>
                        <option value="<?= $value ?>"><?= $label ?></option>
                    <?php } ?>
                </select>
            </div>
            <?php if (isset($discount_type_options)) { ?>
                <div class="input-group mb-3 discount_fixed">
                    <span class="input-group-text"><i class="bi bi-cash"></i></span>
                    <input type="number" min="0" name="discount_fixed" id="discount_fixed" class="form-control" value="<?= $config['default_sales_discount'] ?>" required>
                </div>
            <?php } ?>
        </div>

        <div class="col-12 col-lg-6">
            <label for="reports_sale_type_label" class="form-label"><?= lang('Reports.sale_type') ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
            <div class="input-group mb-3" id="report_sale_type">
                <span class="input-group-text"><i class="bi bi-receipt"></i></span>
                <select class="form-select" name="sale_type" id="input_type" required>
                        <?php foreach ($sale_type_options as $value => $label) { ?>
                            <option value="<?= $value ?>" <?= $value === 'complete' ? 'selected' : '' ?>><?= $label ?></option>
                        <?php } ?>
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
    $(document).ready(function() {
        <?php if (isset($discount_type_options)) { ?>
            $("#discount_type_id").change(check_discount_type).ready(check_discount_type);
        <?php } ?>

        <?= view('partial/daterangepicker') ?>

        $("#generate_report").click(function() {
            var specific_input_data = $('#specific_input_data').val();
            if ($('#discount_fixed').length && !$("#discount_percent").is(":visible")) {
                specific_input_data = $('#discount_fixed').val();
            }

            window.location = [window.location, start_date, end_date, specific_input_data, $("#input_type").val() || 0, $("#discount_type_id").val() || 0].join("/");
        });
    });

    function check_discount_type() {
        var discount_type = $("#discount_type_id").val();

        if (discount_type == 1) {
            $(".discount_percent").hide();
            $(".discount_fixed").show();
        } else {
            $(".discount_percent").show();
            $(".discount_fixed").hide();
        }
    }
</script>
