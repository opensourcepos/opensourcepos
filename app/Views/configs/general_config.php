<?php
/**
 * @var array $themes
 * @var array $image_allowed_types
 * @var array $selected_image_allowed_types
 * @var bool $show_office_group
 * @var string $controller_name
 * @var array $config
 */
?>


<?= form_open('config/saveGeneral/', ['id' => 'general_config_form', 'enctype' => 'multipart/form-data']) ?>

    <?php
    $title_info['config_title'] = lang('Config.general_configuration');
    echo view('configs/config_header', $title_info);
    ?>

    <ul id="general_error_message_box" class="error_message_box"></ul>

    <div class="row">
        <div class="col-12 col-sm-6 col-xxl-3">
            <label for="default_sales_discount" class="form-label"><?= lang('Config.default_sales_discount') ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-cart-dash"></i></span>
                <input class="form-control" type="number" min="0" max="100" name="default_sales_discount" id="default_sales_discount" value="<?= $config['default_sales_discount'] ?>" required>
                <input type="radio" class="btn-check" name="default_sales_discount_type" id="dsd_type_1" value="1" <?= $config['default_sales_discount_type'] == 1 ? 'checked' : '' ?>>
                <label class="btn btn-outline-primary fw-semibold" for="dsd_type_1"><?= $config['currency_symbol'] ?></label>
                <input type="radio" class="btn-check" name="default_sales_discount_type" id="dsd_type_0" value="0" <?= $config['default_sales_discount_type'] == 0 ? 'checked' : '' ?>>
                <label class="btn btn-outline-primary fw-semibold" for="dsd_type_0">%</label>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xxl-3">
            <label for="default_receivings_discount" class="form-label"><?= lang('Config.default_receivings_discount') ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-bag-dash"></i></span>
                <input class="form-control" type="number" min="0" max="100" name="default_receivings_discount" id="default_receivings_discount" value="<?= $config['default_receivings_discount'] ?>" required>
                <input type="radio" class="btn-check" name="default_receivings_discount_type" id="drd_type_1" value="1" <?= $config['default_receivings_discount_type'] == 1 ? 'checked' : '' ?>>
                <label class="btn btn-outline-primary fw-semibold" for="drd_type_1"><?= $config['currency_symbol'] ?></label>
                <input type="radio" class="btn-check" name="default_receivings_discount_type" id="drd_type_0" value="0" <?= $config['default_receivings_discount_type'] == 0 ? 'checked' : '' ?>>
                <label class="btn btn-outline-primary fw-semibold" for="drd_type_0">%</label>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-sm-6 col-xxl-3">
            <label for="lines_per_page" class="form-label"><?= lang('Config.lines_per_page'); ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-list"></i></span>
                <input type="number" min="10" max="1000" name="lines_per_page" class="form-control" id="lines_per_page" value="<?= $config['lines_per_page']; ?>" required>
            </div>
        </div>
    </div>

    <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" role="switch" id="enforce_privacy" name="enforce_privacy" <?= $config['enforce_privacy'] == 1 ? 'checked' : '' ?>>
        <label class="form-check-label" for="enforce_privacy"><?= lang('Config.enforce_privacy'); ?></label>
        <div class="form-text"><i class="bi bi-info-square pe-1"></i><?= lang('Config.enforce_privacy_tooltip') ?></div>
    </div>

    <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" role="switch" id="receiving_calculate_average_price" name="receiving_calculate_average_price" <?= $config['receiving_calculate_average_price'] == 1 ? 'checked' : '' ?>>
        <label class="form-check-label" for="receiving_calculate_average_price"><?= lang('Config.receiving_calculate_average_price'); ?></label>
    </div>

    <div class="row">
        <label class="form-label"><?= lang('Config.image_restrictions'); ?></label>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="input-group mb-3" data-bs-toggle="tooltip" title="<?= lang('Config.image_max_width_tooltip'); ?>">
                <span class="input-group-text"><i class="bi bi-arrows"></i></span>
                <input type="number" min="128" max="3840" name="image_max_width" id="image_max_width" class="form-control" value="<?= $config['image_max_width']; ?>" required>
                <span class="input-group-text">px</span>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="input-group mb-3" data-bs-toggle="tooltip" title="<?= lang('Config.image_max_height_tooltip'); ?>">
                <span class="input-group-text"><i class="bi bi-arrows-vertical"></i></span>
                <input type="number" min="128" max="3840" name="image_max_height" id="image_max_height" class="form-control" value="<?= $config['image_max_height']; ?>" required>
                <span class="input-group-text">px</span>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="input-group mb-3" data-bs-toggle="tooltip" title="<?= lang('Config.image_max_size_tooltip'); ?>">
                <span class="input-group-text"><i class="bi bi-hdd"></i></span>
                <input type="number" min="128" max="2048" name="image_max_size" id="image_max_size" class="form-control" value="<?= $config['image_max_size']; ?>" required>
                <span class="input-group-text">kb</span>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="input-group mb-3" data-bs-toggle="tooltip" title="<?= lang('Config.image_allowed_file_types'); ?>">
                <label class="input-group-text"><i class="bi bi-images"></i></label>
                <select name="image_allowed_types[]" id="image_allowed_types" class="form-select" multiple placeholder="<?= lang('Config.image_allowed_file_types'); ?>" required>
                    <?php foreach($image_allowed_types as $type): ?>
                    <option value="<?= $type; ?>" <?= in_array($type, $selected_image_allowed_types) ? 'selected' : ''; ?>>
                        <?= $type; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" name="gcaptcha_enable" id="gcaptcha_enable" value="gcaptcha_enable" <?= $config['gcaptcha_enable'] == 1 ? 'checked' : '' ?>>
        <label class="form-check-label" for="gcaptcha_enable"><?= lang('Config.gcaptcha_enable') ?></label>
        <a class="d-inline-block" href="https://google.com/recaptcha/admin" target="_blank" rel="noopener"><i class="bi bi-link-45deg link-secondary"></i></a>
        <div class="form-text"><i class="bi bi-info-square pe-1"></i><?= lang('Config.gcaptcha_tooltip') ?></div>
    </div>

    <div class="row mb-3">
        <div class="col-12 col-lg-6">
            <label for="gcaptcha_site_key" class="form-label"><?= lang('Config.gcaptcha_site_key') ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-key"></i></span>
                <input type="text" class="form-control" name="gcaptcha_site_key" id="gcaptcha_site_key" required>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <label for="gcaptcha_secret_key" class="form-label"><?= lang('Config.gcaptcha_secret_key') ?></label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-stars"></i></span>
                <input type="text" class="form-control" name="gcaptcha_secret_key" id="gcaptcha_secret_key" required>
            </div>
        </div>
    </div>

    <div class="row" id="suggestions_layout">
        <label for="suggestions_layout" class="form-label"><?= lang('Config.suggestions_layout'); ?></label>
        <div class="col-12 col-sm-6 col-xl-4 col-xxl-3 mb-3">
            <div class="input-group">
                <label class="input-group-text"><i class="bi bi-layout-three-columns"></i>&nbsp;1.</label>
                <select class="form-select" name="suggestions_first_column">
                    <option value="name" <?= $config['suggestions_first_column'] == 'name' ? 'selected' : '' ?>><?= lang('Items.name') ?></option>
                    <option value="item_number" <?= $config['suggestions_first_column'] == 'item_number' ? 'selected' : '' ?>><?= lang('Items.number_information') ?></option>
                    <option value="unit_price" <?= $config['suggestions_first_column'] == 'unit_price' ? 'selected' : '' ?>><?= lang('Items.unit_price') ?></option>
                    <option value="cost_price" <?= $config['suggestions_first_column'] == 'cost_price' ? 'selected' : '' ?>><?= lang('Items.cost_price') ?></option>
                </select>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4 col-xxl-3 mb-3">
            <div class="input-group">
                <label class="input-group-text"><i class="bi bi-layout-three-columns"></i>&nbsp;2.</label>
                <select class="form-select" name="suggestions_second_column">
                    <option value="" <?= $config['suggestions_second_column'] == null ? 'selected' : '' ?>><?= lang('Config.none') ?></option>
                    <option value="name" <?= $config['suggestions_second_column'] == 'name' ? 'selected' : '' ?>><?= lang('Items.name') ?></option>
                    <option value="item_number" <?= $config['suggestions_second_column'] == 'item_number' ? 'selected' : '' ?>><?= lang('Items.number_information') ?></option>
                    <option value="unit_price" <?= $config['suggestions_second_column'] == 'unit_price' ? 'selected' : '' ?>><?= lang('Items.unit_price') ?></option>
                    <option value="cost_price" <?= $config['suggestions_second_column'] == 'cost_price' ? 'selected' : '' ?>><?= lang('Items.cost_price') ?></option>
                </select>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4 col-xxl-3 mb-3">
            <div class="input-group">
                <label class="input-group-text"><i class="bi bi-layout-three-columns"></i>&nbsp;3.</label>
                <select class="form-select" name="suggestions_third_column">
                    <option value="" <?= $config['suggestions_third_column'] == null ? 'selected' : '' ?>><?= lang('Config.none') ?></option>
                    <option value="name" <?= $config['suggestions_third_column'] == 'name' ? 'selected' : '' ?>><?= lang('Items.name') ?></option>
                    <option value="item_number" <?= $config['suggestions_third_column'] == 'item_number' ? 'selected' : '' ?>><?= lang('Items.number_information') ?></option>
                    <option value="unit_price" <?= $config['suggestions_third_column'] == 'unit_price' ? 'selected' : '' ?>><?= lang('Items.unit_price') ?></option>
                    <option value="cost_price" <?= $config['suggestions_third_column'] == 'cost_price' ? 'selected' : '' ?>><?= lang('Items.cost_price') ?></option>
                </select>
            </div>
        </div>
    </div>

    <label for="giftcard_number" class="form-label"><?= lang('Config.giftcard_number'); ?></label>
    <div class="form-group form-group-sm mb-3">
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="giftcard_number" id="giftcard_series" value="series" <?= $config['giftcard_number'] == 'series' ? 'checked' : '' ?>>
            <label class="form-check-label" for="giftcard_series"><?= lang('Config.giftcard_series') ?></label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="giftcard_number" id="giftcard_random" value="random" <?= $config['giftcard_number'] == 'random' ? 'checked' : '' ?>>
            <label class="form-check-label" for="giftcard_random"><?= lang('Config.giftcard_random') ?></label>
        </div>
    </div>

    <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" role="switch" id="derive_sale_quantity" name="derive_sale_quantity" value="derive_sale_quantity" <?= $config['derive_sale_quantity'] == 1 ? 'checked' : '' ?>>
        <label class="form-check-label" for="derive_sale_quantity"><?= lang('Config.derive_sale_quantity'); ?></label>
        <div class="form-text"><i class="bi bi-info-square pe-1"></i><?= lang('Config.derive_sale_quantity_tooltip') ?></div>
    </div>

    <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" role="switch" id="show_office_group" name="show_office_group" value="show_office_group" <?= $show_office_group > 0 ? 'checked' : '' ?>>
        <label class="form-check-label" for="show_office_group"><?= lang('Config.show_office_group'); ?></label>
    </div>

    <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" role="switch" id="multi_pack_enabled" name="multi_pack_enabled" value="multi_pack_enabled" <?= $config['multi_pack_enabled'] == 1 ? 'checked' : '' ?>>
        <label class="form-check-label" for="multi_pack_enabled"><?= lang('Config.multi_pack_enabled'); ?></label>
    </div>

    <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" role="switch" id="include_hsn" name="include_hsn" value="include_hsn" <?= $config['include_hsn'] == 1 ? 'checked' : '' ?>>
        <label class="form-check-label" for="include_hsn"><?= lang('Config.include_hsn'); ?></label>
    </div>

    <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" role="switch" id="category_dropdown" name="category_dropdown" value="category_dropdown" <?= $config['category_dropdown'] == 1 ? 'checked' : '' ?>>
        <label class="form-check-label" for="category_dropdown"><?= lang('Config.category_dropdown'); ?></label>
    </div>

    <div class="d-flex justify-content-end">
        <button class="btn btn-primary" type="submit" name="submit_general"><?= lang('Common.submit'); ?></button>
    </div>

