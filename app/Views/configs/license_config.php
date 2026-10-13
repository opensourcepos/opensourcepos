<?php
/**
 * @var array $licenses
 */
?>

<?= form_open('', ['id' => 'license_config_form', 'enctype' => 'multipart/form-data']) ?>

    <?php
    $title_info['config_title'] = lang('Config.license_configuration');
    echo view('configs/config_header', $title_info);
    ?>

    <?php
    $license_i = 0;
    foreach ($licenses as $license) {
    ?>

        <div class="mb-3 mx-3 mx-lg-0">
            <label for="license_<?= $license_i; ?>" class="form-label"><?= $license['title']; ?></label>
            <textarea name="license" rows="10" id="license_<?= $license_i; ?>" class="form-control font-monospace" style="font-size: .875rem;" readonly><?= $license['text']; ?></textarea>
        </div>

    <?php
    $license_i++; // Increment counter
    } ?>

<?= form_close() ?>
