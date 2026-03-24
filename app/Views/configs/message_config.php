<?php
/**
 * @var array $config
 */
?>

<?= form_open('config/saveMessage/', ['id' => 'message_config_form', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal']) ?>

    <?php
    $title_info['config_title'] = lang('Config.message_configuration');
    echo view('configs/config_header', $title_info);
    ?>

    <ul id="message_error_message_box" class="error_message_box"></ul>

    <div class="row">
        <div class="col-12 col-lg-6 mb-3">
            <label for="msg_uid" class="form-label"><?= lang('Config.msg_uid'); ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
            <div class="input-group">
                <span class="input-group-text" id="msg_uid_icon"><i class="bi bi-person"></i></span>
                <input type="text" class="form-control" name="msg_uid" id="msg_uid" aria-describedby="msg_uid_icon" required value="<?= esc($config['msg_uid']); ?>">
            </div>
        </div>

        <div class="col-12 col-lg-6 mb-3">
            <label for="msg_pwd" class="form-label"><?= lang('Config.msg_pwd'); ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
            <div class="input-group">
                <span class="input-group-text" id="msg_pwd_icon"><i class="bi bi-lock"></i></span>
                <input type="password" class="form-control" name="msg_pwd" id="msg_pwd" aria-describedby="msg_pwd_icon" required value="<?= esc($config['msg_pwd']); ?>">
            </div>
        </div>

        <div class="col-12 col-lg-6 mb-3">
            <label for="msg_src" class="form-label"><?= lang('Config.msg_src'); ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
            <div class="input-group">
                <span class="input-group-text" id="msg_src_icon"><i class="bi bi-megaphone"></i></span>
                <input type="text" class="form-control" name="msg_src" id="msg_src" aria-describedby="msg_src_icon" required value="<?= esc($config['msg_src'] == null ? $config['company'] : $config['msg_src']); ?>">
            </div>
        </div>
    </div>

    <label for="msg-msg" class="form-label"><?= lang('Config.msg_msg'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text"><i class="bi bi-chat-quote"></i></span>
        <textarea class="form-control" name="msg_msg" id="msg-msg" rows="10" placeholder="<?= lang('Config.msg_msg_placeholder'); ?>" value="<?= $config['msg_msg']; ?>"></textarea>
    </div>

    <div class="d-flex justify-content-end">
        <button class="btn btn-primary" type="submit" name="submit_message"><?= lang('Common.submit'); ?></button>
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
