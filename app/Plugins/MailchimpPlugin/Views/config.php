<?php
/**
 * @var array $settings
 * @var string $controller_name
 */
?>

<?= form_open(site_url('plugins/saveConfig/mailchimp'), ['id' => 'config_form', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal']) ?>
<div id="config_wrapper">
    <fieldset id="config_info">

        <div id="required_fields_message"><?= lang('MailchimpPlugin.fields_required_message') ?></div>
        <div id="plugins_header"><?= lang('MailchimpPlugin.configuration') ?></div>
        <ul id="error_message_box" class="error_message_box"></ul>

        <div class="form-group form-group-sm">
            <?= form_label(lang('MailchimpPlugin.api_key'), 'api_key', ['class' => 'control-label col-xs-2']) ?>
            <div class="col-xs-4">
                <div class="input-group">
                        <span class="input-group-addon input-sm">
                            <span class="glyphicon glyphicon-cloud"></span>
                        </span>
                    <?= form_input([
                        'name'  => 'api_key',
                        'id'    => 'api_key',
                        'class' => 'form-control input-sm',
                        'value' => esc($settings['api_key'] ?? '')
                    ]) ?>
                </div>
            </div>
            <div class="col-xs-1">
                <label class="control-label">
                    <a href="https://eepurl.com/b9a05b" target="_blank">
                        <span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-placement="right" title="<?= lang('MailchimpPlugin.tooltip') ?>"></span>
                    </a>
                </label>
            </div>
        </div>

        <div class="form-group form-group-sm">
            <?= form_label(lang('MailchimpPlugin.lists'), 'list_id', ['class' => 'control-label col-xs-2']) ?>
            <div class="col-xs-4">
                <div class="input-group">
                        <span class="input-group-addon input-sm">
                            <span class="glyphicon glyphicon-user"></span>
                        </span>
                    <?= form_dropdown(
                        'list_id',
                        esc($settings['lists'] ?? ''),
                        esc($settings['list_id'] ?? ''),
                        'id="list_id" class="form-control input-sm"'
                    ) ?>
                </div>
            </div>
        </div>

        <?= form_submit([
            'name'  => 'submit_mailchimp',
            'id'    => 'submit_mailchimp',
            'value' => lang('Common.submit'),
            'class' => 'btn btn-primary btn-sm pull-right'
        ]) ?>

    </fieldset>
</div>
<?= form_close() ?>

<script type="text/javascript">
    // Validation and submit handling
    $(document).ready(function() {
        $('#api_key').change(function() {
            const apiKey = $('#api_key').val();
            if (!apiKey) {
                return;
            }

            $.post("<?= site_url('plugins/mailchimp/checkMailchimpApiKey') ?>", {
                    'api_key': apiKey
                },
                function(response) {
                    $.notify({
                        message: response.message
                    }, {
                        type: response.success ? 'success' : 'danger'
                    });
                    $('#list_id').empty();
                    $.each(response.lists, function(val, text) {
                        $('#list_id').append(new Option(text, val));
                    });
                    $('#list_id').prop('selectedIndex', 0);
                },
                'json'
            );
        });

        $('#config_form').validate($.extend(form_support.handler, {
            submitHandler: function(form) {
                $(form).ajaxSubmit({
                    success: function(response) {
                        $.notify({
                            message: response.message
                        }, {
                            type: response.success ? 'success' : 'danger'
                        });
                        if (response.success) {
                            $('#plugin-config-modal').modal('hide');
                        }
                    },
                    dataType: 'json'
                });
            },

            errorLabelContainer: '#error_message_box'
        }));
    });
</script>
