<?php
/**
 * @var array $support_barcode
 * @var array $config
 * @var array $barcode_fonts
 */
?>

<?= form_open('config/saveBarcode/', ['id' => 'barcode_config_form']) ?>

    <?php
    $title_info['config_title'] = lang('Config.barcode_configuration');
    echo view('configs/config_header', $title_info);
    ?>

    <ul id="barcode_error_message_box" class="error_message_box"></ul>

    <div class="row">
        <div class="col-12 col-lg-6">
            <label for="barcode_type" class="form-label"><?= lang('Config.barcode_type'); ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-braces"></i></span>
                <select class="form-select" name="barcode_type" id="barcode_type">
                    <?php foreach ($support_barcode as $key => $value): ?>
                        <option value="<?= $key ?>" <?= $config['barcode_type'] == $key ? 'selected' : '' ?>><?= $value ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <label for="barcode_width" class="form-label"><?= lang('Config.barcode_width'); ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-arrows"></i></span>
                <input type="number" class="form-control" step="5" max="350" min="60" name="barcode_width" id="barcode_width" value="<?= $config['barcode_width'] ?>" required>
                <span class="input-group-text">px</span>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <label for="barcode_height" class="form-label"><?= lang('Config.barcode_height'); ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-arrows-vertical"></i></span>
                <input type="number" class="form-control" min="10" max="120" name="barcode_height" id="barcode_height" value="<?= $config['barcode_height'] ?>" required>
                <span class="input-group-text">px</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-6 col-lg-3">
            <label for="barcode_font" class="form-label"><?= lang('Config.barcode_font'); ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-fonts"></i></span>
                <select class="form-select" id="barcode_font" name="barcode_font" required>
                    <?php foreach ($barcode_fonts as $key => $value): ?>
                        <option value="<?= $key ?>" <?= $config['barcode_font'] == $key ? 'selected' : '' ?>><?= $value ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <label for="barcode_font_size" class="form-label">Font Size<sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-arrows-angle-expand"></i></span>
                <input type="number" class="form-control" min="1" max="30" name="barcode_font_size" id="barcode_font_size" value="<?= $config['barcode_font_size'] ?>" required>
                <span class="input-group-text">px</span>
            </div>
        </div>
    </div>

    <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" role="switch" id="allow_duplicate_barcodes" name="allow_duplicate_barcodes" value="allow_duplicate_barcodes" <?= $config['allow_duplicate_barcodes'] == 1 ? 'checked' : '' ?>>
        <label class="form-check-label" for="allow_duplicate_barcodes"><?= lang('Config.allow_duplicate_barcodes'); ?></label>
        <div class="form-text"><i class="bi bi-info-square pe-1"></i><?= lang('Config.barcode_tooltip') ?></div>
    </div>

    <label for="barcode_content" class="form-label"><?= lang('Config.barcode_content'); ?></label>
    <div class="row mb-3">
        <div class="col-12">
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="barcode_content" id="barcode_content_id" value="id" <?= $config['barcode_content'] == 'id' ? 'checked' : '' ?>>
                <label class="form-check-label" for="barcode_content_id"><?= lang('Config.barcode_id') ?></label>
            </div>

            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="barcode_content" id="barcode_content_number" value="number" <?= $config['barcode_content'] == 'number' ? 'checked' : '' ?>>
                <label class="form-check-label" for="barcode_content_number"><?= lang('Config.barcode_number') ?></label>
            </div>

            <div class="form-check form-check-inline form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="barcode_generate_if_empty" name="barcode_generate_if_empty" value="barcode_generate_if_empty" <?= $config['barcode_generate_if_empty'] == 1 ? 'checked' : '' ?>>
                <label class="form-check-label" for="barcode_generate_if_empty"><?= lang('Config.barcode_generate_if_empty'); ?></label>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-6">
            <label for="barcode_formats" class="form-label"><?= lang('Config.barcode_formats'); ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-code-square"></i></span>
                <!-- <?php
                $barcode_formats = json_decode(config('OSPOS')->settings['barcode_formats']);
                $options = !empty($barcode_formats) ? array_combine($barcode_formats, $barcode_formats) : [];
                ?>
                <select class="form-select" id="barcode_formats" name="barcode_formats[]" data-role="tagsinput" multiple>
                    <?php foreach ($options as $value): ?>
                        <option value="<?= $value ?>" <?= in_array($value, $barcode_formats) ? 'selected' : '' ?>><?= $value ?></option>
                    <?php endforeach; ?>
                </select> -->
                <input type="text" class="form-control" disabled>
            </div>
        </div>
    </div>

    <div class="row">
        <label for="barcode_layout" class="form-label"><?= lang('Config.barcode_layout'); ?></label>
        <div class="col-6 col-lg-3">
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-1-square"></i></span>
                <select name="barcode_first_row" class="form-select">
                    <option value="not_show" <?= $config['barcode_first_row'] == 'not_show' ? 'selected' : '' ?>><?= lang('Config.none') ?></option>
                    <option value="name" <?= $config['barcode_first_row'] == 'name' ? 'selected' : '' ?>><?= lang('Items.name') ?></option>
                    <option value="category" <?= $config['barcode_first_row'] == 'category' ? 'selected' : '' ?>><?= lang('Items.category') ?></option>
                    <option value="cost_price" <?= $config['barcode_first_row'] == 'cost_price' ? 'selected' : '' ?>><?= lang('Items.cost_price') ?></option>
                    <option value="unit_price" <?= $config['barcode_first_row'] == 'unit_price' ? 'selected' : '' ?>><?= lang('Items.unit_price') ?></option>
                    <option value="company_name" <?= $config['barcode_first_row'] == 'company_name' ? 'selected' : '' ?>><?= lang('Suppliers.company_name') ?></option>
                </select>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-2-square"></i></span>
                <select name="barcode_second_row" class="form-select">
                    <option value="not_show" <?= $config['barcode_second_row'] == 'not_show' ? 'selected' : '' ?>><?= lang('Config.none') ?></option>
                    <option value="name" <?= $config['barcode_second_row'] == 'name' ? 'selected' : '' ?>><?= lang('Items.name') ?></option>
                    <option value="category" <?= $config['barcode_second_row'] == 'category' ? 'selected' : '' ?>><?= lang('Items.category') ?></option>
                    <option value="cost_price" <?= $config['barcode_second_row'] == 'cost_price' ? 'selected' : '' ?>><?= lang('Items.cost_price') ?></option>
                    <option value="unit_price" <?= $config['barcode_second_row'] == 'unit_price' ? 'selected' : '' ?>><?= lang('Items.unit_price') ?></option>
                    <option value="item_code" <?= $config['barcode_second_row'] == 'item_code' ? 'selected' : '' ?>><?= lang('Items.item_number') ?></option>
                    <option value="company_name" <?= $config['barcode_second_row'] == 'company_name' ? 'selected' : '' ?>><?= lang('Suppliers.company_name') ?></option>
                </select>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-3-square"></i></span>
                <select name="barcode_third_row" class="form-select">
                    <option value="not_show" <?= $config['barcode_third_row'] == 'not_show' ? 'selected' : '' ?>><?= lang('Config.none') ?></option>
                    <option value="name" <?= $config['barcode_third_row'] == 'name' ? 'selected' : '' ?>><?= lang('Items.name') ?></option>
                    <option value="category" <?= $config['barcode_third_row'] == 'category' ? 'selected' : '' ?>><?= lang('Items.category') ?></option>
                    <option value="cost_price" <?= $config['barcode_third_row'] == 'cost_price' ? 'selected' : '' ?>><?= lang('Items.cost_price') ?></option>
                    <option value="unit_price" <?= $config['barcode_third_row'] == 'unit_price' ? 'selected' : '' ?>><?= lang('Items.unit_price') ?></option>
                    <option value="item_code" <?= $config['barcode_third_row'] == 'item_code' ? 'selected' : '' ?>><?= lang('Items.item_number') ?></option>
                    <option value="company_name" <?= $config['barcode_third_row'] == 'company_name' ? 'selected' : '' ?>><?= lang('Suppliers.company_name') ?></option>
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-6 col-lg-3">
            <label for="barcode_num_in_row" class="form-label"><?= lang('Config.barcode_number_in_row'); ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-123"></i></span>
                <input type="number" min="1" max="100" name="barcode_num_in_row" id="barcode_num_in_row" class="form-control" value="<?= $config['barcode_num_in_row'] ?>" required>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <label for="barcode_page_width" class="form-label"><?= lang('Config.barcode_page_width'); ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-arrows"></i></span>
                <input type="number" min="0" max="100" name="barcode_page_width" id="barcode_page_width" class="form-control" value="<?= $config['barcode_page_width'] ?>" required>
                <span class="input-group-text"><i class="bi bi-percent"></i></span>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <label for="barcode_page_cellspacing" class="form-label"><?= lang('Config.barcode_page_cellspacing'); ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-distribute-horizontal"></i></span>
                <input type="number" min="1" max="100" name="barcode_page_cellspacing" id="barcode_page_cellspacing" class="form-control" value="<?= $config['barcode_page_cellspacing'] ?>" required>
                <span class="input-group-text">px</span>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button class="btn btn-primary" type="submit" name="submit_barcode"><?= lang('Common.submit'); ?></button>
    </div>

