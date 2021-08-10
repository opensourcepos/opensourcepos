<?php $this->lang->load("calendar"); $this->lang->load("date"); 
	if(empty(config('OSPOS')->date_or_time_format))
	{
?>
		$('#daterangepicker').css("width","180");
		var start_date = "<?php echo date('Y-m-d') ?>";
		var end_date   = "<?php echo date('Y-m-d') ?>";

		$('#daterangepicker').daterangepicker({
			"ranges": {
				"<?php echo lang('Datepicker.today') ?>": [
					"<?php echo date(config('OSPOS')->dateformat, mktime(0,0,0,date("m"),date("d"),date("Y"))) ?>",
					"<?php echo date(config('OSPOS')->dateformat, mktime(0,0,0,date("m"),date("d")+1,date("Y"))-1) ?>"
				],
				"<?php echo lang('Datepicker.today_last_year') ?>": [
					"<?php echo date(config('OSPOS')->dateformat, mktime(0,0,0,date("m"),date("d"),date("Y")-1)) ?>",
					"<?php echo date(config('OSPOS')->dateformat, mktime(0,0,0,date("m"),date("d")+1,date("Y")-1)-1) ?>"
				],
				"<?php echo lang('Datepicker.yesterday') ?>": [
					"<?php echo date(config('OSPOS')->dateformat, mktime(0,0,0,date("m"),date("d")-1,date("Y"))) ?>",
					"<?php echo date(config('OSPOS')->dateformat, mktime(0,0,0,date("m"),date("d"),date("Y"))-1) ?>"
				],
				"<?php echo lang('Datepicker.last_7') ?>": [
					"<?php echo date(config('OSPOS')->dateformat, mktime(0,0,0,date("m"),date("d")-6,date("Y"))) ?>",
					"<?php echo date(config('OSPOS')->dateformat, mktime(0,0,0,date("m"),date("d")+1,date("Y"))-1) ?>"
				],
				"<?php echo lang('Datepicker.last_30') ?>": [
					"<?php echo date(config('OSPOS')->dateformat, mktime(0,0,0,date("m"),date("d")-29,date("Y"))) ?>",
					"<?php echo date(config('OSPOS')->dateformat, mktime(0,0,0,date("m"),date("d")+1,date("Y"))-1) ?>"
				],
				"<?php echo lang('Datepicker.this_month') ?>": [
					"<?php echo date(config('OSPOS')->dateformat, mktime(0,0,0,date("m"),1,date("Y"))) ?>",
					"<?php echo date(config('OSPOS')->dateformat, mktime(0,0,0,date("m")+1,1,date("Y"))-1) ?>"
				],
				"<?php echo lang('Datepicker.same_month_to_same_day_last_year') ?>": [
					"<?php echo date(config('OSPOS')->dateformat, mktime(0,0,0,date("m"),1,date("Y")-1)) ?>",
					"<?php echo date(config('OSPOS')->dateformat, mktime(0,0,0,date("m"),date("d")+1,date("Y")-1)-1) ?>"
				],
				"<?php echo lang('Datepicker.same_month_last_year') ?>": [
					"<?php echo date(config('OSPOS')->dateformat, mktime(0,0,0,date("m"),1,date("Y")-1)) ?>",
					"<?php echo date(config('OSPOS')->dateformat, mktime(0,0,0,date("m")+1,1,date("Y")-1)-1) ?>"
				],
				"<?php echo lang('Datepicker.last_month') ?>": [
					"<?php echo date(config('OSPOS')->dateformat, mktime(0,0,0,date("m")-1,1,date("Y"))) ?>",
					"<?php echo date(config('OSPOS')->dateformat, mktime(0,0,0,date("m"),1,date("Y"))-1) ?>"
				],
				"<?php echo lang('Datepicker.this_year') ?>": [
					"<?php echo date(config('OSPOS')->dateformat, mktime(0,0,0,1,1,date("Y"))) ?>",
					"<?php echo date(config('OSPOS')->dateformat, mktime(0,0,0,date("m"),1,date("Y")+1)-1) ?>"
				],
				"<?php echo lang('Datepicker.last_year') ?>": [
					"<?php echo date(config('OSPOS')->dateformat, mktime(0,0,0,1,1,date("Y")-1)) ?>",
					"<?php echo date(config('OSPOS')->dateformat, mktime(0,0,0,1,1,date("Y"))-1) ?>"
				],
				"<?php echo lang('Datepicker.this_financial_year') ?>": [
					"<?php echo date(config('OSPOS')->dateformat, mktime(0,0,0,config('OSPOS')->financial_year,1,date("Y"))) ?>",
					"<?php echo date(config('OSPOS')->dateformat, mktime(0,0,0,date("m"),1,date("Y")+1)-1) ?>"
				],
				"<?php echo lang('Datepicker.last_financial_year') ?>": [
					"<?php echo date(config('OSPOS')->dateformat, mktime(0,0,0,config('OSPOS')->financial_year,1,date("Y")-1)) ?>",
					"<?php echo date(config('OSPOS')->dateformat, mktime(0,0,0,config('OSPOS')->financial_year,1,date("Y"))-1) ?>"
				],
				"<?php echo lang('Datepicker.all_time') ?>": [
					"<?php echo date(config('OSPOS')->dateformat, mktime(0,0,0,1,1,2010)) ?>",
					"<?php echo date(config('OSPOS')->dateformat, mktime(0,0,0,date("m"),date("d")+1,date("Y"))-1) ?>"
				],
			},
			"locale": {
				"format": '<?php echo dateformat_momentjs(config('OSPOS')->dateformat) ?>',
				"separator": " - ",
				"applyLabel": "<?php echo lang('Datepicker.apply') ?>",
				"cancelLabel": "<?php echo lang('Datepicker.cancel') ?>",
				"fromLabel": "<?php echo lang('Datepicker.from') ?>",
				"toLabel": "<?php echo lang('Datepicker.to') ?>",
				"customRangeLabel": "<?php echo lang('Datepicker.custom') ?>",
				"daysOfWeek": [
					"<?php echo lang('Cal.su') ?>",
					"<?php echo lang('Cal.mo') ?>",
					"<?php echo lang('Cal.tu') ?>",
					"<?php echo lang('Cal.we') ?>",
					"<?php echo lang('Cal.th') ?>",
					"<?php echo lang('Cal.fr') ?>",
					"<?php echo lang('Cal.sa') ?>",
					"<?php echo lang('Cal.su') ?>"
				],
				"monthNames": [
					"<?php echo lang('Cal.january') ?>",
					"<?php echo lang('Cal.february') ?>",
					"<?php echo lang('Cal.march') ?>",
					"<?php echo lang('Cal.april') ?>",
					"<?php echo lang('Cal.may') ?>",
					"<?php echo lang('Cal.june') ?>",
					"<?php echo lang('Cal.july') ?>",
					"<?php echo lang('Cal.august') ?>",
					"<?php echo lang('Cal.september') ?>",
					"<?php echo lang('Cal.october') ?>",
					"<?php echo lang('Cal.november') ?>",
					"<?php echo lang('Cal.december') ?>"
				],
				"firstDay": <?php echo lang('Datepicker.weekstart') ?>
			},
			"alwaysShowCalendars": true,
			"startDate": "<?php echo date(config('OSPOS')->dateformat, mktime(0,0,0,date("m"),date("d")+1,date("Y"))-1) ?>",
			"endDate": "<?php echo date(config('OSPOS')->dateformat, mktime(0,0,0,date("m"),date("d")+1,date("Y"))-1) ?>",
			"minDate": "<?php echo date(config('OSPOS')->dateformat, mktime(0,0,0,01,01,2010)) ?>",
			"maxDate": "<?php echo date(config('OSPOS')->dateformat, mktime(0,0,0,date("m"),date("d")+1,date("Y"))-1) ?>"
		}, function(start, end, label) {
			start_date = start.format('YYYY-MM-DD');
			end_date = end.format('YYYY-MM-DD');
		});
<?php
	}
	else
	{
?>
		$('#daterangepicker').css("width","305");
		var start_date = "<?php echo date('Y-m-d H:i:s', mktime(0,0,0,date("m"),date("d"),date("Y"))) ?>";
		var end_date = "<?php echo date('Y-m-d H:i:s', mktime(23,59,59,date("m"),date("d"),date("Y"))) ?>";
		$('#daterangepicker').daterangepicker({
			"ranges": {
				"<?php echo lang('Datepicker.today') ?>": [
					"<?php echo date(config('OSPOS')->dateformat." ".config('OSPOS')->dateformat, mktime(0,0,0,date("m"),date("d"),date("Y"))) ?>",
					"<?php echo date(config('OSPOS')->dateformat." ".config('OSPOS')->timeformat,mktime(23,59,59,date("m"),date("d"),date("Y"))) ?>"
				],
				"<?php echo lang('Datepicker.today_last_year') ?>": [
					"<?php echo date(config('OSPOS')->dateformat." ".config('OSPOS')->timeformat,mktime(0,0,0,date("m"),date("d"),date("Y")-1)) ?>",
					"<?php echo date(config('OSPOS')->dateformat." ".config('OSPOS')->timeformat,mktime(23,59,59,date("m"),date("d"),date("Y")-1)) ?>"
				],
				"<?php echo lang('Datepicker.yesterday') ?>": [
					"<?php echo date(config('OSPOS')->dateformat." ".config('OSPOS')->timeformat,mktime(0,0,0,date("m"),date("d")-1,date("Y"))) ?>",
					"<?php echo date(config('OSPOS')->dateformat." ".config('OSPOS')->timeformat,mktime(23,59,59,date("m"),date("d")-1,date("Y"))) ?>"
				],
				"<?php echo lang('Datepicker.last_7') ?>": [
					"<?php echo date(config('OSPOS')->dateformat." ".config('OSPOS')->timeformat,mktime(0,0,0,date("m"),date("d")-6,date("Y"))) ?>",
					"<?php echo date(config('OSPOS')->dateformat." ".config('OSPOS')->timeformat,mktime(23,59,59,date("m"),date("d"),date("Y"))) ?>"
				],
				"<?php echo lang('Datepicker.last_30') ?>": [
					"<?php echo date(config('OSPOS')->dateformat." ".config('OSPOS')->timeformat,mktime(0,0,0,date("m"),date("d")-29,date("Y"))) ?>",
					"<?php echo date(config('OSPOS')->dateformat." ".config('OSPOS')->timeformat,mktime(23,59,59,date("m"),date("d"),date("Y"))) ?>"
				],
				"<?php echo lang('Datepicker.this_month') ?>": [
					"<?php echo date(config('OSPOS')->dateformat." ".config('OSPOS')->timeformat,mktime(0,0,0,date("m"),1,date("Y"))) ?>",
					"<?php echo date(config('OSPOS')->dateformat." ".config('OSPOS')->timeformat,mktime(23,59,59,date("m"),date("d"),date("Y"))) ?>"
				],
				"<?php echo lang('Datepicker.same_month_to_same_day_last_year') ?>": [
					"<?php echo date(config('OSPOS')->dateformat." ".config('OSPOS')->timeformat,mktime(0,0,0,date("m"),1,date("Y")-1)) ?>",
					"<?php echo date(config('OSPOS')->dateformat." ".config('OSPOS')->timeformat,mktime(23,59,59,date("m"),date("d"),date("Y")-1)) ?>"
				],
				"<?php echo lang('Datepicker.same_month_last_year') ?>": [
					"<?php echo date(config('OSPOS')->dateformat." ".config('OSPOS')->timeformat,mktime(0,0,0,date("m"),1,date("Y")-1)) ?>",
					"<?php echo date(config('OSPOS')->dateformat." ".config('OSPOS')->timeformat,mktime(23,59,59,date("m")+1,0,date("Y")-1)) ?>"
				],
				"<?php echo lang('Datepicker.last_month') ?>": [
					"<?php echo date(config('OSPOS')->dateformat." ".config('OSPOS')->timeformat,mktime(0,0,0,date("m")-1,1,date("Y"))) ?>",
					"<?php echo date(config('OSPOS')->dateformat." ".config('OSPOS')->timeformat,mktime(23,59,59,date("m"),0,date("Y"))) ?>"
				],
				"<?php echo lang('Datepicker.this_year') ?>": [
					"<?php echo date(config('OSPOS')->dateformat." ".config('OSPOS')->timeformat,mktime(0,0,0,1,1,date("Y"))) ?>",
					"<?php echo date(config('OSPOS')->dateformat." ".config('OSPOS')->timeformat,mktime(23,59,59,date("m")+1,0,date("Y"))) ?>"
				],
				"<?php echo lang('Datepicker.last_year') ?>": [
					"<?php echo date(config('OSPOS')->dateformat." ".config('OSPOS')->timeformat,mktime(0,0,0,1,1,date("Y")-1)) ?>",
					"<?php echo date(config('OSPOS')->dateformat." ".config('OSPOS')->timeformat,mktime(23,59,59,12,31,date("Y")-1)) ?>"
				],
				"<?php echo lang('Datepicker.this_financial_year') ?>": [
					"<?php echo date(config('OSPOS')->dateformat." ".config('OSPOS')->timeformat,mktime(0,0,0,config('OSPOS')->financial_year,1,date("Y"))) ?>",
					"<?php echo date(config('OSPOS')->dateformat." ".config('OSPOS')->timeformat,mktime(23,59,59,date("m")+1,0,date("Y"))) ?>"
				],
				"<?php echo lang('Datepicker.last_financial_year') ?>": [
					"<?php echo date(config('OSPOS')->dateformat." ".config('OSPOS')->timeformat,mktime(0,0,0,config('OSPOS')->financial_year,1,date("Y")-1)) ?>",
					"<?php echo date(config('OSPOS')->dateformat." ".config('OSPOS')->timeformat,mktime(23,59,59,config('OSPOS')->financial_year,0,date("Y"))) ?>"
				],
				"<?php echo lang('Datepicker.all_time') ?>": [
					"<?php echo date(config('OSPOS')->dateformat." ".config('OSPOS')->timeformat,mktime(0,0,0,1,1,2010)) ?>",
					"<?php echo date(config('OSPOS')->dateformat." ".config('OSPOS')->timeformat,mktime(23,59,59,date("m"),date("d"),date("Y"))) ?>"
				],
			},
			"locale": {
				"format": '<?php echo dateformat_momentjs(config('OSPOS')->dateformat." ".config('OSPOS')->timeformat) ?>',
				"separator": " - ",
				"applyLabel": "<?php echo lang('Datepicker.apply') ?>",
				"cancelLabel": "<?php echo lang('Datepicker.cancel') ?>",
				"fromLabel": "<?php echo lang('Datepicker.from') ?>",
				"toLabel": "<?php echo lang('Datepicker.to') ?>",
				"customRangeLabel": "<?php echo lang('Datepicker.custom') ?>",
				"daysOfWeek": [
					"<?php echo lang('Cal.su') ?>",
					"<?php echo lang('Cal.mo') ?>",
					"<?php echo lang('Cal.tu') ?>",
					"<?php echo lang('Cal.we') ?>",
					"<?php echo lang('Cal.th') ?>",
					"<?php echo lang('Cal.fr') ?>",
					"<?php echo lang('Cal.sa') ?>",
					"<?php echo lang('Cal.su') ?>"
				],
				"monthNames": [
					"<?php echo lang('Cal.january') ?>",
					"<?php echo lang('Cal.february') ?>",
					"<?php echo lang('Cal.march') ?>",
					"<?php echo lang('Cal.april') ?>",
					"<?php echo lang('Cal.may') ?>",
					"<?php echo lang('Cal.june') ?>",
					"<?php echo lang('Cal.july') ?>",
					"<?php echo lang('Cal.august') ?>",
					"<?php echo lang('Cal.september') ?>",
					"<?php echo lang('Cal.october') ?>",
					"<?php echo lang('Cal.november') ?>",
					"<?php echo lang('Cal.december') ?>"
				],
				"firstDay": <?php echo lang('Datepicker.weekstart') ?>
			},
		    "timePicker": true,
		    "timePickerSeconds": true,
			"alwaysShowCalendars": true,
			"startDate": "<?php echo date(config('OSPOS')->dateformat." ".config('OSPOS')->timeformat,mktime(0,0,0,date("m"),date("d"),date("Y"))) ?>",
			"endDate": "<?php echo date(config('OSPOS')->dateformat." ".config('OSPOS')->timeformat,mktime(23,59,59,date("m"),date("d"),date("Y"))) ?>",
			"minDate": "<?php echo date(config('OSPOS')->dateformat." ".config('OSPOS')->timeformat,mktime(0,0,0,01,01,2010)) ?>",
			"maxDate": "<?php echo date(config('OSPOS')->dateformat." ".config('OSPOS')->timeformat,mktime(23,59,59,date("m"),date("d"),date("Y"))) ?>"
		}, function(start, end, label) {
			start_date = start.format('YYYY-MM-DD HH:mm:ss');
			end_date = end.format('YYYY-MM-DD HH:mm:ss');
		});
<?php
	}
?>
