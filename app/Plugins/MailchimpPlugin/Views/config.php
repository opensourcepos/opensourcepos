<?php
/**
 * @var array $settings
 */
?>

<?= form_open(site_url('plugins/saveConfig/mailchimp'), ['id' => 'config_form', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal']) ?>
<div id="config_wrapper">
    <fieldset id="config_info">

        <div id="required_fields_message"><?= lang('Common.fields_required_message') ?></div>
        <div id="plugins_header"><?= lang('MailchimpPlugin.configuration') ?></div>
        <ul id="error_message_box" class="error_message_box"></ul>

        <div class="form-group form-group-sm">
            <?= form_label(lang('MailchimpPlugin.api_key'), 'api_key', ['class' => 'control-label col-xs-3']) ?>
            <div class="col-xs-8">
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
                    <span class="input-group-addon input-sm">
                        <a href="https://eepurl.com/b9a05b" target="_blank">
                            <span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-placement="right" title="<?= lang('MailchimpPlugin.tooltip') ?>"></span>
                        </a>
                    </span>
                </div>
            </div>
        </div>

        <div class="form-group form-group-sm">
            <?= form_label(lang('MailchimpPlugin.lists'), 'list_id', ['class' => 'control-label col-xs-3']) ?>
            <div class="col-xs-8">
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

<style>
    .form-group.has-error .input-group-addon .glyphicon-info-sign {
        color: #fff;
    }
</style>

<script type="text/javascript">
    $(document).ready(function() {
        let apiKeyValid = <?= !empty($settings['api_key']) ? 'true' : 'false' ?>;

        $('#api_key').on('change', function() {
            const apiKey = $(this).val();
            if (!apiKey) {
                apiKeyValid = true;
                return;
            }

            $.post("<?= site_url('plugins/mailchimp/checkMailchimpApiKey') ?>", {
                    'api_key': apiKey
                },
                function(response) {
                    const validator = $('#config_form').data('validator');
                    $.notify({ message: response.message }, { type: response.success ? 'success' : 'danger' });

                    if (!response.success) {
                        apiKeyValid = false;
                        validator.showErrors({ 'api_key': response.message });
                    } else {
                        apiKeyValid = true;
                        validator.element('#api_key');
                        $('#list_id').empty();
                        $.each(response.lists, function(val, text) {
                            $('#list_id').append(new Option(text, val));
                        });
                        $('#list_id').prop('selectedIndex', 0);
                    }
                },
                'json'
            );
        });

        $('#config_form').validate($.extend(form_support.handler, {
            submitHandler: function(form) {
                if (!apiKeyValid) {
                    $('#config_form').data('validator').showErrors({
                        'api_key': '<?= lang('MailchimpPlugin.key_unsuccessfully') ?>'
                    });
                    return;
                }
                $(form).ajaxSubmit({
                    success: function(response) {
                        $.notify({ message: response.message }, { type: response.success ? 'success' : 'danger' });
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
