<?php
/**
 * @var string $table_headers
 * @var array $config
 * @var string $controller_name
 */
?>

<?= view('partial/header') ?>

<script type="text/javascript">
    $(document).ready(function() {
        <?= view('partial/bootstrap_tables_locale') ?>

        table_support.init({
            resource: '<?= esc($controller_name) ?>',
            headers: <?= $table_headers ?>,
            pageSize: <?= $config['lines_per_page'] ?>,
            uniqueId: 'plugin_id'
        });

        $('#table').on('click', '.plugin-action', function() {
            var action = $(this).data('action');
            var pluginId = $(this).data('plugin-id');

            if (action === 'uninstall') {
                $('#uninstall-confirm-modal').data('plugin-id', pluginId).modal('show');
                return;
            }

            $.ajax({
                url: '<?= site_url('plugins') ?>/' + action + '/' + pluginId,
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    $.notify(response.message, { type: response.success ? 'success' : 'danger' });
                    if (response.success) {
                        $.get('<?= site_url('plugins/row') ?>/' + pluginId, {}, function(rowData) {
                            $('#table').bootstrapTable('updateByUniqueId', { id: pluginId, row: rowData });
                        }, 'json');
                    }
                }
            });
        });

        $('#uninstall-confirm-btn').on('click', function() {
            var pluginId = $('#uninstall-confirm-modal').data('plugin-id');
            $('#uninstall-confirm-modal').modal('hide');
            $.ajax({
                url: '<?= site_url('plugins') ?>/uninstall/' + pluginId,
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    $.notify(response.message, { type: response.success ? 'success' : 'danger' });
                    if (response.success) {
                        $.get('<?= site_url('plugins/row') ?>/' + pluginId, {}, function(rowData) {
                            $('#table').bootstrapTable('updateByUniqueId', { id: pluginId, row: rowData });
                        }, 'json');
                    }
                }
            });
        });

        $('#table').on('click', '.plugin-config', function() {
            var pluginId = $(this).data('plugin-id');
            $('#plugin-config-content').load('<?= site_url('plugins/config') ?>/' + pluginId);
            $('#plugin-config-modal').modal('show');
        });
    });
</script>

<div id="toolbar"></div>

<div id="table_holder">
    <table id="table"></table>
</div>

<!-- Plugin Config Modal -->
<div class="modal fade" id="plugin-config-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title text-center"><?= lang('Plugins.plugins') ?></h4>
            </div>
            <div class="modal-body" id="plugin-config-content"></div>
        </div>
    </div>
</div>

<!-- Uninstall Confirmation Modal -->
<div class="modal fade" id="uninstall-confirm-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title text-center"><?= lang('Plugins.uninstall') ?></h4>
            </div>
            <div class="modal-body">
                <p><?= lang('Plugins.uninstall_warning') ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="uninstall-confirm-btn"><?= lang('Plugins.uninstall') ?></button>
            </div>
        </div>
    </div>
</div>

<?= view('partial/footer') ?>
