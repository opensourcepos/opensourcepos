<?php $this->lang->load('calendar'); $this->lang->load('date'); ?>

var pickerconfig = function(config) {
    return $.extend({
        format: "<?= dateformat_bootstrap($this->config->item('dateformat')) . ' ' . dateformat_bootstrap($this->config->item('timeformat'));?>",
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
        minuteStep: 1,
        autoclose: true,
        todayBtn: true,
        todayHighlight: true,
        bootcssVer: 3,
        language: "<?= current_language_code(); ?>"
    }, <?= isset($config) ? $config : '{}' ?>);
};

$.fn.datetimepicker.dates['<?= $this->config->item("language"); ?>'] = {
    days: [
		"<?= $this->lang->line("cal_sunday"); ?>",
        "<?= $this->lang->line("cal_monday"); ?>",
        "<?= $this->lang->line("cal_tuesday"); ?>",
        "<?= $this->lang->line("cal_wednesday"); ?>",
        "<?= $this->lang->line("cal_thursday"); ?>",
        "<?= $this->lang->line("cal_friday"); ?>",
        "<?= $this->lang->line("cal_saturday"); ?>",
        "<?= $this->lang->line("cal_sunday"); ?>"
		],
        daysShort: [
		"<?= $this->lang->line("cal_sun"); ?>",
        "<?= $this->lang->line("cal_mon"); ?>",
        "<?= $this->lang->line("cal_tue"); ?>",
        "<?= $this->lang->line("cal_wed"); ?>",
        "<?= $this->lang->line("cal_thu"); ?>",
        "<?= $this->lang->line("cal_fri"); ?>",
        "<?= $this->lang->line("cal_sat"); ?>"
		],
        daysMin: [
		"<?= $this->lang->line("cal_su"); ?>",
        "<?= $this->lang->line("cal_mo"); ?>",
        "<?= $this->lang->line("cal_tu"); ?>",
        "<?= $this->lang->line("cal_we"); ?>",
        "<?= $this->lang->line("cal_th"); ?>",
        "<?= $this->lang->line("cal_fr"); ?>",
        "<?= $this->lang->line("cal_sa"); ?>"
		],
        months: [
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
        monthsShort: [
		"<?= $this->lang->line("cal_jan"); ?>",
        "<?= $this->lang->line("cal_feb"); ?>",
        "<?= $this->lang->line("cal_mar"); ?>",
        "<?= $this->lang->line("cal_apr"); ?>",
        "<?= $this->lang->line("cal_may"); ?>",
        "<?= $this->lang->line("cal_jun"); ?>",
        "<?= $this->lang->line("cal_jul"); ?>",
        "<?= $this->lang->line("cal_aug"); ?>",
        "<?= $this->lang->line("cal_sep"); ?>",
        "<?= $this->lang->line("cal_oct"); ?>",
        "<?= $this->lang->line("cal_nov"); ?>",
        "<?= $this->lang->line("cal_dec"); ?>"
		],
    today: "<?= $this->lang->line("datepicker_today"); ?>",
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
    weekStart: <?= $this->lang->line("datepicker_weekstart"); ?>
};

$(".datetime").datetimepicker(pickerconfig());
