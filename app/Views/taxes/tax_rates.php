<?php
/**
 * @var string $controller_name
 * @var string $tax_rate_table_headers
 * @var array $config
 */
?>

<script type="text/javascript">
    $(document).ready(function() {
        <?= view('partial/bootstrap_tables_locale') ?>
        table_support.init({
            resource: '<?= esc($controller_name) ?>',
            headers: <?= $tax_rate_table_headers ?>,
            pageSize: <?= $config['lines_per_page'] ?>,
            uniqueId: 'tax_rate_id'
        });
    });
</script>

<div class="d-flex gap-2 justify-content-end">
    <button type="button" class="btn btn-primary" data-btn-submit="<?= lang('Common.submit') ?>" data-href="<?= esc("$controller_name/view") ?>" title="<?= lang(ucfirst($controller_name). '.new') ?>">
        <i class="bi bi-piggy-bank me-2"></i><?= lang(ucfirst($controller_name) .'.new') ?>
    </button>
</div>

<div id="toolbar">
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-secondary">
            <i class="bi bi-trash"></i><span class="d-none d-sm-inline ms-2"><?= lang('Common.delete') ?></span>
        </button>
    </div>
</div>

<div id="table_holder">
    <table id="table"></table>
</div>
