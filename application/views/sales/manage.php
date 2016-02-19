<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
$(document).ready(function()
{
    init_table_sorting();
    enable_checkboxes();
    enable_row_selection();

	// refresh payment summaries at page bottom when a search complete takes place
	var on_complete = function(response) {
		$("#payment_summary").html(response.payment_summary);
	};

	// hook the ajax connectors on search actions, hook a on_complete action (refresh payment summaries at page bottom)
    enable_search({suggest_url: '<?php echo site_url("$controller_name/suggest"); ?>',
		confirm_search_message: '<?php echo $this->lang->line("common_confirm_search"); ?>',
		on_complete: on_complete});
    enable_delete('<?php echo $this->lang->line($controller_name."_confirm_delete"); ?>', '<?php echo $this->lang->line($controller_name."_none_selected"); ?>');

	// on any input action in the search filter section trigger a do_search
    $("#search_filter_section input").click(function() 
    {
        // reset page number when selecting a specific page number
        $('#limit_from').val("0");
        do_search(true, on_complete);
    });
	
	// accept partial suggestion to trigger a search on enter press
    $('#search').keypress(function (e) {
        if (e.which == 13) {
            $('#search_form').submit();
        }
    });

/*
	// invoice edit related functionality that is currently disabled (see html)
	var show_renumber = function() {
		var value = $("#only_invoices").val();
		var $button = $("#update_invoice_numbers").parents("li");
		$button.toggle(value === "1");
	};
	
	$("#only_invoices").change(show_renumber);
	show_renumber();

	$("#update_invoice_numbers").click(function() {
		$.ajax({url : "<?php echo site_url('sales') ?>/update_invoice_numbers", dataType: 'json', success : post_bulk_form_submit });
		return false;
	});
*/

	// set the locale language for the datetime picker
	$.fn.datetimepicker.dates['<?php echo $this->config->item("language"); ?>'] = {
		days: ["<?php echo $this->lang->line("common_days_sunday"); ?>",
				"<?php echo $this->lang->line("common_days_monday"); ?>",
				"<?php echo $this->lang->line("common_days_tueday"); ?>",
				"<?php echo $this->lang->line("common_days_wednesday"); ?>",
				"<?php echo $this->lang->line("common_days_thursday"); ?>",
				"<?php echo $this->lang->line("common_days_friday"); ?>",
				"<?php echo $this->lang->line("common_days_saturday"); ?>",
				"<?php echo $this->lang->line("common_days_sunday"); ?>"],
		daysShort: ["<?php echo $this->lang->line("common_daysshort_sunday"); ?>",
				"<?php echo $this->lang->line("common_daysshort_monday"); ?>",
				"<?php echo $this->lang->line("common_daysshort_tueday"); ?>",
				"<?php echo $this->lang->line("common_daysshort_wednesday"); ?>",
				"<?php echo $this->lang->line("common_daysshort_thursday"); ?>",
				"<?php echo $this->lang->line("common_daysshort_friday"); ?>",
				"<?php echo $this->lang->line("common_daysshort_saturday"); ?>",
				"<?php echo $this->lang->line("common_daysshort_sunday"); ?>"],
		daysMin: ["<?php echo $this->lang->line("common_daysmin_sunday"); ?>",
				"<?php echo $this->lang->line("common_daysmin_monday"); ?>",
				"<?php echo $this->lang->line("common_daysmin_tueday"); ?>",
				"<?php echo $this->lang->line("common_daysmin_wednesday"); ?>",
				"<?php echo $this->lang->line("common_daysmin_thursday"); ?>",
				"<?php echo $this->lang->line("common_daysmin_friday"); ?>",
				"<?php echo $this->lang->line("common_daysmin_saturday"); ?>",
				"<?php echo $this->lang->line("common_daysmin_sunday"); ?>"],
		months: ["<?php echo $this->lang->line("common_months_january"); ?>",
				"<?php echo $this->lang->line("common_months_february"); ?>",
				"<?php echo $this->lang->line("common_months_march"); ?>",
				"<?php echo $this->lang->line("common_months_april"); ?>",
				"<?php echo $this->lang->line("common_months_may"); ?>",
				"<?php echo $this->lang->line("common_months_june"); ?>",
				"<?php echo $this->lang->line("common_months_july"); ?>",
				"<?php echo $this->lang->line("common_months_august"); ?>",
				"<?php echo $this->lang->line("common_months_september"); ?>",
				"<?php echo $this->lang->line("common_months_october"); ?>",				
				"<?php echo $this->lang->line("common_months_november"); ?>",
				"<?php echo $this->lang->line("common_months_december"); ?>"],
		monthsShort: ["<?php echo $this->lang->line("common_monthsshort_january"); ?>",
				"<?php echo $this->lang->line("common_monthsshort_february"); ?>",
				"<?php echo $this->lang->line("common_monthsshort_march"); ?>",
				"<?php echo $this->lang->line("common_monthsshort_april"); ?>",
				"<?php echo $this->lang->line("common_monthsshort_may"); ?>",
				"<?php echo $this->lang->line("common_monthsshort_june"); ?>",
				"<?php echo $this->lang->line("common_monthsshort_july"); ?>",
				"<?php echo $this->lang->line("common_monthsshort_august"); ?>",
				"<?php echo $this->lang->line("common_monthsshort_september"); ?>",
				"<?php echo $this->lang->line("common_monthsshort_october"); ?>",				
				"<?php echo $this->lang->line("common_monthsshort_november"); ?>",
				"<?php echo $this->lang->line("common_monthsshort_december"); ?>"],
		today: "<?php echo $this->lang->line("common_today"); ?>",
		<?php
		if( strpos($this->config->item('timeformat'), 'a') !== false )
		{ 
		?>
			meridiem: ["am", "pm"],
		<?php 
		}
		else if( strpos($this->config->item('timeformat'), 'A') !== false )
		{
		?>
			meridiem: ["AM", "PM"],
		<?php 
		}
		else
		{
		?>
			meridiem: [],
		<?php 
		}
		?>
		weekStart: <?php echo $this->lang->line("common_weekstart"); ?>
	};
		
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

function show_hide_search_filter(search_filter_section, switchImgTag)
{
    var ele = document.getElementById(search_filter_section);
    var imageEle = document.getElementById(switchImgTag);
    if(ele.style.display == "block")
    {
		ele.style.display = "none";
		imageEle.innerHTML = '<img src=" <?php echo base_url(); ?>images/plus.png" style="border:0;outline:none;padding:0px;margin:0px;position:relative;top:-5px;" >';
    }
    else
    {
		ele.style.display = "block";
		imageEle.innerHTML = '<img src=" <?php echo base_url(); ?>images/minus.png" style="border:0;outline:none;padding:0px;margin:0px;position:relative;top:-5px;" >';
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
			    0: { sorter: false},
				7: { sorter: 'false'},
				8: { sorter: 'invoice_number'},
				9: { sorter: 'false'}
			},
			widgets: ['staticRow']
		});
	}
}
</script>