<?= form_close() ?>

<script type="text/javascript">
    // Validation and submit handling
    $(document).ready(function() {
        $('#barcode_config_form').validate($.extend(form_support.handler, {

            errorLabelContainer: "#barcode_error_message_box",

            rules: {
                barcode_width: {
                    required: true,
                    number: true
                },
                barcode_height: {
                    required: true,
                    number: true
                },
                barcode_font_size: {
                    required: true,
                    number: true
                },
                barcode_num_in_row: {
                    required: true,
                    number: true
                },
                barcode_page_width: {
                    required: true,
                    number: true
                },
                barcode_page_cellspacing: {
                    required: true,
                    number: true
                }
            },

            messages: {
                barcode_width: {
                    required: "<?= lang('Config.default_barcode_width_required') ?>",
                    number: "<?= lang('Config.default_barcode_width_number') ?>"
                },
                barcode_height: {
                    required: "<?= lang('Config.default_barcode_height_required') ?>",
                    number: "<?= lang('Config.default_barcode_height_number') ?>"
                },
                barcode_font_size: {
                    required: "<?= lang('Config.default_barcode_font_size_required') ?>",
                    number: "<?= lang('Config.default_barcode_font_size_number') ?>"
                },
                barcode_num_in_row: {
                    required: "<?= lang('Config.default_barcode_num_in_row_required') ?>",
                    number: "<?= lang('Config.default_barcode_num_in_row_number') ?>"
                },
                barcode_page_width: {
                    required: "<?= lang('Config.default_barcode_page_width_required') ?>",
                    number: "<?= lang('Config.default_barcode_page_width_number') ?>"
                },
                barcode_page_cellspacing: {
                    required: "<?= lang('Config.default_barcode_page_cellspacing_required') ?>",
                    number: "<?= lang('Config.default_barcode_page_cellspacing_number') ?>"
                }
            }
        }));
    });
</script>
