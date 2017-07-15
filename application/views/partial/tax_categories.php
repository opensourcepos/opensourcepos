<?php
$i = 0;

foreach($tax_categories as $tax_category => $category)
{
	$tax_category_id = $category['tax_category_id'];
	$tax_category = $category['tax_category'];
	$tax_group_sequence = $category['tax_group_sequence'];
	++$i;
?>
	<div class="form-group form-group-sm" style="display:block;" >
		<?php echo form_label($this->lang->line('config_tax_category') . ' ' . $i, 'tax_category_' . $i, array('class'=>'control-label col-xs-2')); ?>
		<div class='col-xs-2'>
			<?php $form_data = array(
					'name'=>'tax_category_' . $tax_category_id,
					'id'=>'tax_category_' . $tax_category_id,
					'class'=>'valid_chars form-control input-sm required',
					'value'=>$tax_category
				);
				echo form_input($form_data);
			?>
		</div>
        <div class='col-xs-2'>
			<?php $form_data = array(
				'name'=>'tax_group_sequence_' . $tax_category_id,
				'id'=>'tax_group_sequence_' . $tax_category_id,
				'class'=>'valid_chars form-control input-sm required',
				'value'=>$tax_group_sequence
			);
			echo form_input($form_data);
			?>
        </div>
		<span class="add_tax_category glyphicon glyphicon-plus" style="padding-top: 0.5em;"></span>
		<span>&nbsp;&nbsp;</span>
        <?php
        if($tax_category_id > 0) {
        ?>
		    <span class="remove_tax_category glyphicon glyphicon-minus" style="padding-top: 0.5em;"></span>
        <?php
		}
        ?>
	</div>
<?php
}
?>
