<?php
/**
 * @var bool $logo_exists
 * @var string $controller_name
 * @var array $config
 */
?>

<?= form_open('config/saveInfo/', ['id' => 'info_config_form', 'enctype' => 'multipart/form-data', 'class' => 'needs-validation']) ?> <!-- TODO-BS5 add is-invalid and invalid-feeback from BS5 to boxes -->

    <?php
    $title_info['config_title'] = lang('Config.info_configuration');
    echo view('configs/config_header', $title_info);
    ?>

    <ul id="info_error_message_box" class="error_message_box"></ul>

    <div class="row">
        <div class="col-12 col-lg-6">
            <label for="info-company" class="form-label"><?= lang('Config.company'); ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-shop-window"></i></span>
                <input type="text" class="form-control" name="company" id="info-company" value="<?= $config['company']; ?>" required> <!-- TODO-BS5 invalid-feedback makes input borders not rounded? -->
                <div class="invalid-feedback"><?= lang('Config.company_required') ?></div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <label for="info-company_logo" class="form-label"><?= lang('Config.company_logo'); ?></label>
        <div class="col-12 col-lg-6">
            <div id="info-company_logo" class="w-100 fileinput <?= $logo_exists ? 'fileinput-exists' : 'fileinput-new'; ?>" data-provides="fileinput">
                <div class="input-group mb-3" aria-describedby="company-logo-desc">
                    <span class="input-group-text"><i class="bi bi-image"></i></span>
                    <div class="fileinput-new form-control rounded-end mb-0" style="height: 200px; cursor: default;"></div>
                    <div class="fileinput-exists fileinput-preview img-thumbnail form-control rounded-end mb-0 bg-light mh-100" style="height: 200px; cursor: default; background-size: 40px 40px; background-position: 0 0, 20px 20px; background-image: linear-gradient(45deg, white 25%, transparent 25%, transparent 75%, white 75%, white), linear-gradient(45deg, white 25%, transparent 25%, transparent 75%, white 75%, white);">
                        <img class="mh-100 mw-100" data-src="holder.js/100%x100%" alt="<?= lang('Config.company_logo') ?>" src="<?php if($logo_exists) echo base_url('uploads/' . $config['company_logo']); else echo '' ?>">
                    </div>
                </div>
                <div type="button" class="btn btn-secondary btn-file me-2">
                    <span class="fileinput-new"><i class="bi bi-hand-index me-2"></i><?= lang('Config.company_select_image') ?></span>
                    <span class="fileinput-exists"><i class="bi bi-images me-2"></i><?= lang('Config.company_change_image') ?></span>
                    <input type="file" name="company_logo">
                </div>
                <button type="button" class="btn btn-outline-secondary fileinput-exists" data-dismiss="fileinput">
                    <i class="bi bi-eraser me-2"></i><?= lang('Config.company_remove_image') ?>
                </button>
            </div>
        </div>
        <div class="col-12 col-lg-6 form-text d-none d-lg-block" id="company-logo-desc">
            <ul class="list-unstyled">
                <li>&raquo; Supported file formats; gif, png, jpg</li> <!-- TODO-BS5 add to translations -->
                <li>&raquo; Max upload size of 100kb</li> <!-- TODO-BS5 add to translations -->
                <li>&raquo; Max dimensions of 200x200px</li> <!-- TODO-BS5 add to translations -->
            </ul>
        </div>
    </div>

    <label for="info-address" class="form-label"><?= lang('Config.address'); ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
    <div class="input-group mb-3">
        <span class="input-group-text"><i class="bi bi-house"></i></span>
        <textarea class="form-control" name="address" id="info-address" rows="10" required><?= $config['address']; ?></textarea>
        <div class="invalid-feedback"><?= lang('Config.address_required') ?></div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-6">
            <label for="info-website" class="form-label"><?= lang('Config.website'); ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-globe"></i></span>
                <input type="url" class="form-control" name="website" id="info-website" value="<?= $config['website']; ?>">
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <label for="info-email" class="form-label"><?= lang('Config.email'); ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-at"></i></span>
                <input type="email" class="form-control" name="email" id="info-email" value="<?= $config['email']; ?>">
                <div class="invalid-feedback"><?= lang('Common.email_invalid_format') ?></div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <label for="info-phone" class="form-label"><?= lang('Config.phone'); ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                <input type="tel" class="form-control" name="phone" id="info-phone" value="<?= $config['phone']; ?>" required>
                <div class="invalid-feedback"><?= lang('Config.phone_required') ?></div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <label for="info-fax" class="form-label"><?= lang('Config.fax'); ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-printer"></i></span>
                <input type="tel" class="form-control" name="fax" id="info-fax" value="<?= $config['fax']; ?>">
            </div>
        </div>
    </div>

    <label for="info-return_policy" class="form-label"><?= lang('Common.return_policy'); ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
    <div class="input-group mb-3">
        <span class="input-group-text"><i class="bi bi-box-arrow-in-down-left"></i></span>
        <textarea class="form-control" name="return_policy" id="info-return_policy" rows="10" required><?= $config['return_policy']; ?></textarea>
        <div class="invalid-feedback"><?= lang('Config.return_policy_required') ?></div>
    </div>

    <div class="d-flex justify-content-end">
        <button class="btn btn-primary" type="submit" name="submit_info"><?= lang('Common.submit'); ?></button>
    </div>

<?= form_close() ?>

<script type="text/javascript">
    // Validation and submit handling
    $(document).ready(function() {
        $("a.fileinput-exists").click(function() {
            $.ajax({
                type: 'POST',
                url: '<?= "$controller_name/removeLogo"; ?>',
                dataType: 'json'
            })
        });

        $('#info_config_form').validate($.extend(form_support.handler, {

            errorLabelContainer: "#info_error_message_box",

            rules: {
                company: "required",
                address: "required",
                phone: "required",
                email: "email",
                return_policy: "required"
            },

            messages: {
                company: "<?= lang('Config.company_required') ?>",
                address: "<?= lang('Config.address_required') ?>",
                phone: "<?= lang('Config.phone_required') ?>",
                email: "<?= lang('Common.email_invalid_format') ?>",
                return_policy: "<?= lang('Config.return_policy_required') ?>"
            }
        }));
    });
</script>
