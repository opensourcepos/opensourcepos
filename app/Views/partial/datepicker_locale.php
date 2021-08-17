<?php $this->lang->load('calendar'); $this->lang->load('date'); ?>

var pickerconfig = function(config) {
    return $.extend({
        format: "<?php echo dateformat_bootstrap($this->config->item('dateformat')) . ' ' . dateformat_bootstrap($this->config->item('timeformat'));?>",
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
        language: "<?php echo current_language_code(); ?>"
    }, <?php echo isset($config) ? $config : '{}' ?>);
};

$.fn.datetimepicker.dates['<?php echo $this->config->item("language"); ?>'] = {
    days: [
		"<?php echo lang("cal_sunday"); ?>",
        "<?php echo lang("cal_monday"); ?>",
        "<?php echo lang("cal_tuesday"); ?>",
        "<?php echo lang("cal_wednesday"); ?>",
        "<?php echo lang("cal_thursday"); ?>",
        "<?php echo lang("cal_friday"); ?>",
        "<?php echo lang("cal_saturday"); ?>",
        "<?php echo lang("cal_sunday"); ?>"
		],
        daysShort: [
		"<?php echo lang("cal_sun"); ?>",
        "<?php echo lang("cal_mon"); ?>",
        "<?php echo lang("cal_tue"); ?>",
        "<?php echo lang("cal_wed"); ?>",
        "<?php echo lang("cal_thu"); ?>",
        "<?php echo lang("cal_fri"); ?>",
        "<?php echo lang("cal_sat"); ?>"
		],
        daysMin: [
		"<?php echo lang("cal_su"); ?>",
        "<?php echo lang("cal_mo"); ?>",
        "<?php echo lang("cal_tu"); ?>",
        "<?php echo lang("cal_we"); ?>",
        "<?php echo lang("cal_th"); ?>",
        "<?php echo lang("cal_fr"); ?>",
        "<?php echo lang("cal_sa"); ?>"
		],
        months: [
		"<?php echo lang("cal_january"); ?>",
        "<?php echo lang("cal_february"); ?>",
        "<?php echo lang("cal_march"); ?>",
        "<?php echo lang("cal_april"); ?>",
        "<?php echo lang("cal_may"); ?>",
        "<?php echo lang("cal_june"); ?>",
        "<?php echo lang("cal_july"); ?>",
        "<?php echo lang("cal_august"); ?>",
        "<?php echo lang("cal_september"); ?>",
        "<?php echo lang("cal_october"); ?>",
        "<?php echo lang("cal_november"); ?>",
        "<?php echo lang("cal_december"); ?>"
		],
        monthsShort: [
		"<?php echo lang("cal_jan"); ?>",
        "<?php echo lang("cal_feb"); ?>",
        "<?php echo lang("cal_mar"); ?>",
        "<?php echo lang("cal_apr"); ?>",
        "<?php echo lang("cal_may"); ?>",
        "<?php echo lang("cal_jun"); ?>",
        "<?php echo lang("cal_jul"); ?>",
        "<?php echo lang("cal_aug"); ?>",
        "<?php echo lang("cal_sep"); ?>",
        "<?php echo lang("cal_oct"); ?>",
        "<?php echo lang("cal_nov"); ?>",
        "<?php echo lang("cal_dec"); ?>"
		],
    today: "<?php echo lang("datepicker_today"); ?>",
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
    weekStart: <?php echo lang("datepicker_weekstart"); ?>
};

$(".datetime").datetimepicker(pickerconfig());