<div id="title_bar">
	<div id="title" class="float_left"><?php echo $this->lang->line('common_list_of').' '.$this->lang->line('sales_receipt_number'); ?></div>
	<a href="javascript:window.print()"><div class="btn btn-info btn-sm pull-right"><span><?php echo $this->lang->line('common_print'); ?></span></div></a>
</div>

<div id="pagination"><?= $links ?></div>

<div id="titleTextImg">
	<div style="float:left;vertical-align:text-top;"><?php echo $this->lang->line('common_search_options'). ': '; ?></div>
	<a id="imageDivLink" href="javascript:show_hide_search_filter('search_filter_section', 'imageDivLink');" style="outline:none;">
	<img src="<?php echo base_url().'images/plus.png'; ?>" style="border:0;outline:none;padding:0px;margin:0px;position:relative;top:-5px;"></a>
</div>

<?php echo form_open("$controller_name/search", array('id'=>'search_form')); ?>
	<div id="search_filter_section" style="display:none;">
		<?php echo form_label($this->lang->line('sales_invoice_filter').' '.':', 'invoices_filter');?>
		<?php echo form_checkbox(array('name'=>'only_invoices','id'=>'only_invoices','value'=>1,'checked'=> isset($only_invoices)?  ( ($only_invoices)? 1 : 0) : 0)) . ' | ';?>
		<?php echo form_label($this->lang->line('sales_cash_filter').' '.':', 'cash_filter');?>
		<?php echo form_checkbox(array('name'=>'only_cash','id'=>'only_cash','value'=>1,'checked'=> isset($only_cash)?  ( ($only_cash)? 1 : 0) : 0)) . ' | ';?>

		<?php echo form_label($this->lang->line('sales_date_range').' :', 'start_date');?>
		<?php echo form_input(array('name'=>'start_date', 'value'=>$start_date, 'class'=>'date_filter', 'size' => '22'));?>
		<?php echo form_label(' - ', 'end_date');?>
		<?php echo form_input(array('name'=>'end_date', 'value'=>$end_date, 'class'=>'date_filter', 'size' => '22'));?>
	</div>

	<div id="table_action_header">
		<ul>
			<li class="float_left"><span><?php echo anchor($controller_name . "/delete",$this->lang->line("common_delete"),array('id'=>'delete')); ?></span></li>
			<!-- li class="float_left"><span><?php echo anchor($controller_name . "/update_invoice_numbers", $this->lang->line('sales_invoice_update'),array('id'=>'update_invoice_numbers')); ?></span></li -->
			<li class="float_right">
				<img src='<?php echo base_url(); ?>images/spinner_small.gif' alt='spinner' id='spinner' />
				<input type="text" name ='search' id='search'/>
				<input type="hidden" name ='limit_from' id='limit_from'/>
			</li>
		</ul>
	</div>
<?php echo form_close(); ?>

<div id="table_holder" class="totals">
	<?php echo $manage_table; ?>
</div>

<div id="payment_summary">
	<?php echo $payments_summary; ?>
</div>

<?php $this->load->view("partial/footer"); ?>
