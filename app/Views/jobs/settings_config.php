<?php
/**
 * @var array $config
 * @var array $throttles
 */
?>

<?= form_open('jobs/saveSettings/', ['id' => 'jobs_settings_form', 'class' => 'form-horizontal']) ?>
    <div id="config_wrapper">
        <fieldset id="config_info">

            <div id="required_fields_message"><?= lang('Common.fields_required_message') ?></div>
            <ul id="jobs_settings_error_message_box" class="error_message_box"></ul>

            <div class="form-group form-group-sm">
                <?= form_label(lang('Jobs.mode'), 'mode', ['class' => 'required control-label col-xs-4 col-sm-3 col-md-2']) ?>
                <div class="col-xs-4 col-sm-3 col-md-2">
                    <?= form_dropdown(
                        'mode',
                        [
                            'auto'   => lang('Jobs.mode_auto'),
                            'web'    => lang('Jobs.mode_web'),
                            'manual' => lang('Jobs.mode_manual')
                        ],
                        $config['jobs_mode'] ?? 'web',
                        'id="mode" class="form-control input-sm required"'
                    ) ?>
                </div>
            </div>

            <div class="form-group form-group-sm">
                <?= form_label(lang('Jobs.web_max_seconds'), 'web_max_seconds', ['class' => 'required control-label col-xs-4 col-sm-3 col-md-2']) ?>
                <div class="col-xs-4 col-sm-3 col-md-2">
                    <div class="input-group">
                        <?= form_input(array_merge([
                            'type'           => 'number',
                            'min'            => 0,
                            'name'           => 'web_max_seconds',
                            'id'             => 'web_max_seconds',
                            'class'          => 'form-control input-sm required digits',
                            'value'          => $config['jobs_web_max_seconds'] ?? 5
                        ], ($config['jobs_mode'] ?? 'web') !== 'web' ? ['disabled' => true] : [])) ?>
                        <span class="input-group-addon input-sm">
                            <span
                                id="web_max_seconds_tooltip"
                                class="glyphicon glyphicon-info-sign"
                                data-toggle="tooltip"
                                data-placement="right"
                                data-tooltip-enabled="<?= esc(lang('Jobs.web_max_seconds_tooltip')) ?>"
                                data-tooltip-disabled="<?= esc(lang('Jobs.web_max_seconds_tooltip_disabled')) ?>"
                                title="<?= lang('Jobs.web_max_seconds_tooltip') ?>"
                            ></span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="form-group form-group-sm">
                <?= form_label(lang('Jobs.task_max_seconds'), 'task_max_seconds', ['class' => 'required control-label col-xs-4 col-sm-3 col-md-2']) ?>
                <div class="col-xs-4 col-sm-3 col-md-2">
                    <div class="input-group">
                        <?= form_input(array_merge([
                            'type'           => 'number',
                            'min'            => 0,
                            'name'           => 'task_max_seconds',
                            'id'             => 'task_max_seconds',
                            'class'          => 'form-control input-sm required digits',
                            'value'          => $config['jobs_task_max_seconds'] ?? 30
                        ], ($config['jobs_mode'] ?? 'web') !== 'web' ? ['disabled' => true] : [])) ?>
                        <span class="input-group-addon input-sm">
                            <span
                                id="task_max_seconds_tooltip"
                                class="glyphicon glyphicon-info-sign"
                                data-toggle="tooltip"
                                data-placement="right"
                                data-tooltip-enabled="<?= esc(lang('Jobs.task_max_seconds_tooltip')) ?>"
                                data-tooltip-disabled="<?= esc(lang('Jobs.task_max_seconds_tooltip_disabled')) ?>"
                                title="<?= lang('Jobs.task_max_seconds_tooltip') ?>"
                            ></span>
                        </span>
                    </div>
                </div>
            </div>

            <?= form_submit([
                'name'  => 'submit_jobs_settings',
                'id'    => 'submit_jobs_settings',
                'value' => lang('Common.submit'),
                'class' => 'btn btn-primary btn-sm pull-right'
            ]) ?>

        </fieldset>
    </div>
<?= form_close() ?>

<hr>

