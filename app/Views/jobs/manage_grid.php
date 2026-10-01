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

        table_support.init({
            employee_id: <?= $employee->get_logged_in_employee_info()->person_id ?>,
            resource: 'jobs',
            headers: <?= $table_headers ?>,
            pageSize: <?= $config['lines_per_page'] ?>,
            uniqueId: 'uid',
            queryParams: function() {
                return $.extend(arguments[0], {
                    "queues": $("#queues").val()
                });
            },
            onLoadSuccess: function(response) {
                $('.process_job').off('click').on('click', function(event) {
                    event.preventDefault();

                    const $link = $(this);

                    if ($link.hasClass('disabled')) {
                        return false;
                    }

                    $.post('jobs/processJob', {id: $link.data('uid')}, function(response) {
                        $.notify(response.message, {type: response.success ? 'success' : 'danger'});
                        table_support.refresh();
                    }, 'json');

                    return false;
                });
            }
        });
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
