<?php $config = config(OSPOS::class)->settings; ?>

var pickerconfig = function(config) {
    return $.extend({
        format: "<?php echo dateformat_bootstrap($config['dateformat']) . ' ' . dateformat_bootstrap($config['timeformat']) ?>",
        <?php
        $t = $config['timeformat'];
        $m = $t[strlen($t)-1];
        if( strpos($config['timeformat'], 'a') !== false || strpos($config['timeformat'], 'A') !== false )
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
        language: "<?php echo current_language_code() ?>"
    }, <?php '{}' ?>);
};

$.fn.datetimepicker.dates['<?php echo $config['language'] ?>'] = {
    days: [
		"<?php echo lang('Cal.sunday') ?>",
        "<?php echo lang('Cal.monday') ?>",
        "<?php echo lang('Cal.tuesday') ?>",
        "<?php echo lang('Cal.wednesday') ?>",
        "<?php echo lang('Cal.thursday') ?>",
        "<?php echo lang('Cal.friday') ?>",
        "<?php echo lang('Cal.saturday') ?>",
        "<?php echo lang('Cal.sunday') ?>"
		],
        daysShort: [
		"<?php echo lang('Cal.sun') ?>",
        "<?php echo lang('Cal.mon') ?>",
        "<?php echo lang('Cal.tue') ?>",
        "<?php echo lang('Cal.wed') ?>",
        "<?php echo lang('Cal.thu') ?>",
        "<?php echo lang('Cal.fri') ?>",
        "<?php echo lang('Cal.sat') ?>"
		],
        daysMin: [
		"<?php echo lang('Cal.su') ?>",
        "<?php echo lang('Cal.mo') ?>",
        "<?php echo lang('Cal.tu') ?>",
        "<?php echo lang('Cal.we') ?>",
        "<?php echo lang('Cal.th') ?>",
        "<?php echo lang('Cal.fr') ?>",
        "<?php echo lang('Cal.sa') ?>"
		],
        months: [
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
        monthsShort: [
		"<?php echo lang('Cal.jan') ?>",
        "<?php echo lang('Cal.feb') ?>",
        "<?php echo lang('Cal.mar') ?>",
        "<?php echo lang('Cal.apr') ?>",
        "<?php echo lang('Cal.may') ?>",
        "<?php echo lang('Cal.jun') ?>",
        "<?php echo lang('Cal.jul') ?>",
        "<?php echo lang('Cal.aug') ?>",
        "<?php echo lang('Cal.sep') ?>",
        "<?php echo lang('Cal.oct') ?>",
        "<?php echo lang('Cal.nov') ?>",
        "<?php echo lang('Cal.dec') ?>"
		],
    today: "<?php echo lang('Datepicker.today') ?>",
    <?php
        if( strpos($config['timeformat'], 'a') !== false )
        {
    ?>
    meridiem: ["am", "pm"],
    <?php
        }
        elseif( strpos($config['timeformat'], 'A') !== false )
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
    weekStart: <?php echo lang('Datepicker.weekstart') ?>
};

$(".datetime").datetimepicker(pickerconfig());
