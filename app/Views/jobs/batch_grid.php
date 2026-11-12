<?php
/**
 * @var string $batch_table_headers
 * @var array $config
 */

use App\Models\Employee;
?>

<script type="text/javascript">
    $(document).ready(function() {
        <?php
        $employee = model(Employee::class);
        ?>

        const bindBatchActions = function() {
            $('.batch_drill_down').off('click').on('click', function(event) {
                event.preventDefault();
                $('a[href="#manage_tab"]').tab('show');
                return false;
            });

            $('.batch_pause').off('click').on('click', function(event) {
                event.preventDefault();

                const batchId = $(this).data('batch-id');

                $.post('jobs/pauseBatch', {id: batchId}, function(response) {
                    $.notify(response.message, {type: response.success ? 'success' : 'danger'});

                    if (response.success) {
                        $('#batch_table').bootstrapTable('refresh');
                    }
                }, 'json');

                return false;
            });

            $('.batch_resume').off('click').on('click', function(event) {
                event.preventDefault();

                const batchId = $(this).data('batch-id');

                $.post('jobs/resumeBatch', {id: batchId}, function(response) {
                    $.notify(response.message, {type: response.success ? 'success' : 'danger'});

                    if (response.success) {
                        $('#batch_table').bootstrapTable('refresh');
                    }
                }, 'json');

                return false;
            });

            $('.batch_delete').off('click').on('click', function(event) {
                event.preventDefault();

                if (!confirm('<?= lang('Jobs.batch_delete_confirm') ?>')) {
                    return false;
                }

                const batchId = $(this).data('batch-id');

                $.post('jobs/deleteBatch', {id: batchId}, function(response) {
                    $.notify(response.message, {type: response.success ? 'success' : 'danger'});

                    if (response.success) {
                        $('#batch_table').bootstrapTable('refresh');
                    }
                }, 'json');

                return false;
            });

            $('.batch_purge').off('click').on('click', function(event) {
                event.preventDefault();

                if (!confirm('<?= lang('Jobs.batch_purge_confirm') ?>')) {
                    return false;
                }

                const batchId = $(this).data('batch-id');

                $.post('jobs/purgeBatch', {id: batchId}, function(response) {
                    $.notify(response.message, {type: response.success ? 'success' : 'danger'});

                    if (response.success) {
                        $('#batch_table').bootstrapTable('refresh');
                    }
                }, 'json');

                return false;
            });
        };

        const exportSuffix = new Date().toISOString().slice(0, 16).replace(/(-|\s*|T|:)*/g, '');

        $('#batch_table')
            .addClass('table-striped')
            .addClass('table-bordered')
            .bootstrapTable({
                columns: <?= $batch_table_headers ?>,
                stickyHeader: true,
                url: 'jobs/searchBatches',
                sidePagination: 'server',
                pageSize: <?= $config['lines_per_page'] ?>,
                pagination: true,
                search: true,
                showColumns: true,
                showExport: true,
                exportDataType: 'basic',
                exportTypes: ['json', 'xml', 'csv', 'txt', 'sql', 'excel', 'pdf'],
                exportOptions: {fileName: 'batches_' + exportSuffix},
                toolbar: '#batch_toolbar',
                uniqueId: 'uid',
                trimOnSearch: false,
                queryParamsType: 'limit',
                queryParams: function(params) {
                    return $.extend(params, {
                        statuses: $('#batch_statuses').val()
                    });
                },
                iconSize: 'sm',
                silentSort: true,
                paginationVAlign: 'bottom',
                escape: true,
                onLoadSuccess: function() {
                    bindBatchActions();
                },
                loadingTemplate: function(loadingMessage) {
                    return '<div class="loading-message text-center"><span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span><span role="status">' + loadingMessage + '</span></div>';
                },
                loadingFontSize: '1em'
            });

        $('#batch_statuses').on('hidden.bs.select', function() {
            $('#batch_table').bootstrapTable('refresh');
        });
    });
</script>

<div id="batch_toolbar">
    <div class="pull-left form-inline" role="toolbar">
        <?php $batchStatusOptions = [
            'pending'    => lang('Jobs.batch_status_pending'),
            'processing' => lang('Jobs.batch_status_processing'),
            'completed'  => lang('Jobs.batch_status_completed'),
            'partial'    => lang('Jobs.batch_status_partial'),
        ] ?>
        <?= form_multiselect('batch_statuses[]', $batchStatusOptions, [], [
            'id'                        => 'batch_statuses',
            'class'                     => 'selectpicker show-menu-arrow',
            'data-none-selected-text'   => lang('Jobs.all_statuses'),
            'data-selected-text-format' => 'count > 1',
            'data-style'                => 'btn-default btn-sm',
            'data-width'                => 'fit'
        ]) ?>
    </div>
</div>

<div id="batch_table_holder">
    <table id="batch_table"></table>
</div>
