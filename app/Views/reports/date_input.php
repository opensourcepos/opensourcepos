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
        <div class="col-12 col-md-6 col-lg-4">
            <label for="daterangepicker" class="form-label"><?= lang('Reports.date_range'); ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-calendar2-range"></i></span>
                <input type="text" class="form-control" name="daterangepicker" id="daterangepicker">
            </div>
        </div>
    </div>

    <?php if (!empty($mode)) { ?>
        <div class="form-group form-group-sm">
            <?php if ($mode == 'sale') { ?>
                <?= form_label(lang('Reports.sale_type'), 'reports_sale_type_label', ['class' => 'required control-label col-xs-2']) ?>
                <div id="report_sale_type" class="col-xs-3">
                    <?= form_dropdown('sale_type', $sale_type_options, 'complete', ['id' => 'input_type', 'class' => 'form-control']) ?>
                </div>
            <?php } elseif ($mode == 'receiving') { ?>
                <?= form_label(lang('Reports.receiving_type'), 'reports_receiving_type_label', ['class' => 'required control-label col-xs-2']) ?>
                <div id="report_receiving_type" class="col-xs-3">
                    <?= form_dropdown(
                        'receiving_type',
                        [
                            'all'          => lang('Reports.all'),
                            'receiving'    => lang('Reports.receivings'),
                            'returns'      => lang('Reports.returns'),
                            'requisitions' => lang('Reports.requisitions')
                        ],
                        'all',
                        ['id' => 'input_type', 'class' => 'form-control']
                    ) ?>
                </div>
            <?php } ?>
        </div>
    <?php } ?>

    <?php if (isset($discount_type_options)) { ?>
        <div class="form-group form-group-sm">
            <?= form_label(lang('Reports.discount_type'), 'reports_discount_type_label', ['class' => 'required control-label col-xs-2']) ?>
            <div id="report_discount_type" class="col-xs-3">
                <?= form_dropdown('discount_type', $discount_type_options, $config['default_sales_discount_type'], ['id' => 'discount_type_id', 'class' => 'form-control']) ?>
            </div>
        </div>
    <?php } ?>

    <?php if (!empty($stock_locations) && count($stock_locations) > 2) { ?>
        <div class="form-group form-group-sm">
            <?= form_label(lang('Reports.stock_location'), 'reports_stock_location_label', ['class' => 'required control-label col-xs-2']) ?>
            <div id="report_stock_location" class="col-xs-3">
                <?= form_dropdown('stock_location', $stock_locations, 'all', ['id' => 'location_id', 'class' => 'form-control']) ?>
            </div>
        </div>
    <?php } ?>

    <?php
    echo form_button(
        [
            'name'    => 'generate_report',
            'id'      => 'generate_report',
            'content' => lang('Common.submit'),
            'class'   => 'btn btn-primary'
        ]
    );
    ?>

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
