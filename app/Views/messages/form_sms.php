<?php
/**
 * @var object $person_info
 * @var string $controller_name
 * @var array $config
 */
?>

<div id="required_fields_message"><?= lang('Common.fields_required_message') ?></div>
<ul id="error_message_box" class="error_message_box"></ul>

<?= form_open("messages/send_form/$person_info->person_id", ['id' => 'send_sms_form', 'class' => 'form-horizontal']) ?>
    <fieldset>

        <div class="form-group form-group-sm">
            <?= form_label(lang('Messages.first_name'), 'first_name_label', ['for' => 'first_name', 'class' => 'control-label col-xs-2']) ?>
            <div class="col-xs-10">
                <?= form_input(['class' => 'form-control input-sm', 'type' => 'text', 'name' => 'first_name', 'value' => $person_info->first_name, 'readonly' => 'true']) ?>
            </div>
        </div>

        <div class="form-group form-group-sm">
            <?= form_label(lang('Messages.last_name'), 'last_name_label', ['for' => 'last_name', 'class' => 'control-label col-xs-2']) ?>
            <div class="col-xs-10">
                <?= form_input(['class' => 'form-control input-sm', 'type' => 'text', 'name' => 'last_name', 'value' => $person_info->last_name, 'readonly' => 'true']) ?>
            </div>
        </div>

        <div class="form-group form-group-sm">
            <?= form_label(lang('Messages.phone'), 'phone_label', ['for' => 'phone', 'class' => 'control-label col-xs-2 required']) ?>
            <div class="col-xs-10">
                <div class="input-group">
                    <span class="input-group-addon input-sm"><span class="glyphicon glyphicon-phone-alt"></span></span>
                    <?= form_input(['class' => 'form-control input-sm required', 'type' => 'text', 'name' => 'phone', 'value' => $person_info->phone_number]) ?>
                </div>
            </div>
        </div>

        <div class="form-group form-group-sm">
            <?= form_label(lang('Messages.message'), 'message_label', ['for' => 'message', 'class' => 'control-label col-xs-2 required']) ?>
            <div class="col-xs-10">
                <?= form_textarea(['class' => 'form-control input-sm required', 'name' => 'message', 'id' => 'message', 'value' => $config['msg_msg']]) ?>
            </div>
        </div>

    </fieldset>
<?= form_close() ?>

<script type="text/javascript">
    // Validation and submit handling
    $(document).ready(function() {
        $('#send_sms_form').validate($.extend({
            submitHandler: function(form) {
                $(form).ajaxSubmit({
                    success: function(response) {
                        dialog_support.hide();
                        table_support.handle_submit("<?= esc($controller_name) ?>", response);
                    },
                    dataType: 'json'
                });
            },

            errorLabelContainer: '#error_message_box',

            rules: {
                phone: {
                    required: true,
                    number: true
                },
                message: {
                    required: true,
                    number: false
                }
            },

            messages: {
                phone: {
                    required: "<?= lang('Messages.phone_number_required') ?>",
                    number: "<?= lang('Messages.phone') ?>"
                },
                message: {
                    required: "<?= lang('Messages.message_required') ?>"
                }
            }
        }, form_support.error));
    });
</script>
