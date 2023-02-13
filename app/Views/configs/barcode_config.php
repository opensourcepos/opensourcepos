<?php
/**
 * @var array $support_barcode
 */
?>
<?php echo form_open('config/save_barcode/', ['id' => 'barcode_config_form', 'class' => 'form-horizontal']) ?>
	<div id="config_wrapper">
		<fieldset id="config_info">
			<div id="required_fields_message"><?php echo lang('Common.fields_required_message') ?></div>
			<ul id="barcode_error_message_box" class="error_message_box"></ul>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.barcode_type'), 'barcode_type', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-2'>
					<?php echo form_dropdown('barcode_type', esc($support_barcode, 'attr'), esc($config['barcode_type'], 'attr'), ['class' => 'form-control input-sm']) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.barcode_width'), 'barcode_width', ['class' => 'control-label col-xs-2 required']) ?>
				<div class='col-xs-2'>
					<?php echo form_input ([
						'step' => '5',
						'max' => '350',
						'min' => '60',
						'type' => 'number',
						'name' => 'barcode_width',
						'id' => 'barcode_width',
						'class' => 'form-control input-sm required',
						'value' => $config['barcode_width']
					]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.barcode_height'), 'barcode_height', ['class' => 'control-label col-xs-2 required']) ?>
				<div class='col-xs-2'>
					<?php echo form_input ([
						'type' => 'number',
						'min' => 10,
						'max' => 120,
						'name' => 'barcode_height',
						'id' => 'barcode_height',
						'class' => 'form-control input-sm required',
						'value'=>$config['barcode_height']
					]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.barcode_font'), 'barcode_font', ['class' => 'control-label col-xs-2 required']) ?>
				<div class='col-sm-2'>
					<?php echo form_dropdown(
						'barcode_font',
						esc($this->barcode_lib->listfonts('fonts'), 'attr'),
						esc($config['barcode_font'], 'attr'),
						['class' => 'form-control input-sm required']
					) ?>
				</div>
				<div class="col-sm-2">
					<?php echo form_input ([
						'type' => 'number',
						'min' => '1',
						'max' => '30',
						'name' => 'barcode_font_size',
						'id' => 'barcode_font_size',
						'class' => 'form-control input-sm required',
						'value' => $config['barcode_font_size']
					]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.allow_duplicate_barcodes'), 'allow_duplicate_barcodes', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-1'>
					<?php echo form_checkbox ([
						'name' => 'allow_duplicate_barcodes',
						'id' => 'allow_duplicate_barcodes',
						'value' => 'allow_duplicate_barcodes',
						'checked' => $config['allow_duplicate_barcodes']
					]) ?>
					&nbsp
					<label class="control-label">
						<span class="glyphicon glyphicon-warning-sign" data-toggle="tooltip" data-placement="right" title="<?php echo lang('Config.barcode_tooltip') ?>"></span>
					</label>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.barcode_content'), 'barcode_content', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-8'>
					<label class="radio-inline">
						<?php echo form_radio ([
							'name' => 'barcode_content',
							'value' => 'id',
							'checked' => $config['barcode_content' === 'id']
						]) ?>
						<?php echo lang('Config.barcode_id') ?>
					</label>
					<label class="radio-inline">
						<?php echo form_radio ([
							'name' => 'barcode_content',
							'value' => 'number',
							'checked' => $config['barcode_content'] === 'number']) ?>
						<?php echo lang('Config.barcode_number') ?>
					</label>
					&nbsp
					&nbsp
					<label class="checkbox-inline">
						<?php echo form_checkbox ([
							'name' => 'barcode_generate_if_empty',
							'value' => 'barcode_generate_if_empty',
							'checked' => $config['barcode_generate_if_empty']
							]) ?>
						<?php echo lang('Config.barcode_generate_if_empty') ?>
					</label>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.barcode_formats'), 'barcode_formats', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-4'>
					<?php
					$barcode_formats = json_decode($config['barcode_formats']);
					echo form_dropdown ([
						'name' => 'barcode_formats[]',
						'id' => 'barcode_formats',
						'options' => !empty($barcode_formats) ? esc(array_combine($barcode_formats, $barcode_formats), 'attr') : [],
						'multiple' => 'multiple',
						'data-role' => 'tagsinput']) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.barcode_layout'), 'barcode_layout', ['class' => 'control-label col-xs-2']) ?>
				<div class="col-sm-10">
					<div class="form-group form-group-sm row">
						<label class="control-label col-sm-1"><?php echo lang('Config.barcode_first_row').' ' ?></label>
						<div class='col-sm-2'>
							<?php echo form_dropdown(
									'barcode_first_row', [
										'not_show' => lang('Config.none'),
										'name' => lang('Items.name'),
										'category' => lang('Items.category'),
										'cost_price' => lang('Items.cost_price'),
										'unit_price' => lang('Items.unit_price'),
										'company_name' => lang('Suppliers.company_name')
									],
								$config['barcode_first_row'], ['class' => 'form-control input-sm']);
							?>
						</div>
						<label class="control-label col-sm-1"><?php echo lang('Config.barcode_second_row').' ' ?></label>
						<div class='col-sm-2'>
							<?php echo form_dropdown('barcode_second_row', [
								'not_show' => lang('Config.none'),
								'name' => lang('Items.name'),
								'category' => lang('Items.category'),
								'cost_price' => lang('Items.cost_price'),
								'unit_price' => lang('Items.unit_price'),
								'item_code' => lang('Items.item_number'),
								'company_name' => lang('Suppliers.company_name')
							],
							$config['barcode_second_row'], ['class' => 'form-control input-sm']) ?>
						</div>
						<label class="control-label col-sm-1"><?php echo lang('Config.barcode_third_row').' ' ?></label>
						<div class='col-sm-2'>
							<?php echo form_dropdown('barcode_third_row', [
								'not_show' => lang('Config.none'),
								'name' => lang('Items.name'),
								'category' => lang('Items.category'),
								'cost_price' => lang('Items.cost_price'),
								'unit_price' => lang('Items.unit_price'),
								'item_code' => lang('Items.item_number'),
								'company_name' => lang('Suppliers.company_name')
							],
							$config['barcode_third_row'], ['class' => 'form-control input-sm']) ?>
						</div>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.barcode_number_in_row'), 'barcode_num_in_row', ['class' => 'control-label col-xs-2 required']) ?>
				<div class='col-xs-2'>
					<?php echo form_input ([
						'name' => 'barcode_num_in_row',
						'id' => 'barcode_num_in_row',
						'class' => 'form-control input-sm required',
						'value' => $config['barcode_num_in_row']
					]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
			<?php echo form_label(lang('Config.barcode_page_width'), 'barcode_page_width', ['class' => 'control-label col-xs-2 required']) ?>
				<div class="col-sm-2">
					<div class='input-group'>
						<?php echo form_input ([
							'name' => 'barcode_page_width',
							'id' => 'barcode_page_width',
							'class' => 'form-control input-sm required',
							'value' => $config['barcode_page_width']
							]) ?>
						<span class="input-group-addon input-sm">%</span>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
			<?php echo form_label(lang('Config.barcode_page_cellspacing'), 'barcode_page_cellspacing', ['class' => 'control-label col-xs-2 required']) ?>
				<div class='col-sm-2'>
					<div class="input-group">
						<?php echo form_input ([
							'name' => 'barcode_page_cellspacing',
							'id' => 'barcode_page_cellspacing',
							'class' => 'form-control input-sm required',
							'value' => $config['barcode_page_cellspacing']
						]) ?>
						<span class="input-group-addon input-sm">px</span>
					</div>
				</div>
			</div>

			<?php echo form_submit ([
				'name' => 'submit_barcode',
				'id' => 'submit_barcode',
				'value' => lang('Common.submit'),
				'class' => 'btn btn-primary btn-sm pull-right']) ?>
		</fieldset>
	</div>
<?php echo form_close() ?>
<script type="text/javascript">
//validation and submit handling
$(document).ready(function()
{
	$('#barcode_config_form').validate($.extend(form_support.handler, {

		errorLabelContainer: "#barcode_error_message_box",

		rules:
		{
			barcode_width:
			{
				required:true,
				number:true
			},
			barcode_height:
			{
				required:true,
				number:true
			},
			barcode_font_size:
			{
				required:true,
				number:true
			},
			barcode_num_in_row:
			{
				required:true,
				number:true
			},
			barcode_page_width:
			{
				required:true,
				number:true
			},
			barcode_page_cellspacing:
			{
				required:true,
				number:true
			}
		},

		messages:
		{
			barcode_width:
			{
				required:"<?php echo lang('Config.default_barcode_width_required') ?>",
				number:"<?php echo lang('Config.default_barcode_width_number') ?>"
			},
			barcode_height:
			{
				required:"<?php echo lang('Config.default_barcode_height_required') ?>",
				number:"<?php echo lang('Config.default_barcode_height_number') ?>"
			},
			barcode_font_size:
			{
				required:"<?php echo lang('Config.default_barcode_font_size_required') ?>",
				number:"<?php echo lang('Config.default_barcode_font_size_number') ?>"
			},
			barcode_num_in_row:
			{
				required:"<?php echo lang('Config.default_barcode_num_in_row_required') ?>",
				number:"<?php echo lang('Config.default_barcode_num_in_row_number') ?>"
			},
			barcode_page_width:
			{
				required:"<?php echo lang('Config.default_barcode_page_width_required') ?>",
				number:"<?php echo lang('Config.default_barcode_page_width_number') ?>"
			},
			barcode_page_cellspacing:
			{
				required:"<?php echo lang('Config.default_barcode_page_cellspacing_required') ?>",
				number:"<?php echo lang('Config.default_barcode_page_cellspacing_number') ?>"
			}
		}
	}));
});
</script>