<?= form_close() ?>

<script type="text/javascript">
    // Validation and submit handling
    $(document).ready(function() {
        var enable_disable_gcaptcha_enable = (function() {
            var gcaptcha_enable = $("#gcaptcha_enable").is(":checked");
            if (gcaptcha_enable) {
                $("#gcaptcha_site_key, #gcaptcha_secret_key").prop("disabled", !gcaptcha_enable).addClass("required");
                $("#config_gcaptcha_site_key, #config_gcaptcha_secret_key").addClass("required");
            } else {
                $("#gcaptcha_site_key, #gcaptcha_secret_key").prop("disabled", gcaptcha_enable).removeClass("required");
                $("#config_gcaptcha_site_key, #config_gcaptcha_secret_key").removeClass("required");
            }

            return arguments.callee;
        })();

        $("#gcaptcha_enable").change(enable_disable_gcaptcha_enable);

        $('#general_config_form').validate($.extend(form_support.handler, {

            errorLabelContainer: "#general_error_message_box",

            rules: {
                lines_per_page: {
                    required: true,
                    remote: "<?= "$controller_name/checkNumeric" ?>"
                },
                default_sales_discount: {
                    required: true,
                    remote: "<?= "$controller_name/checkNumeric" ?>"
                },
                gcaptcha_site_key: {
                    required: "#gcaptcha_enable:checked"
                },
                gcaptcha_secret_key: {
                    required: "#gcaptcha_enable:checked"
                }
            },

            messages: {
                default_sales_discount: {
                    required: "<?= lang('Config.default_sales_discount_required') ?>",
                    number: "<?= lang('Config.default_sales_discount_number') ?>"
                },
                lines_per_page: {
                    required: "<?= lang('Config.lines_per_page_required') ?>",
                    number: "<?= lang('Config.lines_per_page_number') ?>"
                },
                gcaptcha_site_key: {
                    required: "<?= lang('Config.gcaptcha_site_key_required') ?>"
                },
                gcaptcha_secret_key: {
                    required: "<?= lang('Config.gcaptcha_secret_key_required') ?>"
                }
            },

            submitHandler: function(form) {
                $(form).ajaxSubmit({
                    beforeSerialize: function(arr, $form, options) {
                        $("#gcaptcha_site_key, #gcaptcha_secret_key").prop("disabled", false);
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
                        enable_disable_gcaptcha_enable();
                    },
                    dataType: 'json'
                });
            }
        }));
    });
</script>
