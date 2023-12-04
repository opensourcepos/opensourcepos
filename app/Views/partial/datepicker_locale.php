<?php
use Config\OSPOS;

$config = config(OSPOS::class)->settings; ?>

var pickerconfig = function(config) {
    return $.extend({
        format: "<?= dateformat_bootstrap($config['dateformat']) . ' ' . dateformat_bootstrap($config['timeformat']) ?>",
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
        language: "<?= current_language_code() ?>"
    }, <?php '{}' ?>);
};

$.fn.datetimepicker.dates['<?= $config['language'] ?>'] = {
    days: [
		"<?= lang('Cal.sunday') ?>",
        "<?= lang('Cal.monday') ?>",
        "<?= lang('Cal.tuesday') ?>",
        "<?= lang('Cal.wednesday') ?>",
        "<?= lang('Cal.thursday') ?>",
        "<?= lang('Cal.friday') ?>",
        "<?= lang('Cal.saturday') ?>",
        "<?= lang('Cal.sunday') ?>"
		],
        daysShort: [
		"<?= lang('Cal.sun') ?>",
        "<?= lang('Cal.mon') ?>",
        "<?= lang('Cal.tue') ?>",
        "<?= lang('Cal.wed') ?>",
        "<?= lang('Cal.thu') ?>",
        "<?= lang('Cal.fri') ?>",
        "<?= lang('Cal.sat') ?>"
		],
        daysMin: [
		"<?= lang('Cal.su') ?>",
        "<?= lang('Cal.mo') ?>",
        "<?= lang('Cal.tu') ?>",
        "<?= lang('Cal.we') ?>",
        "<?= lang('Cal.th') ?>",
        "<?= lang('Cal.fr') ?>",
        "<?= lang('Cal.sa') ?>"
		],
        months: [
		"<?= lang('Cal.january') ?>",
        "<?= lang('Cal.february') ?>",
        "<?= lang('Cal.march') ?>",
        "<?= lang('Cal.april') ?>",
        "<?= lang('Cal.may') ?>",
        "<?= lang('Cal.june') ?>",
        "<?= lang('Cal.july') ?>",
        "<?= lang('Cal.august') ?>",
        "<?= lang('Cal.september') ?>",
        "<?= lang('Cal.october') ?>",
        "<?= lang('Cal.november') ?>",
        "<?= lang('Cal.december') ?>"
		],
        monthsShort: [
		"<?= lang('Cal.jan') ?>",
        "<?= lang('Cal.feb') ?>",
        "<?= lang('Cal.mar') ?>",
        "<?= lang('Cal.apr') ?>",
        "<?= lang('Cal.may') ?>",
        "<?= lang('Cal.jun') ?>",
        "<?= lang('Cal.jul') ?>",
        "<?= lang('Cal.aug') ?>",
        "<?= lang('Cal.sep') ?>",
        "<?= lang('Cal.oct') ?>",
        "<?= lang('Cal.nov') ?>",
        "<?= lang('Cal.dec') ?>"
		],
    today: "<?= lang('Datepicker.today') ?>",
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
    weekStart: <?= lang('Datepicker.weekstart') ?>
};

$(".datetime").datetimepicker(pickerconfig());
