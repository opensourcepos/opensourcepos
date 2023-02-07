<?php
/**
 * @var object $person_info
 */
?>
<div class="form-group form-group-sm">
	<?php echo form_label(lang('Common.first_name'), 'first_name', ['class' => 'required control-label col-xs-3']) ?>
	<div class='col-xs-8'>
		<?php echo form_input ([
			'name' => 'first_name',
			'id' => 'first_name',
			'class' => 'form-control input-sm',
			'value' => esc($person_info->first_name, 'attr')
		]) ?>
	</div>
</div>

<div class="form-group form-group-sm">	
	<?php echo form_label(lang('Common.last_name'), 'last_name', ['class' => 'required control-label col-xs-3']) ?>
	<div class='col-xs-8'>
		<?php echo form_input ([
			'name' => 'last_name',
			'id' => 'last_name',
			'class' => 'form-control input-sm',
			'value' => esc($person_info->last_name, 'attr')
		]) ?>
	</div>
</div>

<div class="form-group form-group-sm">	
	<?php echo form_label(lang('Common.gender'), 'gender', !empty($basic_version) ? ['class' => 'required control-label col-xs-3'] : ['class' => 'control-label col-xs-3']) ?>
	<div class="col-xs-4">
		<label class="radio-inline">
			<?php echo form_radio ([
				'name' => 'gender',
				'type' => 'radio',
				'id' => 'gender',
				'value'=>1,
				'checked' => $person_info->gender === '1'
			]) ?> <?php echo lang('Common.gender_male') ?>
		</label>
		<label class="radio-inline">
			<?php echo form_radio ([
				'name' => 'gender',
				'type' => 'radio',
				'id' => 'gender',
				'value' => 0,
				'checked' => $person_info->gender === '0'
			]) ?> <?php echo lang('Common.gender_female') ?>
		</label>

	</div>
</div>

<div class="form-group form-group-sm">	
	<?php echo form_label(lang('Common.email'), 'email', ['class' => 'control-label col-xs-3']) ?>
	<div class='col-xs-8'>
		<div class="input-group">
			<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-envelope"></span></span>
			<?php echo form_input ([
				'name' => 'email',
				'id' => 'email',
				'class' => 'form-control input-sm',
				'value' => esc($person_info->email, 'attr')
			]) ?>
		</div>
	</div>
</div>

<div class="form-group form-group-sm">	
	<?php echo form_label(lang('Common.phone_number'), 'phone_number', ['class' => 'control-label col-xs-3']) ?>
	<div class='col-xs-8'>
		<div class="input-group">
			<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-phone-alt"></span></span>
			<?php echo form_input ([
				'name' => 'phone_number',
				'id' => 'phone_number',
				'class' => 'form-control input-sm',
				'value' => esc($person_info->phone_number, 'attr')
			]) ?>
		</div>
	</div>
</div>

<div class="form-group form-group-sm">	
	<?php echo form_label(lang('Common.address_1'), 'address_1', ['class' => 'control-label col-xs-3']) ?>
	<div class='col-xs-8'>
		<?php echo form_input ([
			'name' => 'address_1',
			'id' => 'address_1',
			'class' => 'form-control input-sm',
			'value' => esc($person_info->address_1, 'attr')
		]) ?>
	</div>
</div>

<div class="form-group form-group-sm">	
	<?php echo form_label(lang('Common.address_2'), 'address_2', ['class' => 'control-label col-xs-3']) ?>
	<div class='col-xs-8'>
		<?php echo form_input ([
			'name' => 'address_2',
			'id' => 'address_2',
			'class' => 'form-control input-sm',
			'value' => esc($person_info->address_2, 'attr')
		]) ?>
	</div>
</div>

<div class="form-group form-group-sm">	
	<?php echo form_label(lang('Common.city'), 'city', ['class' => 'control-label col-xs-3']) ?>
	<div class='col-xs-8'>
		<?php echo form_input ([
			'name' => 'city',
			'id' => 'city',
			'class' => 'form-control input-sm',
			'value' => esc($person_info->city, 'attr')
		]) ?>
	</div>
</div>

<div class="form-group form-group-sm">	
	<?php echo form_label(lang('Common.state'), 'state', ['class' => 'control-label col-xs-3']) ?>
	<div class='col-xs-8'>
		<?php echo form_input ([
			'name' => 'state',
			'id' => 'state',
			'class' => 'form-control input-sm',
			'value' => esc($person_info->state, 'attr')
		]) ?>
	</div>
</div>

<div class="form-group form-group-sm">	
	<?php echo form_label(lang('Common.zip'), 'zip', ['class' => 'control-label col-xs-3']) ?>
	<div class='col-xs-8'>
		<?php echo form_input ([
			'name' => 'zip',
			'id' => 'postcode',
			'class' => 'form-control input-sm',
			'value' => esc($person_info->zip, 'attr')
		]) ?>
	</div>
</div>

<div class="form-group form-group-sm">	
	<?php echo form_label(lang('Common.country'), 'country', ['class' => 'control-label col-xs-3']) ?>
	<div class='col-xs-8'>
		<?php echo form_input ([
			'name' => 'country',
			'id' => 'country',
			'class' => 'form-control input-sm',
			'value' => esc($person_info->country, 'attr')
		]) ?>
	</div>
</div>

<div class="form-group form-group-sm">	
	<?php echo form_label(lang('Common.comments'), 'comments', ['class' => 'control-label col-xs-3']) ?>
	<div class='col-xs-8'>
		<?php echo form_textarea ([
			'name' => 'comments',
			'id' => 'comments',
			'class' => 'form-control input-sm',
			'value' => esc($person_info->comments, 'attr')
		]) ?>
	</div>
</div>

<script type="text/javascript">
//validation and submit handling
$(document).ready(function()
{
	nominatim.init({
		fields : {
			postcode : {
				dependencies :  ["postcode", "city", "state", "country"],
				response : {
					field : 'postalcode',
					format: ["postcode", "village|town|hamlet|city_district|city", "state", "country"]
				}
			},

			city : {
				dependencies :  ["postcode", "city", "state", "country"],
				response : {
					format: ["postcode", "village|town|hamlet|city_district|city", "state", "country"]
				}
			},

			state : {
				dependencies :  ["state", "country"]
			},

			country : {
				dependencies :  ["state", "country"]
			}
		},
		language : '<?php echo current_language_code() ?>',
		country_codes: '<?php echo esc($config['country_codes'], 'js') ?>'
	});
});
</script>
