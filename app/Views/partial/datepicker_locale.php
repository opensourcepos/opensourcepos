<?php
use Config\OSPOS;

$config = config(OSPOS::class)->settings; ?>

var pickerconfig = function(config) {
    return $.extend({
        format: "<?= dateformat_bootstrap($config['dateformat']) . ' ' . dateformat_bootstrap($config['timeformat'])?>",
        <?php
        $t = $config['timeformat'];
        $m = $t[strlen($t) - 1];
        if (str_contains($config['timeformat'], 'a') || str_contains($config['timeformat'], 'A')) {
        ?>
            showMeridian: true,
        <?php } else {  ?>
            showMeridian: false,
        <?php } ?>
        minView: 2,
        minuteStep: 1,
        autoclose: true,
        todayBtn: true,
        todayHighlight: true,
        bootcssVer: 3,
        language: "<?= current_language_code() ?>"
    }, <?php echo isset($config) ?>);
};

$.fn.datetimepicker.dates['<?= $config['language'] ?>'] = {
    days: [
        "<?= lang('Calendar.sunday') ?>",
        "<?= lang('Calendar.monday') ?>",
        "<?= lang('Calendar.tuesday') ?>",
        "<?= lang('Calendar.wednesday') ?>",
        "<?= lang('Calendar.thursday') ?>",
        "<?= lang('Calendar.friday') ?>",
        "<?= lang('Calendar.saturday') ?>"
    ],
    daysShort: [
        "<?= lang('Calendar.sun') ?>",
        "<?= lang('Calendar.mon') ?>",
        "<?= lang('Calendar.tue') ?>",
        "<?= lang('Calendar.wed') ?>",
        "<?= lang('Calendar.thu') ?>",
        "<?= lang('Calendar.fri') ?>",
        "<?= lang('Calendar.sat') ?>"
    ],
    daysMin: [
        "<?= lang('Calendar.su') ?>",
        "<?= lang('Calendar.mo') ?>",
        "<?= lang('Calendar.tu') ?>",
        "<?= lang('Calendar.we') ?>",
        "<?= lang('Calendar.th') ?>",
        "<?= lang('Calendar.fr') ?>",
        "<?= lang('Calendar.sa') ?>"
    ],
    months: [
        "<?= lang('Calendar.january') ?>",
        "<?= lang('Calendar.february') ?>",
        "<?= lang('Calendar.march') ?>",
        "<?= lang('Calendar.april') ?>",
        "<?= lang('Calendar.may') ?>",
        "<?= lang('Calendar.june') ?>",
        "<?= lang('Calendar.july') ?>",
        "<?= lang('Calendar.august') ?>",
        "<?= lang('Calendar.september') ?>",
        "<?= lang('Calendar.october') ?>",
        "<?= lang('Calendar.november') ?>",
        "<?= lang('Calendar.december') ?>"
    ],
    monthsShort: [
        "<?= lang('Calendar.jan') ?>",
        "<?= lang('Calendar.feb') ?>",
        "<?= lang('Calendar.mar') ?>",
        "<?= lang('Calendar.apr') ?>",
        "<?= lang('Calendar.may') ?>",
        "<?= lang('Calendar.jun') ?>",
        "<?= lang('Calendar.jul') ?>",
        "<?= lang('Calendar.aug') ?>",
        "<?= lang('Calendar.sep') ?>",
        "<?= lang('Calendar.oct') ?>",
        "<?= lang('Calendar.nov') ?>",
        "<?= lang('Calendar.dec') ?>"
    ],
    today: "<?= lang('Datepicker.today') ?>",
    <?php if (str_contains($config['timeformat'], 'a')) { ?>
        meridiem: ["am", "pm"],
    <?php } elseif (str_contains($config['timeformat'], 'A')) { ?>
        meridiem: ["AM", "PM"],
    <?php } else { ?>
        meridiem: [],
    <?php } ?>
    weekStart: <?= lang('Datepicker.weekstart') ?>
};

$(".datetime").datetimepicker(pickerconfig());
