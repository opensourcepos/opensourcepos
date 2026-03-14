<a href="javascript:void(0);" class="btn btn-info btn-sm" id="show_print_caspos_button" onclick="printCasposReceipt(<?= $sale['sale_id'] ?? '' ?>)">
    <span class="glyphicon glyphicon-print">&nbsp;</span>
    <?= esc(lang('Plugins.caspos_print_receipt') ?? 'Print Fiscal Receipt') ?>
</a>