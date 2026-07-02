<?php
/**
 * @var array $config
 * @var array $whatsapp
 */
?>

<?= form_open('config/saveWhatsapp/', ['id' => 'whatsapp_config_form', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal']) ?>
    <div id="config_wrapper">
        <fieldset id="config_info">

            <div id="required_fields_message"><?= lang('Common.fields_required_message') ?></div>
            <ul id="whatsapp_error_message_box" class="error_message_box"></ul>

            <div class="form-group form-group-sm">
                <?= form_label(lang('Config.whatsapp_enabled'), 'whatsapp_enabled', ['class' => 'control-label col-xs-2']) ?>
                <div class="col-xs-4">
                    <?= form_checkbox('whatsapp_enabled', '1', !empty($config['whatsapp_enabled']), 'id="whatsapp_enabled"') ?>
                </div>
            </div>

            <div class="form-group form-group-sm">
                <?= form_label(lang('Config.whatsapp_phone_id'), 'whatsapp_phone_id', ['class' => 'control-label col-xs-2 required']) ?>
                <div class="col-xs-4">
                    <div class="input-group">
                        <span class="input-group-addon input-sm"><span class="glyphicon glyphicon-phone"></span></span>
                        <?= form_input(['name' => 'whatsapp_phone_id', 'id' => 'whatsapp_phone_id', 'class' => 'form-control input-sm required', 'value' => $config['whatsapp_phone_id'] ?? '']) ?>
                    </div>
                </div>
            </div>

            <div class="form-group form-group-sm">
                <?= form_label(lang('Config.whatsapp_business_id'), 'whatsapp_business_id', ['class' => 'control-label col-xs-2']) ?>
                <div class="col-xs-4">
                    <?= form_input(['name' => 'whatsapp_business_id', 'id' => 'whatsapp_business_id', 'class' => 'form-control input-sm', 'value' => $config['whatsapp_business_id'] ?? '']) ?>
                </div>
            </div>

            <div class="form-group form-group-sm">
                <?= form_label(lang('Config.whatsapp_token'), 'whatsapp_token', ['class' => 'control-label col-xs-2 required']) ?>
                <div class="col-xs-4">
                    <div class="input-group">
                        <span class="input-group-addon input-sm"><span class="glyphicon glyphicon-lock"></span></span>
                        <?= form_password(['name' => 'whatsapp_token', 'id' => 'whatsapp_token', 'class' => 'form-control input-sm required', 'value' => $whatsapp['token'] ?? '']) ?>
                    </div>
                </div>
            </div>

            <div class="form-group form-group-sm">
                <?= form_label(lang('Config.whatsapp_api_url'), 'whatsapp_api_url', ['class' => 'control-label col-xs-2']) ?>
                <div class="col-xs-4">
                    <?= form_input(['name' => 'whatsapp_api_url', 'id' => 'whatsapp_api_url', 'class' => 'form-control input-sm', 'value' => $config['whatsapp_api_url'] ?? 'https://graph.facebook.com']) ?>
                </div>
            </div>

            <div class="form-group form-group-sm">
                <?= form_label(lang('Config.whatsapp_api_version'), 'whatsapp_api_version', ['class' => 'control-label col-xs-2']) ?>
                <div class="col-xs-2">
                    <?= form_input(['name' => 'whatsapp_api_version', 'id' => 'whatsapp_api_version', 'class' => 'form-control input-sm', 'value' => $config['whatsapp_api_version'] ?? 'v21.0']) ?>
                </div>
            </div>

            <div class="form-group form-group-sm">
                <?= form_label(lang('Config.whatsapp_default_country_code'), 'whatsapp_default_country_code', ['class' => 'control-label col-xs-2']) ?>
                <div class="col-xs-2">
                    <?= form_input(['name' => 'whatsapp_default_country_code', 'id' => 'whatsapp_default_country_code', 'class' => 'form-control input-sm', 'value' => $config['whatsapp_default_country_code'] ?? '', 'placeholder' => lang('Config.whatsapp_default_country_code_placeholder')]) ?>
                </div>
            </div>

            <div class="form-group form-group-sm">
                <?= form_label(lang('Config.whatsapp_msg'), 'whatsapp_msg', ['class' => 'control-label col-xs-2']) ?>
                <div class="col-xs-4">
                    <?= form_textarea(['name' => 'whatsapp_msg', 'id' => 'whatsapp_msg', 'class' => 'form-control input-sm', 'value' => $config['whatsapp_msg'] ?? '', 'placeholder' => lang('Config.whatsapp_msg_placeholder')]) ?>
                </div>
            </div>

            <hr>
            <p class="col-xs-offset-2 col-xs-8"><strong><?= lang('Config.whatsapp_webhook_heading') ?></strong></p>

            <div class="form-group form-group-sm">
                <?= form_label(lang('Config.whatsapp_webhook_url'), 'whatsapp_webhook_url', ['class' => 'control-label col-xs-2']) ?>
                <div class="col-xs-8">
                    <?= form_input(['name' => 'whatsapp_webhook_url', 'id' => 'whatsapp_webhook_url', 'class' => 'form-control input-sm', 'value' => site_url('whatsapp/webhook'), 'readonly' => 'true']) ?>
                    <span class="help-block"><?= lang('Config.whatsapp_webhook_url_help') ?></span>
                </div>
            </div>

            <div class="form-group form-group-sm">
                <?= form_label(lang('Config.whatsapp_verify_token'), 'whatsapp_verify_token', ['class' => 'control-label col-xs-2']) ?>
                <div class="col-xs-4">
                    <?= form_input(['name' => 'whatsapp_verify_token', 'id' => 'whatsapp_verify_token', 'class' => 'form-control input-sm', 'value' => $config['whatsapp_verify_token'] ?? '']) ?>
                </div>
            </div>

            <div class="form-group form-group-sm">
                <?= form_label(lang('Config.whatsapp_app_secret'), 'whatsapp_app_secret', ['class' => 'control-label col-xs-2']) ?>
                <div class="col-xs-4">
                    <div class="input-group">
                        <span class="input-group-addon input-sm"><span class="glyphicon glyphicon-lock"></span></span>
                        <?= form_password(['name' => 'whatsapp_app_secret', 'id' => 'whatsapp_app_secret', 'class' => 'form-control input-sm', 'value' => $whatsapp['app_secret'] ?? '']) ?>
                    </div>
                </div>
            </div>

            <div class="col-xs-offset-2 col-xs-8">
                <p class="help-block"><?= lang('Config.whatsapp_window_notice') ?></p>
            </div>

            <?= form_submit([
                'name'  => 'submit_whatsapp',
                'id'    => 'submit_whatsapp',
                'value' => lang('Common.submit'),
                'class' => 'btn btn-primary btn-sm pull-right'
            ]) ?>

            <div class="col-xs-offset-2 col-xs-8" style="margin-top: 10px;">
                <span class="glyphicon glyphicon-info-sign">&nbsp;</span>
                <a href="https://developers.facebook.com/documentation/business-messaging/whatsapp/get-started" target="_blank" rel="noopener noreferrer">
                    <?= lang('Config.whatsapp_docs_link') ?>
                </a>
            </div>

        </fieldset>
    </div>
<?= form_close() ?>

<script type="text/javascript">
    // Validation and submit handling
    $(document).ready(function() {
        $('#whatsapp_config_form').validate($.extend(form_support.handler, {

            errorLabelContainer: "#whatsapp_error_message_box",

            rules: {
                whatsapp_phone_id: "required",
                whatsapp_token: "required"
            },

            messages: {
                whatsapp_phone_id: "<?= lang('Config.whatsapp_phone_id_required') ?>",
                whatsapp_token: "<?= lang('Config.whatsapp_token_required') ?>"
            }
        }));
    });
</script>
