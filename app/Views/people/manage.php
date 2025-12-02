<?php
/**
 * @var string $controller_name
 * @var string $table_headers
 * @var array $config
 */
?>

<?= view('partial/header') ?>

<?php
$title_info['config_title'] = 'People';
echo view('configs/config_header', $title_info);
?>

<script type="text/javascript">
    $(document).ready(function() {
        <?= view('partial/bootstrap_tables_locale') ?>

        table_support.init({
            resource: '<?= esc($controller_name) ?>',
            headers: <?= $table_headers ?>,
            pageSize: <?= $config['lines_per_page'] ?>,
            uniqueId: 'people.person_id',
            enableActions: function() {
                var email_disabled = $("td input:checkbox:checked").parents("tr").find("td a[href^='mailto:']").length == 0;
                $("#email").prop('disabled', email_disabled);
            }
        });

        $("#email").click(function(event) {
            var recipients = $.map($("tr.selected a[href^='mailto:']"), function(element) {
                return $(element).attr('href').replace(/^mailto:/, '');
            });
            location.href = "mailto:" + recipients.join(",");
        });
    });
</script>

<div class="d-flex gap-2 justify-content-end">
    <button type="button" class="btn btn-primary" data-btn-submit="<?= lang('Common.submit') ?>" data-href="<?= '$controller_name/view' ?>" title="<?= lang(ucfirst($controller_name). '.new') ?>">
        <i class="bi bi-person-add me-2"></i><?= lang(ucfirst($controller_name) .".new") ?>
    </button>
    <?php if ($controller_name == 'customers') { ?>
    <button type="button" class="btn btn-primary" data-btn-submit="<?= lang('Common.submit') ?>" data-href="<?= '$controller_name/csvImport' ?>" title="<?= lang(ucfirst($controller_name) .'.import_items_csv') ?>">
        <i class="bi bi-file-earmark-arrow-down me-2"></i><?= lang('Common.import_csv') ?>
    </button>
    <?php } ?>
</div>

<div id="toolbar">
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-secondary">
            <i class="bi bi-trash"></i><span class="d-none d-sm-inline ms-2"><?= lang('Common.delete') ?></span>
        </button>
        <button type="button" class="btn btn-secondary">
            <i class="bi bi-envelope"></i><span class="d-none d-sm-inline ms-2"><?= lang('Common.email') ?></span>
        </button>
    </div>
</div>

<div id="table_holder">
    <table id="table"></table>
</div>

<?= view('partial/footer') ?>
