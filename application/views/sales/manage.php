<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
$(document).ready(function()
{
    init_table_sorting();
    enable_checkboxes();
    enable_row_selection();

	// refresh payment summaries at page bottom when a search complete takes place
	var on_complete = function(response)
	{
		$("#payment_summary").html(response.payment_summary);
	};

	// hook the ajax connectors on search actions, hook a on_complete action (refresh payment summaries at page bottom)
    enable_search({suggest_url: '<?php echo site_url("$controller_name/suggest_search"); ?>',
		confirm_search_message: "<?php echo $this->lang->line("common_confirm_search"); ?>",
		on_complete: on_complete});
    enable_delete("<?php echo $this->lang->line($controller_name."_confirm_delete")?>","<?php echo $this->lang->line($controller_name."_none_selected")?>");

	// when any filter is clicked and the dropdown window is closed
	$('#filters').on('hidan.bs.select', function(e)
	{
        // reset page number when selecting a specific page number
        $('#limit_from').val("0");
        do_search(true, on_complete);
	});
	
	//$('#filters').selectpicker('val', [<?php echo "'" . implode("','", $selected) . "'" ?>]);
	
	// accept partial suggestion to trigger a search on enter press
    $('#search').keypress(function (e)
	{
        if (e.which == 13)
		{
            $('#search_form').submit();
        }
    });

/*
	// invoice edit related functionality that is currently disabled (see html)
	var show_renumber = function()
	{
		var value = $("#only_invoices").val();
		var $button = $("#update_invoice_numbers").parents("li");
		$button.toggle(value === "1");
	};
	
	$("#only_invoices").change(show_renumber);
	show_renumber();

	$("#update_invoice_numbers").click(function()
	{
		$.ajax({url : "<?php echo site_url('sales') ?>/update_invoice_numbers", dataType: 'json', success : post_bulk_form_submit });
		return false;
	});
*/

	<?php $this->load->view('partial/datepicker_locale'); ?>

	// initialise the datetime picker and trigger a search on any change of date
	$(".date_filter").datetimepicker({
		format: "<?php echo dateformat_bootstrap($this->config->item("dateformat")) . ' ' . dateformat_bootstrap($this->config->item("timeformat"));?>",
		startDate: "<?php echo date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), mktime(0, 0, 0, 1, 1, 2010));?>",
		<?php
		$t = $this->config->item('timeformat');
		$m = $t[strlen($t)-1];
		if( strpos($this->config->item('timeformat'), 'a') !== false || strpos($this->config->item('timeformat'), 'A') !== false )
		{ 
		?>
			showMeridian: true,
		<?php 
		}
		else
		{
		?>
			showMeridian: false,
		<?php 
		}
		?>
		autoclose: true,
		todayBtn: true,
		todayHighlight: true,
		bootcssVer: 3,
		language: "<?php echo $this->config->item('language'); ?>"
	}).on('changeDate', function(event) {
        // reset page number when selecting a specific page number
        $('#limit_from').val("0");
		do_search(true, on_complete);
		return false;
	});
});

function post_form_submit(response)
{
	if(!response.success)
	{
		set_feedback(response.message, 'alert alert-dismissible alert-danger', true);
	}
	else
	{
		update_row(response.id,'<?php echo site_url("$controller_name/get_row"); ?>');
		set_feedback(response.message, 'alert alert-dismissible alert-success', false);
	}
}

function post_bulk_form_submit(response)
{
	if(!response.success)
	{
		set_feedback(response.message, 'alert alert-dismissible alert-danger', true);
	}
	else
	{
		for(id in response.ids)
		{
			update_row(response.ids[id],'<?php echo site_url("$controller_name/get_row"); ?>');
		}
		set_feedback(response.message, 'alert alert-dismissible alert-success', false);
	}
}
    
