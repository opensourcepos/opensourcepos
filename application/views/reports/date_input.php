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
	
<?php
	if($mode == 'sale')
	{
?>
	<div class="form-group form-group-sm">
		<?php echo form_label($this->lang->line('reports_sale_type'), 'reports_sale_type_label', array('class'=>'required control-label col-xs-2')); ?>
		<div id='report_sale_type' class="col-xs-3">
			<?php echo form_dropdown('sale_type', array('all' => $this->lang->line('reports_all'),
			'sales' => $this->lang->line('reports_sales'),
			'returns' => $this->lang->line('reports_returns')), 'all', 'id="input_type" class="form-control"'); ?>
		</div>
	</div>
<?php
}
elseif($mode == 'receiving')
{
?>
	<div class="form-group form-group-sm">
		<?php echo form_label($this->lang->line('reports_receiving_type'), 'reports_receiving_type_label', array('class'=>'required control-label col-xs-2')); ?>
		<div id='report_receiving_type' class="col-xs-3">
			<?php echo form_dropdown('receiving_type', array('all' => $this->lang->line('reports_all'),
				'receiving' => $this->lang->line('reports_receivings'),
				'returns' => $this->lang->line('reports_returns'),
				'requisitions' => $this->lang->line('reports_requisitions')), 'all', 'id="input_type" class="form-control"'); ?>
		</div>
	</div>
<?php
}
if (!empty($stock_locations) && count($stock_locations) > 1)
{
?>
	<div class="form-group form-group-sm">
		<?php echo form_label($this->lang->line('reports_stock_location'), 'reports_stock_location_label', array('class'=>'required control-label col-xs-2')); ?>
		<div id='report_stock_location' class="col-xs-3">
			<?php echo form_dropdown('stock_location',$stock_locations,'all','id="location_id" class="form-control"'); ?>
		</div>
	</div>
<?php
}
?>

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
		var input_type = $("#input_type").val();
		var location_id = $("#location_id").val();
		var location = window.location;
		if ($("#simple_radio").attr('checked'))
		{
			location += '/'+$("#report_date_range_simple option:selected").val() + '/' + input_type;
		}
		else
		{
	        if(!input_type)
	        {
	            location += '/'+start_date + '/'+ end_date;
	        }
	        else
	        {
				location += '/'+start_date + '/'+ end_date+ '/' + input_type;
			}
		}
		if (location_id)
		{
			location += '/' + location_id;
		}
		window.location = location;
	});

	$("#report_date_range_simple").click(function()
	{
		$("#simple_radio").attr('checked', 'checked');
	});
	
});
</script>