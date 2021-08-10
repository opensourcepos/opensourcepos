<?php $this->lang->load("calendar"); $this->lang->load("date"); 
	if(empty($this->config->item('date_or_time_format')))
	{
?>
		$('#daterangepicker').css("width","180");
		var start_date = "<?= date('Y-m-d') ?>";
		var end_date   = "<?= date('Y-m-d') ?>";

		$('#daterangepicker').daterangepicker({
			"ranges": {
				"<?= $this->lang->line("datepicker_today"); ?>": [
					"<?= date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d"),date("Y")));?>",
					"<?= date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d")+1,date("Y"))-1);?>"
				],
				"<?= $this->lang->line("datepicker_today_last_year"); ?>": [
					"<?= date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d"),date("Y")-1));?>",
					"<?= date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d")+1,date("Y")-1)-1);?>"
				],
				"<?= $this->lang->line("datepicker_yesterday"); ?>": [
					"<?= date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d")-1,date("Y")));?>",
					"<?= date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d"),date("Y"))-1);?>"
				],
				"<?= $this->lang->line("datepicker_last_7"); ?>": [
					"<?= date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d")-6,date("Y")));?>",
					"<?= date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d")+1,date("Y"))-1);?>"
				],
				"<?= $this->lang->line("datepicker_last_30"); ?>": [
					"<?= date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d")-29,date("Y")));?>",
					"<?= date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d")+1,date("Y"))-1);?>"
				],
				"<?= $this->lang->line("datepicker_this_month"); ?>": [
					"<?= date($this->config->item('dateformat'), mktime(0,0,0,date("m"),1,date("Y")));?>",
					"<?= date($this->config->item('dateformat'), mktime(0,0,0,date("m")+1,1,date("Y"))-1);?>"
				],
				"<?= $this->lang->line("datepicker_same_month_to_same_day_last_year"); ?>": [
					"<?= date($this->config->item('dateformat'), mktime(0,0,0,date("m"),1,date("Y")-1));?>",
					"<?= date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d")+1,date("Y")-1)-1);?>"
				],
				"<?= $this->lang->line("datepicker_same_month_last_year"); ?>": [
					"<?= date($this->config->item('dateformat'), mktime(0,0,0,date("m"),1,date("Y")-1));?>",
					"<?= date($this->config->item('dateformat'), mktime(0,0,0,date("m")+1,1,date("Y")-1)-1);?>"
				],
				"<?= $this->lang->line("datepicker_last_month"); ?>": [
					"<?= date($this->config->item('dateformat'), mktime(0,0,0,date("m")-1,1,date("Y")));?>",
					"<?= date($this->config->item('dateformat'), mktime(0,0,0,date("m"),1,date("Y"))-1);?>"
				],
				"<?= $this->lang->line("datepicker_this_year"); ?>": [
					"<?= date($this->config->item('dateformat'), mktime(0,0,0,1,1,date("Y")));?>",
					"<?= date($this->config->item('dateformat'), mktime(0,0,0,date("m"),1,date("Y")+1)-1);?>"
				],
				"<?= $this->lang->line("datepicker_last_year"); ?>": [
					"<?= date($this->config->item('dateformat'), mktime(0,0,0,1,1,date("Y")-1));?>",
					"<?= date($this->config->item('dateformat'), mktime(0,0,0,1,1,date("Y"))-1);?>"
				],
				"<?= $this->lang->line("datepicker_this_financial_year"); ?>": [
					"<?= date($this->config->item('dateformat'), mktime(0,0,0,$this->config->item('financial_year'),1,date("Y")));?>",
					"<?= date($this->config->item('dateformat'), mktime(0,0,0,date("m"),1,date("Y")+1)-1);?>"
				],
				"<?= $this->lang->line("datepicker_last_financial_year"); ?>": [
					"<?= date($this->config->item('dateformat'), mktime(0,0,0,$this->config->item('financial_year'),1,date("Y")-1));?>",
					"<?= date($this->config->item('dateformat'), mktime(0,0,0,$this->config->item('financial_year'),1,date("Y"))-1);?>"
				],
				"<?= $this->lang->line("datepicker_all_time"); ?>": [
					"<?= date($this->config->item('dateformat'), mktime(0,0,0,1,1,2010));?>",
					"<?= date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d")+1,date("Y"))-1);?>"
				],
			},
			"locale": {
				"format": '<?= dateformat_momentjs($this->config->item("dateformat"))?>',
				"separator": " - ",
				"applyLabel": "<?= $this->lang->line("datepicker_apply"); ?>",
				"cancelLabel": "<?= $this->lang->line("datepicker_cancel"); ?>",
				"fromLabel": "<?= $this->lang->line("datepicker_from"); ?>",
				"toLabel": "<?= $this->lang->line("datepicker_to"); ?>",
				"customRangeLabel": "<?= $this->lang->line("datepicker_custom"); ?>",
				"daysOfWeek": [
					"<?= $this->lang->line("cal_su"); ?>",
					"<?= $this->lang->line("cal_mo"); ?>",
					"<?= $this->lang->line("cal_tu"); ?>",
					"<?= $this->lang->line("cal_we"); ?>",
					"<?= $this->lang->line("cal_th"); ?>",
					"<?= $this->lang->line("cal_fr"); ?>",
					"<?= $this->lang->line("cal_sa"); ?>",
					"<?= $this->lang->line("cal_su"); ?>"
				],
				"monthNames": [
					"<?= $this->lang->line("cal_january"); ?>",
					"<?= $this->lang->line("cal_february"); ?>",
					"<?= $this->lang->line("cal_march"); ?>",
					"<?= $this->lang->line("cal_april"); ?>",
					"<?= $this->lang->line("cal_may"); ?>",
					"<?= $this->lang->line("cal_june"); ?>",
					"<?= $this->lang->line("cal_july"); ?>",
					"<?= $this->lang->line("cal_august"); ?>",
					"<?= $this->lang->line("cal_september"); ?>",
					"<?= $this->lang->line("cal_october"); ?>",
					"<?= $this->lang->line("cal_november"); ?>",
					"<?= $this->lang->line("cal_december"); ?>"
				],
				"firstDay": <?= $this->lang->line("datepicker_weekstart"); ?>
			},
			"alwaysShowCalendars": true,
			"startDate": "<?= date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d")+1,date("Y"))-1);?>",
			"endDate": "<?= date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d")+1,date("Y"))-1);?>",
			"minDate": "<?= date($this->config->item('dateformat'), mktime(0,0,0,01,01,2010));?>",
			"maxDate": "<?= date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d")+1,date("Y"))-1);?>"
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
		var start_date = "<?= date('Y-m-d H:i:s', mktime(0,0,0,date("m"),date("d"),date("Y")))?>";
		var end_date = "<?= date('Y-m-d H:i:s', mktime(23,59,59,date("m"),date("d"),date("Y")))?>";
		$('#daterangepicker').daterangepicker({
			"ranges": {
				"<?= $this->lang->line("datepicker_today"); ?>": [
					"<?= date($this->config->item('dateformat')." ".$this->config->item('timeformat'), mktime(0,0,0,date("m"),date("d"),date("Y")));?>",
					"<?= date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(23,59,59,date("m"),date("d"),date("Y")));?>"
				],
				"<?= $this->lang->line("datepicker_today_last_year"); ?>": [
					"<?= date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(0,0,0,date("m"),date("d"),date("Y")-1));?>",
					"<?= date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(23,59,59,date("m"),date("d"),date("Y")-1));?>"
				],
				"<?= $this->lang->line("datepicker_yesterday"); ?>": [
					"<?= date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(0,0,0,date("m"),date("d")-1,date("Y")));?>",
					"<?= date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(23,59,59,date("m"),date("d")-1,date("Y")));?>"
				],
				"<?= $this->lang->line("datepicker_last_7"); ?>": [
					"<?= date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(0,0,0,date("m"),date("d")-6,date("Y")));?>",
					"<?= date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(23,59,59,date("m"),date("d"),date("Y")));?>"
				],
				"<?= $this->lang->line("datepicker_last_30"); ?>": [
					"<?= date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(0,0,0,date("m"),date("d")-29,date("Y")));?>",
					"<?= date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(23,59,59,date("m"),date("d"),date("Y")));?>"
				],
				"<?= $this->lang->line("datepicker_this_month"); ?>": [
					"<?= date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(0,0,0,date("m"),1,date("Y")));?>",
					"<?= date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(23,59,59,date("m"),date("d"),date("Y")));?>"
				],
				"<?= $this->lang->line("datepicker_same_month_to_same_day_last_year"); ?>": [
					"<?= date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(0,0,0,date("m"),1,date("Y")-1));?>",
					"<?= date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(23,59,59,date("m"),date("d"),date("Y")-1));?>"
				],
				"<?= $this->lang->line("datepicker_same_month_last_year"); ?>": [
					"<?= date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(0,0,0,date("m"),1,date("Y")-1));?>",
					"<?= date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(23,59,59,date("m")+1,0,date("Y")-1));?>"
				],
				"<?= $this->lang->line("datepicker_last_month"); ?>": [
					"<?= date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(0,0,0,date("m")-1,1,date("Y")));?>",
					"<?= date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(23,59,59,date("m"),0,date("Y")));?>"
				],
				"<?= $this->lang->line("datepicker_this_year"); ?>": [
					"<?= date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(0,0,0,1,1,date("Y")));?>",
					"<?= date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(23,59,59,date("m")+1,0,date("Y")));?>"
				],
				"<?= $this->lang->line("datepicker_last_year"); ?>": [
					"<?= date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(0,0,0,1,1,date("Y")-1));?>",
					"<?= date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(23,59,59,12,31,date("Y")-1));?>"
				],
				"<?= $this->lang->line("datepicker_this_financial_year"); ?>": [
					"<?= date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(0,0,0,$this->config->item('financial_year'),1,date("Y")));?>",
					"<?= date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(23,59,59,date("m")+1,0,date("Y")));?>"
				],
				"<?= $this->lang->line("datepicker_last_financial_year"); ?>": [
					"<?= date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(0,0,0,$this->config->item('financial_year'),1,date("Y")-1));?>",
					"<?= date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(23,59,59,$this->config->item('financial_year'),0,date("Y")));?>"
				],
				"<?= $this->lang->line("datepicker_all_time"); ?>": [
					"<?= date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(0,0,0,1,1,2010));?>",
					"<?= date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(23,59,59,date("m"),date("d"),date("Y")));?>"
				],
			},
			"locale": {
				"format": '<?= dateformat_momentjs($this->config->item("dateformat")." ".$this->config->item('timeformat'))?>',
				"separator": " - ",
				"applyLabel": "<?= $this->lang->line("datepicker_apply"); ?>",
				"cancelLabel": "<?= $this->lang->line("datepicker_cancel"); ?>",
				"fromLabel": "<?= $this->lang->line("datepicker_from"); ?>",
				"toLabel": "<?= $this->lang->line("datepicker_to"); ?>",
				"customRangeLabel": "<?= $this->lang->line("datepicker_custom"); ?>",
				"daysOfWeek": [
					"<?= $this->lang->line("cal_su"); ?>",
					"<?= $this->lang->line("cal_mo"); ?>",
					"<?= $this->lang->line("cal_tu"); ?>",
					"<?= $this->lang->line("cal_we"); ?>",
					"<?= $this->lang->line("cal_th"); ?>",
					"<?= $this->lang->line("cal_fr"); ?>",
					"<?= $this->lang->line("cal_sa"); ?>",
					"<?= $this->lang->line("cal_su"); ?>"
				],
				"monthNames": [
					"<?= $this->lang->line("cal_january"); ?>",
					"<?= $this->lang->line("cal_february"); ?>",
					"<?= $this->lang->line("cal_march"); ?>",
					"<?= $this->lang->line("cal_april"); ?>",
					"<?= $this->lang->line("cal_may"); ?>",
					"<?= $this->lang->line("cal_june"); ?>",
					"<?= $this->lang->line("cal_july"); ?>",
					"<?= $this->lang->line("cal_august"); ?>",
					"<?= $this->lang->line("cal_september"); ?>",
					"<?= $this->lang->line("cal_october"); ?>",
					"<?= $this->lang->line("cal_november"); ?>",
					"<?= $this->lang->line("cal_december"); ?>"
				],
				"firstDay": <?= $this->lang->line("datepicker_weekstart"); ?>
			},
		    "timePicker": true,
		    "timePickerSeconds": true,
			"alwaysShowCalendars": true,
			"startDate": "<?= date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(0,0,0,date("m"),date("d"),date("Y")));?>",
			"endDate": "<?= date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(23,59,59,date("m"),date("d"),date("Y")));?>",
			"minDate": "<?= date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(0,0,0,01,01,2010));?>",
			"maxDate": "<?= date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(23,59,59,date("m"),date("d"),date("Y")));?>"
		}, function(start, end, label) {
			start_date = start.format('YYYY-MM-DD HH:mm:ss');
			end_date = end.format('YYYY-MM-DD HH:mm:ss');
		});
<?php
	}
?>
