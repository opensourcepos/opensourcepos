<?php
/**
 * @var string $controller_name
 * @var string $table_headers
 * @var array $filters
 * @var array $selected_filters
 * @var array $config
 */
?>
<?= view('partial/header') ?>

<script type="application/javascript">
$(document).ready(function()
{
    // when any filter is clicked and the dropdown window is closed
    $('#filters').on('hidden.bs.select', function(e) {
        table_support.refresh();
    });

    // load the preset datarange picker
    <?= view('partial/daterangepicker') ?>

    $("#daterangepicker").on('apply.daterangepicker', function(ev, picker) {
        table_support.refresh();
    });

    <?= view('partial/bootstrap_tables_locale') ?>

    table_support.query_params = function()
    {
        return {
            "start_date": start_date,
            "end_date": end_date,
            "filters": $("#filters").val()
        }
    };

    table_support.init({
        resource: '<?= esc($controller_name) ?>',
        headers: <?= $table_headers ?>,
        pageSize: <?= $config['lines_per_page'] ?>,
        uniqueId: 'sale_id',
        onLoadSuccess: function(response) {
            if($("#table tbody tr").length > 1) {
                $("#payment_summary").html(response.payment_summary);
                $("#table tbody tr:last td:first").html("");
                $("#table tbody tr:last").css('font-weight', 'bold');
            }
        },
        queryParams: function() {
            return $.extend(arguments[0], table_support.query_params());
        },
        columns: {
            'invoice': {
                align: 'center'
            }
        }
    });
});
</script>

<?= view('partial/print_receipt', ['print_after_sale'=>false, 'selected_printer' => 'takings_printer']) ?>

<div id="title_bar" class="print_hide btn-toolbar">
    <button onclick="javascript:printdoc()" class='btn btn-info btn-sm pull-right'>
        <span class="glyphicon glyphicon-print">&nbsp</span><?= lang('Common.print') ?>
    </button>
    <?= anchor("sales", '<span class="glyphicon glyphicon-shopping-cart">&nbsp</span>' . lang('Sales.register'), ['class' => 'btn btn-info btn-sm pull-right', 'id' => 'show_sales_button']) ?>
</div>

<div id="toolbar">
    <div class="pull-left form-inline" role="toolbar">
        <button id="delete" class="btn btn-default btn-sm print_hide">
            <span class="glyphicon glyphicon-trash">&nbsp</span><?= lang('Common.delete') ?>
        </button>

        <?= form_input (['name' => 'daterangepicker', 'class' => 'form-control input-sm', 'id' => 'daterangepicker']) ?>
        <?= form_multiselect('filters[]', $filters, $selected_filters, ['id' => 'filters', 'data-none-selected-text'=>lang('Common.none_selected_text'), 'class' => 'selectpicker show-menu-arrow', 'data-selected-text-format' => 'count > 1', 'data-style' => 'btn-default btn-sm', 'data-width' => 'fit']) ?>
    </div>
</div>

<div id="table_holder">
    <table id="table"></table>
</div>

<div id="payment_summary">
</div>

<?= view('partial/footer') ?>
