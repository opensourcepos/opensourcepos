$.fn.datetimepicker.dates['<?php echo $this->config->item("language"); ?>'] = {
    days: ["<?php echo $this->lang->line("datepicker_days_sunday"); ?>",
        "<?php echo $this->lang->line("datepicker_days_monday"); ?>",
        "<?php echo $this->lang->line("datepicker_days_tueday"); ?>",
        "<?php echo $this->lang->line("datepicker_days_wednesday"); ?>",
        "<?php echo $this->lang->line("datepicker_days_thursday"); ?>",
        "<?php echo $this->lang->line("datepicker_days_friday"); ?>",
        "<?php echo $this->lang->line("datepicker_days_saturday"); ?>",
        "<?php echo $this->lang->line("datepicker_days_sunday"); ?>"],
        daysShort: ["<?php echo $this->lang->line("datepicker_daysshort_sunday"); ?>",
        "<?php echo $this->lang->line("datepicker_daysshort_monday"); ?>",
        "<?php echo $this->lang->line("datepicker_daysshort_tueday"); ?>",
        "<?php echo $this->lang->line("datepicker_daysshort_wednesday"); ?>",
        "<?php echo $this->lang->line("datepicker_daysshort_thursday"); ?>",
        "<?php echo $this->lang->line("datepicker_daysshort_friday"); ?>",
        "<?php echo $this->lang->line("datepicker_daysshort_saturday"); ?>",
        "<?php echo $this->lang->line("datepicker_daysshort_sunday"); ?>"],
        daysMin: ["<?php echo $this->lang->line("datepicker_daysmin_sunday"); ?>",
        "<?php echo $this->lang->line("datepicker_daysmin_monday"); ?>",
        "<?php echo $this->lang->line("datepicker_daysmin_tueday"); ?>",
        "<?php echo $this->lang->line("datepicker_daysmin_wednesday"); ?>",
        "<?php echo $this->lang->line("datepicker_daysmin_thursday"); ?>",
        "<?php echo $this->lang->line("datepicker_daysmin_friday"); ?>",
        "<?php echo $this->lang->line("datepicker_daysmin_saturday"); ?>",
        "<?php echo $this->lang->line("datepicker_daysmin_sunday"); ?>"],
        months: ["<?php echo $this->lang->line("datepicker_months_january"); ?>",
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
        "<?php echo $this->lang->line("datepicker_months_december"); ?>"],
        monthsShort: ["<?php echo $this->lang->line("datepicker_monthsshort_january"); ?>",
        "<?php echo $this->lang->line("datepicker_monthsshort_february"); ?>",
        "<?php echo $this->lang->line("datepicker_monthsshort_march"); ?>",
        "<?php echo $this->lang->line("datepicker_monthsshort_april"); ?>",
        "<?php echo $this->lang->line("datepicker_monthsshort_may"); ?>",
        "<?php echo $this->lang->line("datepicker_monthsshort_june"); ?>",
        "<?php echo $this->lang->line("datepicker_monthsshort_july"); ?>",
        "<?php echo $this->lang->line("datepicker_monthsshort_august"); ?>",
        "<?php echo $this->lang->line("datepicker_monthsshort_september"); ?>",
        "<?php echo $this->lang->line("datepicker_monthsshort_october"); ?>",
        "<?php echo $this->lang->line("datepicker_monthsshort_november"); ?>",
        "<?php echo $this->lang->line("datepicker_monthsshort_december"); ?>"
    ],
    today: "<?php echo $this->lang->line("datepicker_today"); ?>",
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
    weekStart: <?php echo $this->lang->line("datepicker_weekstart"); ?>
};