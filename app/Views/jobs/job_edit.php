<?php
/**
 * @var string $uid
 * @var string $queue
 * @var string $priority
 * @var string $payload
 * @var string $available_at
 * @var array $priorities
 */
?>

<div id="required_fields_message"><?= lang('Common.fields_required_message') ?></div>
<ul id="error_message_box" class="error_message_box"></ul>

<?= form_open("jobs/save/$uid", ['id' => 'job_form', 'class' => 'form-horizontal']) ?>
    <fieldset id="job_basic_info">

        <div class="form-group form-group-sm">
            <?= form_label(lang('Jobs.queue'), 'job_queue', ['class' => 'control-label col-xs-3']) ?>
            <div class="col-xs-8">
                <?= form_input(['name' => 'job_queue', 'id' => 'job_queue', 'class' => 'form-control input-sm', 'value' => $queue, 'disabled' => 'disabled']) ?>
            </div>
        </div>

        <div class="form-group form-group-sm">
            <?= form_label(lang('Jobs.priority'), 'priority', ['class' => 'required control-label col-xs-3']) ?>
            <div class="col-xs-8">
                <?= form_dropdown('priority', array_combine($priorities, $priorities), $priority, ['id' => 'priority', 'class' => 'form-control input-sm']) ?>
            </div>
        </div>

        <div class="form-group form-group-sm">
            <?= form_label(lang('Jobs.available_at'), 'available_at', ['class' => 'required control-label col-xs-3']) ?>
            <div class="col-xs-8">
                <?= form_input(['name' => 'available_at', 'id' => 'available_at', 'type' => 'datetime-local', 'class' => 'form-control input-sm', 'value' => $available_at]) ?>
            </div>
        </div>

        <div class="form-group form-group-sm">
            <?= form_label(lang('Jobs.payload'), 'payload', ['class' => 'required control-label col-xs-3']) ?>
            <div class="col-xs-8">
                <?= form_textarea(['name' => 'payload', 'id' => 'payload', 'class' => 'form-control input-sm', 'rows' => 12], $payload) ?>
            </div>
        </div>

    </fieldset>
<?= form_close() ?>

<script type="text/javascript">
    $(document).ready(function() {
        $('#job_form').validate($.extend({
            submitHandler: function(form) {
                $(form).ajaxSubmit({
                    success: function(response) {
                        dialog_support.hide();
                        table_support.handle_submit("jobs", response);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        table_support.handle_submit("jobs", {
                            message: errorThrown
                        });
                    },
                    dataType: 'json'
                });
            },

            errorLabelContainer: '#error_message_box',

            rules: {
                payload: {
                    required: true
                },
                available_at: {
                    required: true
                }
            },

            messages: {
                payload: {
                    required: "<?= lang('Jobs.invalid_payload_json') ?>"
                }
            }
        }, form_support.error));
    });
</script>
