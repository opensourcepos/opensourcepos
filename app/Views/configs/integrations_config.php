<?php
/**
 * @var array $mailchimp
 * @var string $controller_name
 */
?>
<?= form_open('config/saveMailchimp/', ['id' => 'mailchimp_config_form', 'enctype' => 'multipart/form-data']) ?>

    <?php
    $title_info['config_title'] = lang('Config.integrations_configuration');
    echo view('configs/config_header', $title_info);
    ?>

    <legend class="fs-5"><?= lang('Config.mailchimp_configuration') ?></legend>
    <ul id="mailchimp_error_message_box" class="error_message_box"></ul>

    <div class="row">
        <div class="col-12 col-lg-6">
            <label for="mailchimp_api_key" class="form-label">
                <?= lang('Config.mailchimp_api_key'); ?>
                <!--<a href="https://eepurl.com/dyijVH" target="_blank" rel="noopener">
                    <i class="bi bi-info-circle-fill text-secondary" data-bs-toggle="tooltip" title="<?= lang('Config.mailchimp_tooltip'); ?>"></i>
                </a>-->
            </label>
            <div class="input-group">
                <span class="input-group-text" id="mailchimp-api-key-icon"><i class="bi bi-key"></i></span>
                <input type="text" class="form-control" name="mailchimp_api_key" id="mailchimp_api_key" aria-describedby="mailchimp-api-key-icon" value="<?= $mailchimp['api_key']; ?>">
            </div>
            <div class="form-text mb-3">
                <a class="link-secondary" href="https://eepurl.com/dyijVH" target="_blank" rel="noopener">
                    <i class="bi bi-info-square pe-1"></i><?= lang('Config.mailchimp_tooltip') ?>
                </a>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <label for="mailchimp_list_id" class="form-label"><?= lang('Config.mailchimp_lists'); ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="mailchimp-lists-icon"><i class="bi bi-list-stars"></i></span>
                <select class="form-select" id="mailchimp_list_id" aria-describedby="mailchimp-lists-icon" <?= $mailchimp['api_key'] == null ? 'disabled' : '' ?>>
                    <option>Choose...</option>
                    <?php foreach($mailchimp['lists'] as $value => $display_text): ?>
                        <option value="<?= $value; ?>" <?= $value == $mailchimp['list_id'] ? 'selected' : ''; ?>>
                            <?= $display_text; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button class="btn btn-primary" name="sumbit_mailchimp"><?= lang('Common.submit'); ?></button>
    </div>

<?= form_close() ?>

<script type="text/javascript">
    // Validation and submit handling
    $(document).ready(function() {
        $('#mailchimp_api_key').change(function() {
            $.post("<?= "$controller_name/checkMailchimpApiKey" ?>", {
                    'mailchimp_api_key': $('#mailchimp_api_key').val()
                },
                function(response) {
                    $.notify({
                        icon: 'bi bi-bell-fill',
                        message: response.message
                    }, {
                        type: response.success ? 'success' : 'danger'
                    });
                    $('#mailchimp_list_id').empty();
                    $.each(response.mailchimp_lists, function(val, text) {
                        $('#mailchimp_list_id').append(new Option(text, val));
                    });
                    $('#mailchimp_list_id').prop('selectedIndex', 0);
                },
                'json'
            );
        });

        $('#mailchimp_config_form').validate($.extend(form_support.handler, {
            submitHandler: function(form) {
                $(form).ajaxSubmit({
                    success: function(response) {
                        $.notify({
                            message: response.message
                        }, {
                            type: response.success ? 'success' : 'danger'
                        })
                    },
                    dataType: 'json'
                });
            },

            errorLabelContainer: '#mailchimp_error_message_box'
        }));
    });
</script>
