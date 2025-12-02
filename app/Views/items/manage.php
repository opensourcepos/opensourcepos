<?php
/**
 * @var string $controller_name
 * @var string $table_headers
 * @var array $filters
 * @var array $stock_locations
 * @var int $stock_location
 * @var array $config
 */

use App\Models\Employee;
?>

<?= view('partial/header') ?>

<?php
$title_info['config_title'] = 'Items';
echo view('configs/config_header', $title_info);
?>

<script type="text/javascript">
    $(document).ready(function() {
        $('#generate_barcodes').click(function() {
            window.open(
                'index.php/items/generateBarcodes/' + table_support.selected_ids().join(':'),
                '_blank'
            );
        });

        // When any filter is clicked and the dropdown window is closed
        $('#filters').on('hidden.bs.select', function(e) {
            table_support.refresh();
        });

        // Load the preset daterange picker
        <?= view('partial/daterangepicker') ?>
        // Set the beginning of time as starting date
        $('#daterangepicker').data('daterangepicker').setStartDate("<?= date($config['dateformat'], mktime(0, 0, 0, 01, 01, 2010)) ?>");
        // Update the hidden inputs with the selected dates before submitting the search data
        var start_date = "<?= date('Y-m-d', mktime(0, 0, 0, 01, 01, 2010)) ?>";
        $("#daterangepicker").on('apply.daterangepicker', function(ev, picker) {
            table_support.refresh();
        });

        $("#stock_location").change(function() {
            table_support.refresh();
        });

        <?php
        echo view('partial/bootstrap_tables_locale');
        $employee = model(Employee::class);
        ?>

        table_support.init({
            employee_id: <?= $employee->get_logged_in_employee_info()->person_id ?>,
            resource: '<?= esc($controller_name) ?>',
            headers: <?= $table_headers ?>,
            pageSize: <?= $config['lines_per_page'] ?>,
            uniqueId: 'items.item_id',
            queryParams: function() {
                return $.extend(arguments[0], {
                    "start_date": start_date,
                    "end_date": end_date,
                    "stock_location": $("#stock_location").val(),
                    "filters": $("#filters").val()
                });
            },
            onLoadSuccess: function(response) {
                $('a.rollover').imgPreview({
                    imgCSS: {
                        width: 200
                    },
                    distanceFromCursor: {
                        top: 10,
                        left: -210
                    }
                })
            }
        });
    });
</script>

<div class="d-flex gap-2 justify-content-end">
    <button type="button" class="btn btn-primary" data-btn-new="<?= lang('Common.new') ?>" data-btn-submit="<?= lang('Common.submit') ?>" data-href="<?= '$controller_name/view' ?>" title="<?= lang(ucfirst($controller_name) .".new") ?>">
        <i class="bi bi-tag me-2"></i><?= lang(ucfirst($controller_name) .".new") ?>
    </button>
    <button type="button" class="btn btn-primary" data-btn-submit="<?= lang('Common.submit') ?>" data-href="<?= '$controller_name/csvImport' ?>" title="<?= lang('Items.import_items_csv') ?>">
        <i class="bi bi-file-earmark-arrow-down me-2"></i><?= lang('Common.import_csv') ?>
    </button>
</div>

<div id="toolbar">
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-secondary d-print-none">
            <i class="bi bi-trash"></i><span class="d-none d-md-inline ms-2"><?= lang('Common.delete') ?></span>
        </button>
        <button type="button" class="btn btn-secondary d-print-none" data-btn-submit="<?= lang('Common.submit') ?>" data-href="<?= 'items/bulkEdit' ?>" title="<?= lang('Items.edit_multiple_items') ?>">
            <i class="bi bi-pencil-square"></i><span class="d-none d-md-inline ms-2"><?= lang('Items.bulk_edit') ?></span>
        </button>
        <button type="button" class="btn btn-secondary d-print-none" data-href="<?= '$controller_name/generateBarcodes' ?>" title="<?= lang('Items.generate_barcodes') ?>">
            <i class="bi bi-upc-scan"></i><span class="d-none d-md-inline ms-2"><?= lang('Items.generate_barcodes') ?></span>
        </button>
        <input type="text" class="form-control" name="daterangepicker" id="daterangepicker">
        <select id="filters" name="filters[]" class="selectpicker show-menu-arrow" data-none-selected-text="<?= lang('Common.none_selected_text') ?>" data-selected-text-format="count > 1" data-style="btn-secondary" data-width="fit" multiple>
            <?php foreach ($filters as $key => $value): ?>
                <option value="<?= esc($key) ?>"><?= esc($value) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (count($stock_locations) > 1): ?>
            <select id="stock_location" name="stock_location" class="selectpicker show-menu-arrow" data-style="btn-secondary" data-width="fit">
                <?php foreach ($stock_locations as $key => $value): ?>
                    <option value="<?= esc($key) ?>" <?= $key == $stock_location ? 'selected' : '' ?>><?= esc($value) ?></option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>
    </div>
</div>

<div id="table_holder">
    <table id="table"></table>
</div>

<?= view('partial/footer') ?>
