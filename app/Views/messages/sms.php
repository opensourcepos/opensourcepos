<?= view('partial/header') ?>

<?= form_open("messages/send/", ['id' => 'send_sms_form', 'enctype' => 'multipart/form-data', 'method' => 'post']) ?>

    <?php
    $title_info['config_title'] = lang('Messages.sms_send');
    echo view('configs/config_header', $title_info);
    ?>

    <div class="col mb-3">
        <label for="message-recipients" class="form-label"><?= lang('Messages.phone'); ?></label>
        <div class="input-group">
            <span class="input-group-text" id="message-icon"><i class="bi bi-phone"></i></span>
            <input type="text" name="phone" class="form-control" id="message-recipients" aria-describedby="message-icon" required placeholder="<?= lang('Messages.phone_placeholder'); ?>">
        </div>
        <span class="form-text"><?= lang('Messages.multiple_phones'); ?></span>
    </div>

    <div class="col mb-3">
        <label for="text-message" class="form-label"><?= lang('Messages.message'); ?></label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-chat-quote"></i></span>
            <textarea class="form-control" name="message" id="text-message" rows="10" placeholder="<?= lang('Messages.message_placeholder'); ?>"></textarea>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button class="btn btn-primary" id="submit_form" name="submit_form">Send</button>
    </div>

<?= form_close() ?>

<?= view('partial/footer') ?>

<script type="text/javascript">
    // Validation and submit handling
    $(document).ready(function() {
        $('#send_sms_form').validate({
            submitHandler: function(form) {
                $(form).ajaxSubmit({
                    success: function(response) {
                        $.notify({
                            icon: 'bi bi-bell-fill',
                            message: response.message
                        }, {
                            type: response.success ? 'success' : 'danger'
                        })
                    },
                    dataType: 'json'
                });
            }
        });
    });
</script>
