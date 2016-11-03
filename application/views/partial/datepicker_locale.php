<?php $this->lang->load('calendar'); $this->lang->load('date'); ?>

$.fn.datetimepicker.dates['<?php echo $this->config->item("language"); ?>'] = {
    days: [
		"<?php echo $this->lang->line("cal_sunday"); ?>",
        "<?php echo $this->lang->line("cal_monday"); ?>",
        "<?php echo $this->lang->line("cal_tuesday"); ?>",
        "<?php echo $this->lang->line("cal_wednesday"); ?>",
        "<?php echo $this->lang->line("cal_thursday"); ?>",
        "<?php echo $this->lang->line("cal_friday"); ?>",
        "<?php echo $this->lang->line("cal_saturday"); ?>",
        "<?php echo $this->lang->line("cal_sunday"); ?>"
		],
        daysShort: [
		"<?php echo $this->lang->line("cal_sun"); ?>",
        "<?php echo $this->lang->line("cal_mon"); ?>",
        "<?php echo $this->lang->line("cal_tue"); ?>",
        "<?php echo $this->lang->line("cal_wed"); ?>",
        "<?php echo $this->lang->line("cal_thu"); ?>",
        "<?php echo $this->lang->line("cal_fri"); ?>",
        "<?php echo $this->lang->line("cal_sat"); ?>"
		],
        daysMin: [
		"<?php echo $this->lang->line("cal_su"); ?>",
        "<?php echo $this->lang->line("cal_mo"); ?>",
        "<?php echo $this->lang->line("cal_tu"); ?>",
        "<?php echo $this->lang->line("cal_we"); ?>",
        "<?php echo $this->lang->line("cal_th"); ?>",
        "<?php echo $this->lang->line("cal_fr"); ?>",
        "<?php echo $this->lang->line("cal_sa"); ?>"
		],
        months: [
		"<?php echo $this->lang->line("cal_january"); ?>",
        "<?php echo $this->lang->line("cal_february"); ?>",
        "<?php echo $this->lang->line("cal_march"); ?>",
        "<?php echo $this->lang->line("cal_april"); ?>",
        "<?php echo $this->lang->line("cal_may"); ?>",
        "<?php echo $this->lang->line("cal_june"); ?>",
        "<?php echo $this->lang->line("cal_july"); ?>",
        "<?php echo $this->lang->line("cal_august"); ?>",
        "<?php echo $this->lang->line("cal_september"); ?>",
        "<?php echo $this->lang->line("cal_october"); ?>",
        "<?php echo $this->lang->line("cal_november"); ?>",
        "<?php echo $this->lang->line("cal_december"); ?>"
		],
        monthsShort: [
		"<?php echo $this->lang->line("cal_jan"); ?>",
        "<?php echo $this->lang->line("cal_feb"); ?>",
        "<?php echo $this->lang->line("cal_mar"); ?>",
        "<?php echo $this->lang->line("cal_apr"); ?>",
        "<?php echo $this->lang->line("cal_may"); ?>",
        "<?php echo $this->lang->line("cal_jun"); ?>",
        "<?php echo $this->lang->line("cal_jul"); ?>",
        "<?php echo $this->lang->line("cal_aug"); ?>",
        "<?php echo $this->lang->line("cal_sep"); ?>",
        "<?php echo $this->lang->line("cal_oct"); ?>",
        "<?php echo $this->lang->line("cal_nov"); ?>",
        "<?php echo $this->lang->line("cal_dec"); ?>"
		],
    today: "<?php echo $this->lang->line("datepicker_today"); ?>",
    <?php
        if( strpos($this->config->item('timeformat'), 'a') !== false )
        {
    ?>
    meridiem: ["am", "pm"],
    <?php
        }
        elseif( strpos($this->config->item('timeformat'), 'A') !== false )
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
    weekStart: <?php echo $this->lang->line("datepicker_weekstart"); ?>
};