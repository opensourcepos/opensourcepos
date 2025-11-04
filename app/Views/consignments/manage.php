<?php
/**
 * @var string $table_headers
 * @var array $filters
 * @var array $selected_filters
 * @var array $config
 * @var string $controller_name
 */
?>

<?= view('partial/header') ?>

<script type="text/javascript">
    $(document).ready(function() {
        $('#filters').on('hidden.bs.select', function() {
            table_support.refresh();
        });

        <?= view('partial/daterangepicker') ?>

        $("#daterangepicker").on('apply.daterangepicker', function() {
            table_support.refresh();
        });

        <?= view('partial/bootstrap_tables_locale') ?>

        table_support.init({
            resource: '<?= esc($controller_name) ?>',
            headers: <?= $table_headers ?>,
            pageSize: <?= $config['lines_per_page'] ?>,
            uniqueId: 'consignment_id',
            queryParams: function() {
                return $.extend(arguments[0], {
                    "start_date": start_date,
                    "end_date": end_date,
                    "filters": $("#filters").val()
                });
            }
        });

        $('#mark_paid').click(function() {
            const ids = table_support.selected_ids();
            if (!ids.length) {
                return false;
            }

            if (confirm("<?= lang('Consignments.confirm_mark_paid') ?>")) {
                $.post('<?= esc($controller_name) ?>/markPaid', {'ids': ids}, function(response) {
                    $.notify(response.message, {type: response.success ? 'success' : 'danger'});
                    if (response.success) {
                        table_support.refresh();
                    }
                }, 'json');
            }
        });
    });
</script>

<div id="title_bar" class="btn-toolbar print_hide">
    <span class="title_bar_text"><?= lang('Consignments.manage_title') ?></span>
</div>

<div id="toolbar">
    <div class="pull-left form-inline" role="toolbar">
        <button id="mark_paid" class="btn btn-success btn-sm">
            <span class="glyphicon glyphicon-ok">&nbsp;</span><?= lang('Consignments.mark_paid') ?>
        </button>
        <?= form_input(['name' => 'daterangepicker', 'class' => 'form-control input-sm', 'id' => 'daterangepicker']) ?>
        <?= form_multiselect('filters[]', $filters, $selected_filters, [
            'id'                        => 'filters',
            'data-none-selected-text'   => lang('Common.none_selected_text'),
            'class'                     => 'selectpicker show-menu-arrow',
            'data-selected-text-format' => 'count > 1',
            'data-style'                => 'btn-default btn-sm',
            'data-width'                => 'fit'
        ]) ?>
    </div>
</div>

<div id="table_holder">
    <table id="table"></table>
</div>

<?= view('partial/footer') ?>
