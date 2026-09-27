<?php
/**
 * @var string $controller_name
 * @var string $table_headers
 * @var array $filters
 * @var array $selected_filters
 * @var array $config
 * @var string|null $start_date
 * @var string|null $end_date
 */
?>

<?= view('partial/header') ?>

<?php
$title_info['config_title'] = 'Cashups';
echo view('configs/config_header', $title_info);
?>

<script type="text/javascript">
    $(document).ready(function() {
        // Load the preset datarange picker
        <?= view('partial/daterangepicker') ?>

        <?= view('partial/bootstrap_tables_locale') ?>

        // Override dates from server if provided
        <?php if (isset($start_date) && $start_date): ?>
        start_date = "<?= esc($start_date) ?>";
        <?php endif; ?>
        <?php if (isset($end_date) && $end_date): ?>
        end_date = "<?= esc($end_date) ?>";
        <?php endif; ?>

        table_support.init({
            resource: '<?= esc($controller_name) ?>',
            headers: <?= $table_headers ?>,
            pageSize: <?= $config['lines_per_page'] ?>,
            uniqueId: 'cashup_id',
            queryParams: function() {
                return $.extend(arguments[0], {
                    "end_date": end_date,
                    "filters": $("#filters").val(),
                    "start_date": start_date
                });
            }
        });
    });
</script>
<?= view('partial/table_filter_persistence') ?>

<?= view('partial/print_receipt', ['print_after_sale' => false, 'selected_printer' => 'takings_printer']) ?>

<div id="title_bar" class="d-flex gap-2 justify-content-end d-print-none">
    <button type="button" class="btn btn-primary modal-launch" data-btn-submit="<?= lang('Common.submit') ?>" data-href="<?= "$controller_name/view" ?>" title="<?= lang(ucfirst($controller_name) . ".new") ?>">
        <i class="bi bi-journal-check me-2"></i><?= lang(esc(ucfirst($controller_name)) . '.new')    // TODO: String Interpolation ?>
    </button>
    <button type="button" class="btn btn-primary" onclick="window.print()" title="<?= lang('Common.print') ?>">
        <i class="bi bi-printer me-2"></i><?= lang('Common.print') ?>
    </button>
</div>

<div id="toolbar">
    <div class="d-flex gap-2">
        <button type="button" id="delete" class="btn btn-secondary d-print-none">
            <i class="bi bi-trash"></i><span class="d-none d-sm-inline ms-2"><?= lang('Common.delete') ?></span>
        </button>

        <div class="input-group w-auto">
            <span class="input-group-text" id="daterangepicker-icon"><i class="bi bi-calendar2-range"></i></span>
            <input type="text" class="form-select" name="daterangepicker" id="daterangepicker" aria-describedby="daterangepicker-icon">
        </div>

        <div class="input-group w-auto">
            <span class="input-group-text" id="filters-icon"><i class="bi bi-funnel"></i></span>
            <select class="form-select" name="filters[]" id="filters" aria-describedby="filters-icon" multiple>
                <?php foreach ($filters as $key => $label): ?>
                    <option value="<?= $key ?>" <?= in_array($key, $selected_filters ?? []) ? 'selected' : '' ?>>
                        <?= esc($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>

<div id="table_holder">
    <table id="table"></table>
</div>

<?= view('partial/footer') ?>

<script type="text/javascript">
    new TomSelect('#filters', {
        plugins: ['checkbox_options', 'remove_button'],
        placeholder: '<?= lang('Common.none_selected_text') ?>',
        hidePlaceholder: true,
        closeAfterSelect: false,
        onChange: function() {
            $('#table').bootstrapTable('refresh');
        }
    });
</script>
