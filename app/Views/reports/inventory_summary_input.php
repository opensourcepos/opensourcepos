<?php
/**
 * @var array $stock_locations
 * @var array $item_count
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

        <div class="col-12 col-lg-6">
            <label for="reports_item_count_label" class="form-label"><?= lang('Reports.item_count') ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
            <div class="input-group mb-3" id="report_item_count">
                <span class="input-group-text"><i class="bi bi-funnel"></i></span>
                <select class="form-select" name="item_count" id="item_count" required>
                    <?php foreach ($item_count as $value => $label) { ?>
                        <option value="<?= $value ?>" <?= $value === 'all' ? 'selected' : '' ?>><?= $label ?></option>
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
        $("#generate_report").click(function() {
            window.location = [window.location, $("#location_id").val(), $("#item_count").val()].join("/");
        });
    });
</script>
