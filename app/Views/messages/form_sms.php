<?php
/**
 * @var object $person_info
 * @var string $controller_name
 * @var array $config
 */
?>

<?= form_open("messages/send_form/$person_info->person_id", ['id' => 'send_sms_form']) ?>

    <ul id="error_message_box" class="alert alert-warning d-none"></ul>

    <label for="first_name" class="form-label"><?= lang('Messages.first_name'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="first_name-icon"><i class="bi bi-person-square"></i></span>
        <input type="text" class="form-control" name="first_name" id="first_name" aria-describedby="first_name-icon" value="<?= $person_info->first_name; ?>" disabled readonly>
    </div>

    <label for="last_name" class="form-label"><?= lang('Messages.last_name'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="last_name-icon"><i class="bi bi-person-square"></i></span>
        <input type="text" class="form-control" name="last_name" id="last_name" aria-describedby="last_name-icon" value="<?= $person_info->last_name; ?>" disabled readonly>
    </div>

    <label for="phone" class="form-label"><?= lang('Messages.phone'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="phone-icon"><i class="bi bi-telephone"></i></span>
        <input type="text" class="form-control" name="phone" id="phone" aria-describedby="phone-icon" value="<?= $person_info->phone_number; ?>">
    </div>

    <label for="message" class="form-label"><?= lang('Messages.message'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="message-icon"><i class="bi bi-chat-dots"></i></span>
        <textarea class="form-control" name="message" id="message" rows="10" aria-describedby="message-icon"><?= $config['msg_msg'] ?></textarea>
    </div>

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
