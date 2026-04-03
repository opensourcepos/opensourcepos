<?php
/**
 * @var array $dinner_tables
 */
?>

<span class="d-flex justify-content-start add_dinner_table">
    <button class="btn btn-outline-success mb-3" type="button"><i class="bi bi-plus-lg"></i>&nbsp;<?= lang('Config.dinner_table_add') ?></button>
</span>

<?php
$i = 0;

foreach ($dinner_tables as $table_key => $table) {
    $dinner_table_id = $table['dinner_table_id'];
    $dinner_table_name = $table['name'];
    ++$i;
?>

    <div class="col-12 col-lg-6 <?= $table['deleted'] ? 'd-none' : '' ?>">
        <label for="dinner_table_<?= (int) $dinner_table_id ?>" class="form-label"><?= lang('Config.dinner_table') . " $i"; ?></label>
        <div class="input-group mb-3">
            <span class="input-group-text"><?= (int) $dinner_table_id ?>.</span>
            <input type="text" class="form-control dinner_table valid_chars" name="dinner_table_<?= (int) $dinner_table_id ?>" id="dinner_table_<?= (int) $dinner_table_id ?>" value="<?= esc($dinner_table_name) ?>" required <?= $table['deleted'] ? 'disabled' : '' ?>>
            <button class="btn btn-outline-danger remove_dinner_table" type="button"><i class="bi bi-x-lg"></i></button>
        </div>
    </div>

<?php } ?>
