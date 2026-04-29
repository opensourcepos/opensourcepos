<?= view('partial/header') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <span class="glyphicon glyphicon-puzzle"></span> <?= lang('Plugins.management') ?>
                    </h3>
                </div>
                <div class="panel-body">
                    <?php if (empty($plugins)): ?>
                        <div class="alert alert-info">
                            <?= lang('Plugins.no_plugins_found') ?>
                        </div>
                    <?php else: ?>
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th><?= lang('Plugins.name') ?></th>
                                    <th><?= lang('Plugins.description') ?></th>
                                    <th><?= lang('Plugins.version') ?></th>
                                    <th><?= lang('Plugins.status') ?></th>
                                    <th><?= lang('Plugins.actions') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($plugins as $pluginId => $plugin): ?>
                                    <tr id="plugin-row-<?= esc($pluginId) ?>">
                                        <td>
                                            <strong><?= esc($plugin['name']) ?></strong>
                                            <br><small class="text-muted"><?= esc($plugin['id']) ?></small>
                                        </td>
                                        <td><?= esc($plugin['description']) ?></td>
                                        <td><span class="label label-default"><?= esc($plugin['version']) ?></span></td>
                                        <td>
                                            <?php if ($plugin['enabled']): ?>
                                                <span class="label label-success"><?= lang('Plugins.active') ?></span>
                                            <?php else: ?>
                                                <span class="label label-default"><?= lang('Plugins.inactive') ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($plugin['enabled']): ?>
                                                <button class="btn btn-warning btn-xs plugin-action"
                                                        data-action="disable"
                                                        data-plugin-id="<?= esc($pluginId) ?>">
                                                    <span class="glyphicon glyphicon-pause"></span> <?= lang('Plugins.disable') ?>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-success btn-xs plugin-action"
                                                        data-action="enable"
                                                        data-plugin-id="<?= esc($pluginId) ?>">
                                                    <span class="glyphicon glyphicon-play"></span> <?= lang('Plugins.enable') ?>
                                                </button>
                                            <?php endif; ?>

                                            <?php if ($plugin['has_config'] && $plugin['enabled']): ?>
                                                <button class="btn btn-primary btn-xs plugin-config"
                                                        data-plugin-id="<?= esc($pluginId) ?>">
                                                    <span class="glyphicon glyphicon-cog"></span> <?= lang('Plugins.configure') ?>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Plugin Config Modal -->
<div class="modal fade" id="plugin-config-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?= lang('Plugins.plugins') ?></h4>
            </div>
            <div class="modal-body" id="plugin-config-content">
                <!-- Config form loaded dynamically -->
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    $('.plugin-action').on('click', function() {
        var btn = $(this);
        var action = btn.data('action');
        var pluginId = btn.data('plugin-id');

        $.post('plugins/' + action + '/' + pluginId, {
            <?= esc(csrf_token()) ?>: '<?= esc(csrf_hash()) ?>'
        }, function(response) {
            if (response.success) {
                $.notify({ message: response.message }, { type: 'success' });
                setTimeout(function() { location.reload(); }, 1000);
            } else {
                $.notify({ message: response.message }, { type: 'danger' });
            }
        }, 'json');
    });

    $('.plugin-config').on('click', function() {
        var pluginId = $(this).data('plugin-id');
        $('#plugin-config-content').load('plugins/config/' + pluginId);
        $('#plugin-config-modal').modal('show');
    });
});
</script>

<?= view('partial/footer') ?>
