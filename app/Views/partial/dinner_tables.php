<?php
/**
 * @var array $dinner_tables
 */
?>
<?php
$i = 0;

foreach($dinner_tables as $table_key => $table)
{
	$dinner_table_id = $table['dinner_table_id'];
	$dinner_table_name = $table['name'];
	++$i;
?>
	<div class="form-group form-group-sm" style="<?= $table['deleted'] ? 'display:none;' : 'display:block;' ?>">
		<?= form_label(lang('Config.dinner_table') . " $i", "dinner_table_$i", ['class' => 'required control-label col-xs-2']) ?>


		<div class='col-xs-2'>
			<?php $form_data = [
					'name' => esc("dinner_table_$dinner_table_id"),
					'id' => esc("dinner_table_$dinner_table_id"),
					'class' => 'dinner_table valid_chars form-control input-sm required',
					'value' => esc($dinner_table_name)
				];
				$table['deleted'] && $form_data['disabled'] = 'disabled';
				echo form_input($form_data);
			?>
		</div>
		<span class="add_dinner_table glyphicon glyphicon-plus" style="padding-top: 0.5em;"></span>
		<span>&nbsp;&nbsp;</span>
		<span class="remove_dinner_table glyphicon glyphicon-minus" style="padding-top: 0.5em;"></span>
	</div>
<?php
}
?>
