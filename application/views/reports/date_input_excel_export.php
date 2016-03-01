<?php $this->load->view("partial/header"); ?>
<div id="page_title" style="margin-bottom:8px;"><?php echo $this->lang->line('reports_report_input'); ?></div>
<?php
if(isset($error))
{
	echo "<div class='alert alert-dismissible alert-danger'>".$error."</div>";
}
?>
<?php echo form_open('#', array('id'=>'item_form', 'enctype'=>'multipart/form-data', 'class' => 'form-horizontal')); ?>

	<div class="form-group form-group-sm">
		<?php echo form_label($this->lang->line('reports_date_range'), 'report_date_range_label', array('class'=>'control-label col-xs-2 required')); ?>

		<div class="col-xs-4">
			<div class="radio">
				<label>
					<input type="radio" name="report_type" id="simple_radio" value='simple' checked='checked'/>
					<?php echo form_dropdown('report_date_range_simple',$report_date_range_simple, '', 'id="report_date_range_simple" class="form-control"'); ?>
				</label>
			</div>
			<div class="radio">
				<label>
					<input type="radio" name="report_type" id="complex_radio" value='complex' />
					<?php echo form_input(array('name'=>'daterangepicker', 'class'=>'form-control input-sm', 'id'=>'daterangepicker')); ?></label>
			</div>
		</div>
	</div>

	<div class="form-group form-group-sm">
		<?php echo form_label($this->lang->line('reports_sale_type'), 'reports_sale_type_label', array('class'=>'required control-label col-xs-2')); ?>
		<div id='report_sale_type' class="col-xs-3">
			<?php echo form_dropdown('sale_type', array('all' => $this->lang->line('reports_all'),
				'sales' => $this->lang->line('reports_sales'),
				'returns' => $this->lang->line('reports_returns')), 'all', 'id="input_type" class="form-control"'); ?>
		</div>
	</div>

	<div class="form-group form-group-sm">
		<?php echo form_label($this->lang->line('common_export_excel'), 'export_excel', !empty($basic_version) ? array('class'=>'control-label required col-xs-3') : array('class'=>'control-label col-xs-2')); ?>
		<div class="col-xs-4">
			<label class="radio-inline">
				<input type="radio" name="export_excel" id="export_excel_yes" value='1' /> <?php echo $this->lang->line('common_export_excel_yes'); ?>
			</label>
			<label class="radio-inline">
				<input type="radio" name="export_excel" id="export_excel_no" value='0' checked='checked' /> <?php echo $this->lang->line('common_export_excel_no'); ?>
			</label>
		</div>
	</div>

<?php
	echo form_button(array(
		'name'=>'generate_report',
		'id'=>'generate_report',
		'content'=>$this->lang->line('common_submit'),
		'class'=>'btn btn-primary btn-sm')
	);
?>

<?php echo form_close(); ?>

<?php $this->load->view("partial/footer"); ?>

<script type="text/javascript" language="javascript">
$(document).ready(function()
{
	var start_date, end_date;
	$('#daterangepicker').daterangepicker({
		locale: {
			format: '<?php echo dateformat_momentjs($this->config->item("dateformat"))?>'
		},
		startDate: "<?php echo date($this->config->item('dateformat'), time());?>",
		endDate: "<?php echo date($this->config->item('dateformat'), time());?>"
	}).on('apply.daterangepicker', function(ev, picker) {
		$("#complex_radio").attr("checked", "checked");
		start_date = picker.startDate.format('YYYY-MM-DD');
		end_date = picker.endDate.format('YYYY-MM-DD');
	});

	$("#generate_report").click(function()
	{
		var sale_type = $("#sale_type").val();
		var export_excel = 0;
		if ($("#export_excel_yes").attr('checked'))
		{
			export_excel = 1;
		}
		
		if ($("#simple_radio").attr('checked'))
		{
			window.location = window.location+'/'+$("#report_date_range_simple option:selected").val() + '/'+sale_type+'/'+export_excel;
		}
		else
		{
			window.location = window.location+'/'+start_date + '/'+ end_date + '/'+sale_type+'/'+ export_excel;
		}
	});
	
	$("#report_date_range_simple").click(function()
	{
		$("#simple_radio").attr('checked', 'checked');
	});
	
});
</script>