<?php
/**
 * @var array $tax_categories
 */
?>

<?php
$i = 0;

foreach ($tax_categories as $key => $category) {
    $tax_category_id = $category['tax_category_id'];
    $tax_category = $category['tax_category'];
    $tax_group_sequence = $category['tax_group_sequence'];
    ++$i;
?>

    <div class="row mb-3" style="display: block;">
        <?= form_label(lang('Taxes.tax_category') . " $i", "tax_category_$i", ['class' => 'col-form-label col-2']) ?>
        <div class="col-3">
            <?php $form_data = [
                'name'        => 'tax_category[]',
                'id'          => "tax_category_$i",
                'class'       => 'valid_chars form-control input-sm',
                'placeholder' => lang('Taxes.tax_category_name'),
                'value'       => $tax_category
            ];
            echo form_input($form_data);
            ?>
        </div>
        <div class="col-2">
            <?php $form_data = [
                'name'        => 'tax_group_sequence[]',
                'class'       => 'valid_chars form-control input-sm',
                'placeholder' => lang('Taxes.sequence'),
                'value'       => $tax_group_sequence
            ];
            echo form_input($form_data);
            ?>
        </div>
        <span class="add_tax_category bi bi-plus-circle" style="padding-top: 0.5em; display: inline-block; cursor: pointer;"></span>
        <span>&nbsp;&nbsp;</span>
        <span class="remove_tax_category bi bi-dash-circle" style="padding-top: 0.5em; display: inline-block; cursor: pointer;"></span>
        <?= form_hidden('tax_category_id[]', (string)$tax_category_id) ?>
    </div>

<?php } ?>
