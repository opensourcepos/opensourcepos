var start_date = "<?php echo date('Y-m-d') ?>";
var end_date   = "<?php echo date('Y-m-d') ?>";

$('#daterangepicker').daterangepicker({
	"ranges": {
		"<?php echo $this->lang->line("datepicker_today"); ?>": [
			"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d"),date("Y")));?>",
			"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d")+1,date("Y"))-1);?>"
		],
		"<?php echo $this->lang->line("datepicker_today_last_year"); ?>": [
			"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d"),date("Y")-1));?>",
			"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d")+1,date("Y")-1)-1);?>"
		],
		"<?php echo $this->lang->line("datepicker_yesterday"); ?>": [
			"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d")-1,date("Y")));?>",
			"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d"),date("Y"))-1);?>"
		],
		"<?php echo $this->lang->line("datepicker_last_7"); ?>": [
			"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d")-6,date("Y")));?>",
			"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d")+1,date("Y"))-1);?>"
		],
		"<?php echo $this->lang->line("datepicker_last_30"); ?>": [
			"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d")-29,date("Y")));?>",
			"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d")+1,date("Y"))-1);?>"
		],
		"<?php echo $this->lang->line("datepicker_this_month"); ?>": [
			"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),1,date("Y")));?>",
			"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m")+1,1,date("Y"))-1);?>"
		],
		 "<?php echo $this->lang->line("datepicker_this_month_to_today_last_year"); ?>": [
			"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),1,date("Y")-1));?>",
			"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d")+1,date("Y")-1)-1);?>"
		],
		 "<?php echo $this->lang->line("datepicker_this_month_last_year"); ?>": [
			"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),1,date("Y")-1));?>",
			"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m")+1,1,date("Y")-1)-1);?>"
		],
		 "<?php echo $this->lang->line("datepicker_last_month"); ?>": [
			"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m")-1,1,date("Y")));?>",
			"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),1,date("Y"))-1);?>"
		],
		"<?php echo $this->lang->line("datepicker_this_year"); ?>": [
			"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,1,1,date("Y")));?>",
			"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),1,date("Y")+1)-1);?>"
		],
		 "<?php echo $this->lang->line("datepicker_last_year"); ?>": [
			"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,1,1,date("Y")-1));?>",
			"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,1,1,date("Y"))-1);?>"
		],
		 "<?php echo $this->lang->line("datepicker_all_time"); ?>": [
			"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,01,01,2010));?>",
			"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d")+1,date("Y"))-1);?>"
		],
	},
	"locale": {
		"format": '<?php echo dateformat_momentjs($this->config->item("dateformat"))?>',
		"separator": " - ",
		"applyLabel": "<?php echo $this->lang->line("datepicker_apply"); ?>",
		"cancelLabel": "<?php echo $this->lang->line("datepicker_cancel"); ?>",
		"fromLabel": "<?php echo $this->lang->line("datepicker_from"); ?>",
		"toLabel": "<?php echo $this->lang->line("datepicker_to"); ?>",
		"customRangeLabel": "<?php echo $this->lang->line("datepicker_custom"); ?>",
		"daysOfWeek": [
			"<?php echo $this->lang->line("datepicker_daysmin_sunday"); ?>",
			"<?php echo $this->lang->line("datepicker_daysmin_monday"); ?>",
			"<?php echo $this->lang->line("datepicker_daysmin_tueday"); ?>",
			"<?php echo $this->lang->line("datepicker_daysmin_wednesday"); ?>",
			"<?php echo $this->lang->line("datepicker_daysmin_thursday"); ?>",
			"<?php echo $this->lang->line("datepicker_daysmin_friday"); ?>",
			"<?php echo $this->lang->line("datepicker_daysmin_saturday"); ?>",
			"<?php echo $this->lang->line("datepicker_daysmin_sunday"); ?>"
		],
		"monthNames": [
			"<?php echo $this->lang->line("datepicker_months_january"); ?>",
			"<?php echo $this->lang->line("datepicker_months_february"); ?>",
			"<?php echo $this->lang->line("datepicker_months_march"); ?>",
			"<?php echo $this->lang->line("datepicker_months_april"); ?>",
			"<?php echo $this->lang->line("datepicker_months_may"); ?>",
			"<?php echo $this->lang->line("datepicker_months_june"); ?>",
			"<?php echo $this->lang->line("datepicker_months_july"); ?>",
			"<?php echo $this->lang->line("datepicker_months_august"); ?>",
			"<?php echo $this->lang->line("datepicker_months_september"); ?>",
			"<?php echo $this->lang->line("datepicker_months_october"); ?>",
			"<?php echo $this->lang->line("datepicker_months_november"); ?>",
			"<?php echo $this->lang->line("datepicker_months_december"); ?>"
		],
		"firstDay": <?php echo $this->lang->line("datepicker_weekstart"); ?>
	},
	"alwaysShowCalendars": true,
	"startDate": "<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d")+1,date("Y"))-1);?>",
	"endDate": "<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d")+1,date("Y"))-1);?>",
	"minDate": "<?php echo date($this->config->item('dateformat'), mktime(0,0,0,01,01,2010));?>",
	"maxDate": "<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d")+1,date("Y"))-1);?>"
}, function(start, end, label) {
	start_date = start.format('YYYY-MM-DD');
	end_date = end.format('YYYY-MM-DD');
});