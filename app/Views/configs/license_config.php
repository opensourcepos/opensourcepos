<?php
/**
 * @var array $licenses
 */
?>
<?php echo form_open('', ['id' => 'license_config_form', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal']) ?>
	<div id="config_wrapper">
		<fieldset>
			<?php
			$counter = 0;
			foreach($licenses as $license)
			{
			?>
				<div class="form-group form-group-sm">
					<?php echo form_label(esc($license['title']), 'license', ['class' => 'control-label col-xs-3']) ?>
					<div class='col-xs-6'>
						<?php echo form_textarea ([
							'name' => 'license',
							'id' => 'license_' . $counter++,	//TODO: String Interpolation
							'class' => 'form-control',
							'readonly' => '',
							'value' => esc($license['text'])
						]) ?>
					</div>
				</div>
			<?php
			}
			?>
		</fieldset>
	</div>
<?php echo form_close() ?>
