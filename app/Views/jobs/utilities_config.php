<?php
/**
 * @var array $config
 * @var array $queues
 */
?>

<div id="config_wrapper" class="form-horizontal">
    <fieldset id="utilities_info">

        <div class="row form-group form-group-sm">
            <label class="control-label col-xs-4 col-sm-3 col-md-2"><?= lang('Jobs.process_all_jobs') ?></label>
            <div class="col-xs-2"></div>
            <div class="col-xs-4 text-left">
                <button type="button" id="process_all_jobs" class="btn btn-default btn-sm">
                    <span class="glyphicon glyphicon-play">&nbsp;</span><?= lang('Jobs.process_all_jobs') ?>
                </button>
            </div>
        </div>

        <div class="row form-group form-group-sm">
            <label class="control-label col-xs-4 col-sm-3 col-md-2"><?= lang('Jobs.process_selected_jobs') ?></label>
            <div class="col-xs-2 text-left">
                <?= form_multiselect('selected_jobs[]', array_combine($queues, $queues), [], [
                    'id'                        => 'selected_jobs',
                    'class'                     => 'selectpicker show-menu-arrow',
                    'data-none-selected-text'   => lang('Common.none_selected_text'),
                    'data-selected-text-format' => 'count > 1',
                    'data-style'                => 'btn-default btn-sm',
                    'data-width'                => 'fit'
                ]) ?>
            </div>
            <div class="col-xs-4 text-left">
                <button type="button" id="process_selected_jobs" class="btn btn-default btn-sm">
                    <span class="glyphicon glyphicon-play">&nbsp;</span><?= lang('Jobs.process_selected_jobs') ?>
                </button>
            </div>
        </div>

    </fieldset>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#process_all_jobs').click(function() {
            $.post('<?= 'jobs/processJobs' ?>', {scope: 'all'}, function(response) {
                $.notify({
                    message: response.message
                }, {
                    type: response.success ? 'success' : 'warning'
                });
            }, 'json');
        });

        $('#process_selected_jobs').click(function() {
            $.post('<?= 'jobs/processJobs' ?>', {scope: 'selected', selected_jobs: $('#selected_jobs').val()}, function(response) {
                $.notify({
                    message: response.message
                }, {
                    type: response.success ? 'success' : 'warning'
                });
            }, 'json');
        });
    });
</script>
