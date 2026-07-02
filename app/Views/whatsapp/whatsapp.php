<?= view('partial/header') ?>
<?php
/**
 * WhatsApp landing page: send a free-form message and browse recent conversations.
 *
 * @var array $conversations
 * @var bool $configured
 * @var array $config
 */
?>

<script type="text/javascript">
    dialog_support.init("a.modal-dlg");
</script>

<div class="container-fluid">

    <?php if (!$configured): ?>
        <div class="alert alert-warning"><?= lang('Whatsapp.not_configured_notice') ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-sm-6">
            <?= form_open("whatsapp/send/", ['id' => 'send_whatsapp_form', 'class' => 'form-horizontal']) ?>
                <fieldset>
                    <legend><?= lang('Whatsapp.whatsapp_send') ?></legend>

                    <div class="form-group form-group-sm">
                        <label for="phone" class="col-xs-3 control-label"><?= lang('Whatsapp.phone') ?></label>
                        <div class="col-xs-9">
                            <input class="form-control input-sm required" type="text" name="phone" id="phone" placeholder="<?= lang('Whatsapp.phone_placeholder') ?>">
                            <span class="help-block"><?= lang('Whatsapp.phone_help') ?></span>
                        </div>
                    </div>

                    <div class="form-group form-group-sm">
                        <label for="message" class="col-xs-3 control-label"><?= lang('Whatsapp.message') ?></label>
                        <div class="col-xs-9">
                            <textarea class="form-control input-sm required" rows="3" id="message" name="message" placeholder="<?= lang('Whatsapp.message_placeholder') ?>"><?= esc($config['whatsapp_msg'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <?= form_submit([
                        'name'  => 'submit_form',
                        'id'    => 'submit_form',
                        'value' => lang('Whatsapp.whatsapp_send'),
                        'class' => 'btn btn-primary btn-sm pull-right'
                    ]) ?>
                </fieldset>
            <?= form_close() ?>
        </div>

        <div class="col-sm-6">
            <legend><?= lang('Whatsapp.recent_conversations') ?></legend>
            <table class="table table-hover table-condensed">
                <thead>
                    <tr>
                        <th><?= lang('Whatsapp.phone') ?></th>
                        <th><?= lang('Whatsapp.messages') ?></th>
                        <th><?= lang('Whatsapp.last_activity') ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($conversations)): ?>
                        <tr><td colspan="4" class="text-muted"><?= lang('Whatsapp.no_conversations') ?></td></tr>
                    <?php else: ?>
                        <?php foreach ($conversations as $conversation): ?>
                            <tr>
                                <td><?= esc($conversation['phone']) ?></td>
                                <td><?= esc($conversation['message_count']) ?></td>
                                <td><?= esc($conversation['last_activity']) ?></td>
                                <td>
                                    <button type="button" class="btn btn-xs btn-info view-conversation" data-phone="<?= esc($conversation['phone']) ?>">
                                        <?= lang('Whatsapp.view') ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <div id="conversation_container"></div>
        </div>
    </div>
</div>

<?= view('partial/footer') ?>

<script type="text/javascript">
    $(document).ready(function() {
        var load_conversation = function(phone) {
            $.get('<?= site_url('whatsapp/conversation') ?>/' + encodeURIComponent(phone), function(html) {
                $('#conversation_container').html(html);
            });
        };

        $('#send_whatsapp_form').validate({
            submitHandler: function(form) {
                $(form).ajaxSubmit({
                    success: function(response) {
                        $.notify({
                            message: response.message
                        }, {
                            type: response.success ? 'success' : 'danger'
                        });

                        if (response.success) {
                            load_conversation($('#phone').val());
                        }
                    },
                    dataType: 'json'
                });
            }
        });

        $('.view-conversation').on('click', function() {
            load_conversation($(this).data('phone'));
        });
    });
</script>
