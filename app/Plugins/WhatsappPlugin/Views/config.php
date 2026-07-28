<?php
/**
 * @var array  $settings
 * @var string $webhook_url
 */
?>

<?= form_open(site_url('plugins/saveConfig/whatsapp'), ['id' => 'config_form', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal']) ?>
<div id="config_wrapper">
    <fieldset id="config_info">

        <div id="required_fields_message"><?= lang('Common.fields_required_message') ?></div>
        <div id="plugins_header"><?= lang('WhatsappPlugin.configuration') ?></div>
        <ul id="error_message_box" class="error_message_box"></ul>

        <div class="form-group form-group-sm">
            <?= form_label(lang('WhatsappPlugin.phone_id'), 'phone_id', ['class' => 'control-label col-xs-3 required']) ?>
            <div class="col-xs-8">
                <div class="input-group">
                    <span class="input-group-addon input-sm"><span class="glyphicon glyphicon-phone"></span></span>
                    <?= form_input([
                        'name'  => 'phone_id',
                        'id'    => 'phone_id',
                        'class' => 'form-control input-sm required',
                        'value' => esc($settings['phone_id'] ?? ''),
                    ]) ?>
                </div>
            </div>
        </div>

        <div class="form-group form-group-sm">
            <?= form_label(lang('WhatsappPlugin.business_id'), 'business_id', ['class' => 'control-label col-xs-3']) ?>
            <div class="col-xs-8">
                <?= form_input([
                    'name'  => 'business_id',
                    'id'    => 'business_id',
                    'class' => 'form-control input-sm',
                    'value' => esc($settings['business_id'] ?? ''),
                ]) ?>
            </div>
        </div>

        <div class="form-group form-group-sm">
            <?= form_label(lang('WhatsappPlugin.token'), 'token', ['class' => 'control-label col-xs-3 required']) ?>
            <div class="col-xs-8">
                <div class="input-group">
                    <span class="input-group-addon input-sm"><span class="glyphicon glyphicon-lock"></span></span>
                    <?= form_password([
                        'name'  => 'token',
                        'id'    => 'token',
                        'class' => 'form-control input-sm required',
                        'value' => esc($settings['token'] ?? ''),
                    ]) ?>
                </div>
            </div>
        </div>

        <div class="form-group form-group-sm">
            <?= form_label(lang('WhatsappPlugin.api_url'), 'api_url', ['class' => 'control-label col-xs-3']) ?>
            <div class="col-xs-8">
                <?= form_input([
                    'name'  => 'api_url',
                    'id'    => 'api_url',
                    'class' => 'form-control input-sm',
                    'value' => esc($settings['api_url'] ?? 'https://graph.facebook.com'),
                ]) ?>
            </div>
        </div>

        <div class="form-group form-group-sm">
            <?= form_label(lang('WhatsappPlugin.api_version'), 'api_version', ['class' => 'control-label col-xs-3']) ?>
            <div class="col-xs-4">
                <?= form_input([
                    'name'  => 'api_version',
                    'id'    => 'api_version',
                    'class' => 'form-control input-sm',
                    'value' => esc($settings['api_version'] ?? 'v21.0'),
                ]) ?>
            </div>
        </div>

        <div class="form-group form-group-sm">
            <?= form_label(lang('WhatsappPlugin.default_country_code'), 'default_country_code', ['class' => 'control-label col-xs-3']) ?>
            <div class="col-xs-4">
                <?= form_input([
                    'name'        => 'default_country_code',
                    'id'          => 'default_country_code',
                    'class'       => 'form-control input-sm',
                    'value'       => esc($settings['default_country_code'] ?? ''),
                    'placeholder' => lang('WhatsappPlugin.default_country_code_placeholder'),
                ]) ?>
            </div>
        </div>

        <div class="form-group form-group-sm">
            <?= form_label(lang('WhatsappPlugin.saved_message'), 'saved_message', ['class' => 'control-label col-xs-3']) ?>
            <div class="col-xs-8">
                <?= form_textarea([
                    'name'        => 'saved_message',
                    'id'          => 'saved_message',
                    'class'       => 'form-control input-sm',
                    'value'       => $settings['saved_message'] ?? '',
                    'placeholder' => lang('WhatsappPlugin.saved_message_placeholder'),
                ]) ?>
            </div>
        </div>

        <hr>
        <div id="plugins_header"><?= lang('WhatsappPlugin.webhook_heading') ?></div>

        <div class="form-group form-group-sm">
            <?= form_label(lang('WhatsappPlugin.webhook_url'), 'webhook_url', ['class' => 'control-label col-xs-3']) ?>
            <div class="col-xs-8">
                <?= form_input([
                    'name'     => 'webhook_url_display',
                    'id'       => 'webhook_url_display',
                    'class'    => 'form-control input-sm',
                    'value'    => esc($webhook_url ?? ''),
                    'readonly' => 'true',
                ]) ?>
                <span class="help-block"><?= lang('WhatsappPlugin.webhook_url_help') ?></span>
            </div>
        </div>

        <div class="form-group form-group-sm">
            <?= form_label(lang('WhatsappPlugin.verify_token'), 'verify_token', ['class' => 'control-label col-xs-3']) ?>
            <div class="col-xs-8">
                <?= form_input([
                    'name'  => 'verify_token',
                    'id'    => 'verify_token',
                    'class' => 'form-control input-sm',
                    'value' => esc($settings['verify_token'] ?? ''),
                ]) ?>
            </div>
        </div>

        <div class="form-group form-group-sm">
            <?= form_label(lang('WhatsappPlugin.app_secret'), 'app_secret', ['class' => 'control-label col-xs-3']) ?>
            <div class="col-xs-8">
                <div class="input-group">
                    <span class="input-group-addon input-sm"><span class="glyphicon glyphicon-lock"></span></span>
                    <?= form_password([
                        'name'  => 'app_secret',
                        'id'    => 'app_secret',
                        'class' => 'form-control input-sm',
                        'value' => esc($settings['app_secret'] ?? ''),
                    ]) ?>
                </div>
                <span class="help-block"><?= lang('WhatsappPlugin.app_secret_help') ?></span>
            </div>
        </div>

        <div class="col-xs-offset-3 col-xs-8">
            <p class="help-block"><?= lang('WhatsappPlugin.window_notice') ?></p>
        </div>

        <?= form_submit([
            'name'  => 'submit_whatsapp',
            'id'    => 'submit_whatsapp',
            'value' => lang('Common.submit'),
            'class' => 'btn btn-primary btn-sm pull-right',
        ]) ?>

        <div class="col-xs-offset-3 col-xs-8" style="margin-top: 10px;">
            <span class="glyphicon glyphicon-info-sign">&nbsp;</span>
            <a href="https://developers.facebook.com/documentation/business-messaging/whatsapp/get-started" target="_blank" rel="noopener noreferrer">
                <?= lang('WhatsappPlugin.docs_link') ?>
            </a>
        </div>

    </fieldset>
</div>
<?= form_close() ?>

<script type="text/javascript">
    $(document).ready(function() {
        // The read-only webhook URL is display-only; keep it out of the submitted settings.
        $('#webhook_url_display').prop('disabled', true);

        $('#config_form').validate($.extend(form_support.handler, {
            submitHandler: function(form) {
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

            errorLabelContainer: '#error_message_box',

            rules: {
                phone_id: 'required',
                token: 'required'
            },

            messages: {
                phone_id: '<?= lang('WhatsappPlugin.phone_id_required') ?>',
                token: '<?= lang('WhatsappPlugin.token_required') ?>'
            }
        }));
    });
</script>
