<?php
/**
 * @var array $licenses
 */
?>

<?= form_open('', ['id' => 'license_config_form', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal']) ?>
    <div id="config_wrapper">
        <fieldset>

            <?php
            $counter = 0;
            foreach ($licenses as $license) {
            ?>
                <div class="form-group form-group-sm">
                    <?= form_label($license['title'], 'license', ['class' => 'control-label col-xs-3']) ?>
                    <div class="col-xs-6">
                        <?= form_textarea([
                            'name'     => 'license',
                            'id'       => 'license_' . $counter++,    // TODO: String Interpolation
                            'class'    => 'form-control font-monospace',
                            'rows'     => '14',
                            'readonly' => '',
                            'value'    => $license['text']
                        ]) ?>
                    </div>
                </div>
            <?php } ?>

        </fieldset>
    </div>
<?= form_close() ?>
