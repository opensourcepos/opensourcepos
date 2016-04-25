<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
$(document).ready(function()
{
	// when any filter is clicked and the dropdown window is closed
	$('#filters').on('hidan.bs.select', function(e)
	{
        // reset page number when selecting a specific page number
		table_support.refresh();
	});
	
	// load the preset datarange picker
	<?php $this->load->view('partial/daterangepicker'); ?>
	// set the beginning of time as starting date
	$('#daterangepicker').data('daterangepicker').setStartDate("<?php echo date($this->config->item('dateformat'), mktime(0,0,0,01,01,2010));?>");
	// update the hidden inputs with the selected dates before submitting the search data
	var start_date = "<?php echo date('Y-m-d', mktime(0,0,0,01,01,2010));?>";
	$("#daterangepicker").on('apply.daterangepicker', function(ev, picker) {
		table_support.refresh();
	});

	table_support.init('<?php echo site_url($controller_name);?>', <?php echo $table_headers; ?>, {

		confirmDeleteMessage : '<?php echo $this->lang->line($controller_name."_confirm_delete")?>',

		loadSuccess: function(response) {
			$("#payment_summary").html(response.payment_summary);
		},

		queryParams: function() {
			return $.extend(arguments[0], {
				start_date: start_date,
				end_date: end_date,
				filters: $("#filters").val() || [""]
			});
		}
	});

});

</script>

<?php $this->load->view('partial/print_receipt', array('print_after_sale'=>false, 'selected_printer'=>'takings_printer')); ?>

<div id="title_bar" class="print_hide btn-toolbar">

	<button onclick="javascript:printdoc()" class='btn btn-info btn-sm pull-right'>
		<span class="glyphicon glyphicon-print"></span><?php echo $this->lang->line('common_print'); ?>
	</button>

</div>

<div id="toolbar">
	<div class="pull-left form-inline" role="toolbar">
		<button id="delete" class="btn btn-default btn-sm" data-href='<?php echo site_url($controller_name."/delete"); ?>'>
			<span class="glyphicon glyphicon-trash"></span>
			<?php echo $this->lang->line("common_delete");?>
		</button>

		<?php echo form_input(array('name'=>'daterangepicker', 'class'=>'form-control input-sm', 'id'=>'daterangepicker')); ?>
		<?php echo form_multiselect('filters[]', $filters, '', array('id'=>'filters', 'class'=>'selectpicker show-menu-arrow', 'data-selected-text-format'=>'count > 3', 'data-style'=>'btn-default btn-sm', 'data-width'=>'fit')); ?>
	</div>
</div>

<div id="table_holder">
	<table id="table"></table>
</div>

<div id="payment_summary">
</div>

<?php $this->load->view("partial/footer"); ?>
