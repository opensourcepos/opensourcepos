<?php
/**
 * @var array $config
 */
?>
<?= form_open('config/saveEmail/', ['id' => 'email_config_form', 'enctype' => 'multipart/form-data']) ?>

    <?php
    $title_info['config_title'] = lang('Config.email_configuration');
    echo view('configs/config_header', $title_info);
    ?>

    <ul id="email_error_message_box" class="error_message_box"></ul>

    <div class="row">
        <div class="col-12 col-lg-6">
            <label for="protocol" class="form-label"><?= lang('Config.email_protocol'); ?></label>
            <div class="input-group mb-3">
                <label class="input-group-text"><i class="bi bi-mailbox"></i></label>
                <?= form_dropdown('protocol', array('mail' => 'Mail', 'sendmail' => 'Sendmail', 'smtp' => 'SMTP'), $config['protocol'], array('class' => 'form-select', 'id' => 'protocol')); ?>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <label for="mailpath" class="form-label"><?= lang('Config.email_mailpath'); ?></label>
            <div class="input-group mb-3">
                <label class="input-group-text"><i class="bi bi-braces"></i></label>
                <input type="text" name="mailpath" class="form-control" id="mailpath" value="<?= $config['mailpath']; ?>">
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <label for="smtp_host" class="form-label"><?= lang('Config.email_smtp_host'); ?></label>
            <div class="input-group mb-3">
                <label class="input-group-text"><i class="bi bi-database"></i></label>
                <input type="text" name="smtp_host" class="form-control" id="smtp_host" value="<?= $config['smtp_host']; ?>">
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <label for="smtp_port" class="form-label"><?= lang('Config.email_smtp_port'); ?></label>
            <div class="input-group mb-3">
                <label class="input-group-text"><i class="bi bi-door-open"></i></label>
                <input type="number" name="smtp_port" class="form-control" id="smtp_port" value="<?= $config['smtp_port']; ?>">
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <label for="smtp_crypto" class="form-label"><?= lang('Config.email_smtp_crypto'); ?></label>
            <div class="input-group mb-3">
                <label class="input-group-text"><i class="bi bi-shield-lock"></i></label>
                <?= form_dropdown('smtp_crypto', array('' => 'None', 'tls' => 'TLS', 'ssl' => 'SSL'), $config['smtp_crypto'], array('class' => 'form-select', 'id' => 'smtp_crypto')); ?>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <label for="smtp_timeout" class="form-label"><?= lang('Config.email_smtp_timeout'); ?></label>
            <div class="input-group mb-3">
                <label class="input-group-text"><i class="bi bi-stopwatch"></i></label>
                <input type="number" name="smtp_timeout" class="form-control" id="smtp_timeout" value="<?= $config['smtp_timeout']; ?>">
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <label for="smtp_user" class="form-label"><?= lang('Config.email_smtp_user'); ?></label>
            <div class="input-group mb-3">
                <label class="input-group-text"><i class="bi bi-person"></i></label>
                <input type="text" name="smtp_user" class="form-control" id="smtp_user" value="<?= $config['smtp_user']; ?>">
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <label for="smtp_pass" class="form-label"><?= lang('Config.email_smtp_pass'); ?></label>
            <div class="input-group mb-3">
                <label class="input-group-text"><i class="bi bi-lock"></i></label>
                <input type="password" name="smtp_pass" class="form-control" id="smtp_pass" value="<?= $config['smtp_pass']; ?>">
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button class="btn btn-primary" name="submit_email"><?= lang('Common.submit'); ?></button>
    </div>

<?= form_close() ?>

<script type="text/javascript">
    // Validation and submit handling
    $(document).ready(function() {
        var check_protocol = function() {
            if ($('#protocol').val() == 'sendmail') {
                $('#mailpath').prop('disabled', false);
                $('#smtp_host, #smtp_user, #smtp_pass, #smtp_port, #smtp_timeout, #smtp_crypto').prop('disabled', true);
            } else if ($('#protocol').val() == 'smtp') {
                $('#smtp_host, #smtp_user, #smtp_pass, #smtp_port, #smtp_timeout, #smtp_crypto').prop('disabled', false);
                $('#mailpath').prop('disabled', true);
            } else {
                $('#mailpath, #smtp_host, #smtp_user, #smtp_pass, #smtp_port, #smtp_timeout, #smtp_crypto').prop('disabled', true);
            }
        };

        $('#protocol').change(check_protocol).ready(check_protocol);

        $('#email_config_form').validate($.extend(form_support.handler, {
            submitHandler: function(form) {
                $(form).ajaxSubmit({
                    beforeSerialize: function(arr, $form, options) {
                        $('#mailpath, #smtp_host, #smtp_user, #smtp_pass, #smtp_port, #smtp_timeout, #smtp_crypto').prop('disabled', false);
                        return true;
                    },
                    success: function(response) {
                        $.notify({
                            icon: 'bi bi-bell-fill',
                            message: response.message
                        }, {
                            type: response.success ? 'success' : 'danger'
                        })
                        // Set back disabled state
                        check_protocol();
                    },
                    dataType: 'json'
                });
            },

            errorLabelContainer: '#email_error_message_box'
        }));
    });
</script>