function init_table_sorting()
{
	$.tablesorter.addParser({
	    id: "datetime",
	    is: function(s) {
	        return false; 
	    },
	    format: function(s,table) {
	        s = s.replace(/\-/g,"/");
	        s = s.replace(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})\s(\d{1,2})\:(\d{2})/, "$3/$2/$1 $4:$5");
	        return $.tablesorter.formatFloat(new Date(s).getTime());
	    },
	    type: "numeric"
	});

	$.tablesorter.addParser({
		id: "invoice_number",
		is: function(s) {
			return false;
		},
		format: function(s,table) {
			s = s.split(/[\/\-]/);
			if (s.length == 2 && s[0].match(/[12]\d{3}/g))
			{
				return $.tablesorter.formatFloat(new Date(s[0]).getTime() + s[1]);
			}
			return $.tablesorter.formatFloat(s);
		},
		type: "numeric"
	});

	$.tablesorter.addParser({
		id: "receipt_number",
		is: function(s) {
			return false;
		},
		format: function(s,table) {
			s = s.split(/[\s]/);
			if (s.length == 2 && s[1].match(/\d+/g))
			{
				return $.tablesorter.formatFloat(s[1]);
			}
			return s;
		},
		type: "numeric"
	});
		
	//Only init if there is more than one row
	if($('.tablesorter tbody tr').length > 1)
	{
		$("#sortable_table").tablesorter(
		{
			sortList: [[1,0]],
			dateFormat: '<?php echo dateformat_jquery($this->config->item('dateformat')); ?>',
			headers:
			{
			    0: { sorter: 'false'},
				7: { sorter: 'false'},
				8: { sorter: 'invoice_number'},
				9: { sorter: 'false'},
				10: { sorter: 'false'},
				11: { sorter: 'false'}
			},
			widgets: ['staticRow']
		});
	}
}
</script>

<?php $this->load->view('partial/print_receipt', array('print_after_sale'=>false, 'selected_printer'=>'takings_printer')); ?>

<div id="title_bar" class="print_hide">
	<div id="pagination" class="pull-left"><?php echo $links; ?></div>

	<a href="javascript:printdoc();"><div class="btn btn-info btn-sm pull-right"><?php echo $this->lang->line('common_print'); ?></div></a>
</div>

<?php echo form_open("$controller_name/search", array('id'=>'search_form', 'class'=>'form-horizontal')); ?>
	<fieldset>
		<div id="table_action_header" class="form-group">
			<ul>
				<li class="pull-left print_hide"><?php echo anchor($controller_name . "/delete", '<div class="btn btn-default btn-sm"><span>' . $this->lang->line("common_delete") . '</span></div>', array('id'=>'delete')); ?></li>
				<!-- li class="pull-left print_hide"><?php echo anchor($controller_name . "/update_invoice_numbers", '<div class="btn btn-default btn-sm"><span>' . $this->lang->line('sales_invoice_update') . '</span></div>', array('id'=>'update_invoice_numbers')); ?></li -->
				
				<li class="pull-right print_hide">
					<?php echo form_input(array('name'=>'search', 'class'=>'form-control input-sm', 'id'=>'search')); ?>
					<?php echo form_input(array('name'=>'limit_from', 'type'=>'hidden', 'id'=>'limit_from')); ?>
				</li>
				<li class="pull-right print_show"><?php echo form_input(array('name'=>'end_date', 'value'=>$end_date, 'class'=>'date_filter form-control input-sm'));?></li>
				<li class="pull-right print_show"><?php echo form_label('-', 'end_date');?></li>
				<li class="pull-right print_show"><?php echo form_input(array('name'=>'start_date', 'value'=>$start_date, 'class'=>'date_filter form-control input-sm'));?></li>
				<li class="pull-right print_show"><?php echo form_label($this->lang->line('sales_date_range').':', 'start_date');?></li>
				<li class="pull-right print_show"><div id="multi_filter"><?php echo form_multiselect('filters[]', $filters, '', array('id'=>'filters', 'class'=>'selectpicker show-menu-arrow', 'data-selected-text-format'=>'count > 3', 'data-style'=>'btn-default btn-sm', 'data-width'=>'fit')); ?></div></li>
			</ul>
		</div>
	</fieldset>
<?php echo form_close(); ?>

<div id="table_holder" class="totals">
	<?php echo $manage_table; ?>
</div>

<div id="payment_summary">
	<?php echo $payments_summary; ?>
</div>

<?php $this->load->view("partial/footer"); ?>
