<?php
/**
 * @var array $config
 */
?>

<?= form_open('config/saveMessage/', ['id' => 'message_config_form', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal']) ?>
    <div id="config_wrapper">
        <fieldset id="config_info">

            <div id="required_fields_message"><?= lang('Common.fields_required_message') ?></div>
            <ul id="message_error_message_box" class="error_message_box"></ul>

            <div class="form-group form-group-sm">
                <?= form_label(lang('Config.msg_uid'), 'msg_uid', ['class' => 'control-label col-xs-2 required']) ?>
                <div class="col-xs-4">
                    <div class="input-group">
                        <span class="input-group-addon input-sm">
                            <span class="glyphicon glyphicon-user"></span>
                        </span>
                        <?= form_input([
                            'name'  => 'msg_uid',
                            'id'    => 'msg_uid',
                            'class' => 'form-control input-sm required',
                            'value' => $config['msg_uid']
                        ]) ?>
                    </div>
                </div>
            </div>

            <div class="form-group form-group-sm">
                <?= form_label(lang('Config.msg_pwd'), 'msg_pwd', ['class' => 'control-label col-xs-2 required']) ?>
                <div class="col-xs-4">
                    <div class="input-group">
                        <span class="input-group-addon input-sm">
                            <span class="glyphicon glyphicon-lock"></span>
                        </span>
                        <?= form_password([
                            'name'  => 'msg_pwd',
                            'id'    => 'msg_pwd',
                            'class' => 'form-control input-sm required',
                            'value' => $config['msg_pwd']
                        ]) ?>
                    </div>
                </div>
            </div>

            <div class="form-group form-group-sm">
                <?= form_label(lang('Config.msg_src'), 'msg_src', ['class' => 'control-label col-xs-2 required']) ?>
                <div class="col-xs-4">
                    <div class="input-group">
                        <span class="input-group-addon input-sm">
                            <span class="glyphicon glyphicon-bullhorn"></span>
                        </span>
                        <?= form_input([
                            'name'  => 'msg_src',
                            'id'    => 'msg_src',
                            'class' => 'form-control input-sm required',
                            'value' => $config['msg_src'] == null ? $config['company'] : $config['msg_src']
                        ]) ?>
                    </div>
                </div>
            </div>

            <div class="form-group form-group-sm">
                <?= form_label(lang('Config.msg_msg'), 'msg_msg', ['class' => 'control-label col-xs-2']) ?>
                <div class="col-xs-4">
                    <?= form_textarea([
                        'name'        => 'msg_msg',
                        'id'          => 'msg_msg',
                        'class'       => 'form-control input-sm',
                        'value'       => $config['msg_msg'],
                        'placeholder' => lang('Config.msg_msg_placeholder')
                    ]) ?>
                </div>
            </div>

            <?= form_submit([
                'name'  => 'submit_message',
                'id'    => 'submit_message',
                'value' => lang('Common.submit'),
                'class' => 'btn btn-primary btn-sm pull-right'
            ]) ?>

        </fieldset>
    </div>
<?= form_close() ?>

<script type="text/javascript">
    // Validation and submit handling
    $(document).ready(function() {
        $('#message_config_form').validate($.extend(form_support.handler, {

            errorLabelContainer: "#message_error_message_box",

            rules: {
                msg_uid: "required",
                msg_pwd: "required",
                msg_src: "required"
            },

            messages: {
                msg_uid: "<?= lang('Config.msg_uid_required') ?>",
                msg_pwd: "<?= lang('Config.msg_pwd_required') ?>",
                msg_src: "<?= lang('Config.msg_src_required') ?>"
            }
        }));
    });
</script>
