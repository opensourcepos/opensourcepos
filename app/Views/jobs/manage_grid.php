<?php
/**
 * @var string $table_headers
 * @var array $queues
 * @var array $config
 */

use App\Models\Employee;
?>

<script type="text/javascript">
    $(document).ready(function() {
        <?php
        echo view('partial/bootstrap_tables_locale');
        $employee = model(Employee::class);
        ?>

        let lastQueryParams = null;

        const fadeOutRow = function(uid, callback) {
            const $cells = $('tr[data-uniqueid="' + uid + '"]').find('td');

            $cells.animate({backgroundColor: '#e1ffdd'}, 600, 'linear')
                .animate({opacity: 0}, 600, 'linear');

            $cells.promise().done(callback);
        };

        // Removes rows client-side and pulls in replacement rows from what
        // would be the next page, so the current page stays full without a
        // full table reload (shift-up behavior).
        const removeRowsAndBackfill = function(uids) {
            const $table = $('#table');
            const bsTable = $table.data('bootstrap.table');

            if (!bsTable || !lastQueryParams) {
                table_support.refresh();
                return;
            }

            const visibleUidsBefore = bsTable.getData({useCurrentPage: true}).map(function(row) {
                return row.uid;
            });

            uids.forEach(function(uid) {
                bsTable.removeByUniqueId(uid);
            });

            const backfillParams = $.extend({}, lastQueryParams, {
                offset: (parseInt(lastQueryParams.offset, 10) || 0) + (parseInt(lastQueryParams.limit, 10) || 0) - uids.length,
                limit: uids.length
            });

            $.get('jobs/search', backfillParams, function(response) {
                const candidates = (response.rows || []).filter(function(row) {
                    return visibleUidsBefore.indexOf(row.uid) === -1;
                });

                if (candidates.length) {
                    bsTable.append(candidates);
                }

                bindRowActions();
            }, 'json');
        };

        const removeRowAndBackfill = function(uid) {
            removeRowsAndBackfill([uid]);
        };

        const bindRowActions = function() {
            $('.process_job').off('click').on('click', function(event) {
                event.preventDefault();

                const $link = $(this);

                if ($link.hasClass('disabled')) {
                    return false;
                }

                const uid = $link.data('uid');

                $.post('jobs/processJob', {id: uid}, function(response) {
                    $.notify(response.message, {type: response.success ? 'success' : 'danger'});

                    if (response.success) {
                        fadeOutRow(uid, function() {
                            removeRowAndBackfill(uid);
                        });
                    } else {
                        table_support.refresh();
                    }
                }, 'json');

                return false;
            });

            $('.pause_job').off('click').on('click', function(event) {
                event.preventDefault();

                const $link = $(this);
                const uid = $link.data('uid');

                $.post('jobs/pauseJob', {id: uid}, function(response) {
                    $.notify(response.message, {type: response.success ? 'success' : 'danger'});

                    if (response.success) {
                        fadeOutRow(uid, function() {
                            removeRowAndBackfill(uid);
                        });
                    } else {
                        table_support.refresh();
                    }
                }, 'json');

                return false;
            });
        };

        table_support.init({
            employee_id: <?= $employee->get_logged_in_employee_info()->person_id ?>,
            resource: 'jobs',
            headers: <?= $table_headers ?>,
            pageSize: <?= $config['lines_per_page'] ?>,
            uniqueId: 'uid',
            queryParams: function() {
                const params = $.extend(arguments[0], {
                    "queues": $("#queues").val()
                });
                lastQueryParams = params;
                return params;
            },
            onLoadSuccess: function(response) {
                bindRowActions();
            }
        });

        const pollingFrequencySeconds = <?= (int)$config['table_polling_frequency'] ?>;

        if (pollingFrequencySeconds > 0) {
            let pollInFlight = false;

            setInterval(function() {
                if (document.visibilityState !== 'visible' || pollInFlight || !lastQueryParams) {
                    return;
                }

                const $table = $('#table');
                const bsTable = $table.data('bootstrap.table');

                if (!bsTable) {
                    return;
                }

                const currentRows = bsTable.getData({useCurrentPage: true});
                const currentByUid = {};
                currentRows.forEach(function(row) {
                    currentByUid[row.uid] = row;
                });

                pollInFlight = true;

                $.get('jobs/search', lastQueryParams, function(response) {
                    const newByUid = {};
                    (response.rows || []).forEach(function(row) {
                        newByUid[row.uid] = row;
                    });

                    const removedUids = Object.keys(currentByUid).filter(function(uid) {
                        return !(uid in newByUid);
                    });

                    if (removedUids.length) {
                        const selector = removedUids.map(function(uid) {
                            return 'tr[data-uniqueid="' + uid + '"]';
                        }).join(',');
                        const $cells = $(selector).find('td');

                        $cells.animate({backgroundColor: '#d9534f'}, 600, 'linear')
                            .animate({opacity: 0}, 600, 'linear');

                        $cells.promise().done(function() {
                            removeRowsAndBackfill(removedUids);
                        });

                        return;
                    }

                    Object.keys(newByUid).forEach(function(uid) {
                        if (uid in currentByUid && JSON.stringify(currentByUid[uid]) !== JSON.stringify(newByUid[uid])) {
                            bsTable.updateByUniqueId({id: uid, row: newByUid[uid], replace: true});
                            $('tr[data-uniqueid="' + uid + '"]').find('td')
                                .animate({backgroundColor: '#e1ffdd'}, 600, 'linear')
                                .animate({backgroundColor: ''}, 1200, 'linear');
                        }
                    });

                    bindRowActions();
                }, 'json').always(function() {
                    pollInFlight = false;
                });
            }, pollingFrequencySeconds * 1000);
        }
    });
</script>

<div id="toolbar">
    <div class="pull-left form-inline" role="toolbar">
        <button id="delete" class="btn btn-default btn-sm print_hide">
            <span class="glyphicon glyphicon-trash">&nbsp;</span><?= lang('Common.delete') ?>
        </button>
        <?= form_multiselect('queues[]', array_combine($queues, $queues), [], [
            'id'                        => 'queues',
            'class'                     => 'selectpicker show-menu-arrow',
            'data-none-selected-text'   => lang('Jobs.all_queues'),
            'data-selected-text-format' => 'count > 1',
            'data-style'                => 'btn-default btn-sm',
            'data-width'                => 'fit'
        ]) ?>
    </div>
</div>

<div id="table_holder">
    <table id="table"></table>
</div>
