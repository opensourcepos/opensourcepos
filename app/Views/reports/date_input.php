<?php
/**
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
            <label for="daterangepicker" class="form-label"><?= lang('Reports.date_range') ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="daterangepicker-icon"><i class="bi bi-calendar2-range"></i></span>
                <input type="text" class="form-select" name="daterangepicker" id="daterangepicker" aria-describedby="daterangepicker-icon">
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <?php if (!empty($mode)) { ?>
                    <?php if ($mode == 'sale') { ?>
                        <label for="reports_sale_type_label" class="form-label"><?= lang('Reports.sale_type') ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
                        <div class="input-group mb-3" id="report_sale_type">
                            <span class="input-group-text"><i class="bi bi-receipt"></i></span>
                            <select class="form-select" name="sale_type" id="input_type" required>
                                <?php foreach ($sale_type_options as $value => $label) { ?>
                                    <option value="<?= $value ?>" <?= $value === 'complete' ? 'selected' : '' ?>><?= $label ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    <?php } elseif ($mode == 'receiving') { ?>
                        <label for="reports_receiving_type_label" class="form-label"><?= lang('Reports.receiving_type') ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
                        <div class="input-group mb-3" id="report_receiving_type">
                            <span class="input-group-text"><i class="bi bi-truck"></i></span>
                            <select class="form-select" name="receiving_type" id="input_type" required>
                                <option value="all" <?= 'all' === 'all' ? 'selected' : '' ?>><?= lang('Reports.all') ?></option>
                                <option value="receiving"><?= lang('Reports.receivings') ?></option>
                                <option value="returns"><?= lang('Reports.returns') ?></option>
                                <option value="requisitions"><?= lang('Reports.requisitions') ?></option>
                            </select>
                        </div>
                    <?php } ?>
            <?php } ?>
        </div>

        <?php if (isset($discount_type_options)) { ?>
            <div class="col-12 col-lg-6">
                <label for="reports_discount_type_label" class="form-label"><?= lang('Reports.discount_type') ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
                <div class="input-group mb-3" id="report_discount_type">
                    <span class="input-group-text"><i class="bi bi-plus-slash-minus"></i></span>
                    <select class="form-select" name="discount_type" id="discount_type_id" required>
                        <?php foreach ($discount_type_options as $value => $label) { ?>
                            <option value="<?= $value ?>" <?= $value == $config['default_sales_discount_type'] ? 'selected' : '' ?>><?= $label ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
        <?php } ?>

        <?php if (!empty($stock_locations) && count($stock_locations) > 2) { ?>
            <div class="col-12 col-lg-6">
                <label for="reports_stock_location_label" class="form-label"><?= lang('Reports.stock_location') ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
                <div class="input-group mb-3" id="report_stock_location">
                    <span class="input-group-text"><i class="bi bi-boxes"></i></span>
                    <select class="form-select" name="stock_location" id="location_id" required>
                        <?php foreach ($stock_locations as $value => $label) { ?>
                            <option value="<?= $value ?>" <?= $value === 'all' ? 'selected' : '' ?>><?= $label ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
        <?php } ?>
    </div>

    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-primary" name="generate_report" id="generate_report"><?= lang('Common.submit'); ?></button>
    </div>

<?= form_close() ?>

<?= view('partial/footer') ?>

<script type="text/javascript">
    $(document).ready(function() {
        <?= view('partial/daterangepicker') ?>

        $("#generate_report").click(function() {
            window.location = [window.location, start_date, end_date, $("#input_type").val() || 0, $("#location_id").val() || 'all', $("#discount_type_id").val() || 0].join("/");
        });
    });
</script>