<div id="config_wrapper">
    <fieldset id="throttle_info">
        <legend><?= lang('Jobs.throttles') ?></legend>

        <?= form_open('jobs/saveThrottles/', ['id' => 'jobs_throttles_form', 'class' => 'form-horizontal']) ?>
            <ul id="jobs_throttles_error_message_box" class="error_message_box"></ul>

            <div id="throttles_wrapper">
                <?= view('partial/job_throttles', ['throttles' => $throttles]) ?>
            </div>

            <?= form_submit([
                'name'  => 'submit_jobs_throttles',
                'id'    => 'submit_jobs_throttles',
                'value' => lang('Common.submit'),
                'class' => 'btn btn-primary btn-sm pull-right'
            ]) ?>
        <?= form_close() ?>
    </fieldset>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip({ container: 'body' });

        const toggleWebMaxSeconds = function() {
            const isWeb = $('#mode').val() === 'web';
            $('#web_max_seconds').prop('disabled', !isWeb);
            $('#task_max_seconds').prop('disabled', !isWeb);

            const $tooltip = $('#web_max_seconds_tooltip');
            const text = isWeb ? $tooltip.attr('data-tooltip-enabled') : $tooltip.attr('data-tooltip-disabled');
            $tooltip.attr('data-original-title', text);

            const $taskTooltip = $('#task_max_seconds_tooltip');
            const taskText = isWeb ? $taskTooltip.attr('data-tooltip-enabled') : $taskTooltip.attr('data-tooltip-disabled');
            $taskTooltip.attr('data-original-title', taskText);
        };
        $('#mode').change(toggleWebMaxSeconds);
        toggleWebMaxSeconds();

        $('#jobs_settings_form').validate($.extend(form_support.handler, {
            submitHandler: function(form) {
                $('#web_max_seconds').prop('disabled', false);
                $('#task_max_seconds').prop('disabled', false);
                $(form).ajaxSubmit({
                    success: function(response) {
                        $.notify({
                            message: response.message
                        }, {
                            type: response.success ? 'success' : 'danger'
                        });
                        toggleWebMaxSeconds();
                    },
                    dataType: 'json'
                });
            },

            errorLabelContainer: '#jobs_settings_error_message_box'
        }));

        const hideShowRemoveThrottle = function() {
            if ($('input[name*="throttle_count_"]:enabled').length > 1) {
                $('.remove_throttle').show();
            } else {
                $('.remove_throttle').hide();
            }
        };

        const addThrottle = function() {
            const row = $(this).closest('.form-group');
            let id = row.find('input').attr('id');
            id = id.replace(/.*?_(\d+)$/g, '$1');
            const previousId = 'throttle_count_' + id;
            const previousPeriodId = 'throttle_period_' + id;
            const block = row.clone(true);
            const newBlock = block.insertAfter(row);
            let maxId = 0;
            $('#throttles_wrapper input[id^="throttle_count_"]').each(function() {
                const existingId = parseInt($(this).attr('id').replace(/.*?_(\d+)$/g, '$1'), 10);
                if (existingId > maxId) {
                    maxId = existingId;
                }
            });
            const newId = maxId + 1;
            const newBlockId = 'throttle_count_' + newId;
            const newBlockPeriodId = 'throttle_period_' + newId;
            $(newBlock).find('input[id="' + previousId + '"]').attr('id', newBlockId).removeAttr('disabled').attr('name', newBlockId).attr('class', 'form-control input-sm required digits').val('0');
            $(newBlock).find('select[id="' + previousPeriodId + '"]').attr('id', newBlockPeriodId).removeAttr('disabled').attr('name', newBlockPeriodId);
            hideShowRemoveThrottle();
        };

        const removeThrottle = function() {
            $(this).closest('.form-group').remove();
            hideShowRemoveThrottle();
        };

        const initAddRemoveThrottles = function() {
            $('.add_throttle').click(addThrottle);
            $('.remove_throttle').click(removeThrottle);
            hideShowRemoveThrottle();
        };
        initAddRemoveThrottles();

        $('#jobs_throttles_form').validate($.extend(form_support.handler, {
            submitHandler: function(form) {
                $(form).ajaxSubmit({
                    success: function(response) {
                        $.notify({
                            message: response.message
                        }, {
                            type: response.success ? 'success' : 'danger'
                        });
                        $('#throttles_wrapper').load('<?= 'jobs/throttles' ?>', initAddRemoveThrottles);
                    },
                    dataType: 'json'
                });
            },

            errorLabelContainer: '#jobs_throttles_error_message_box'
        }));
    });
</script>
