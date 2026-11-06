<?php
/**
 * @var array $throttles
 */
?>

<?php
$i = 0;

if (empty($throttles)) {
    $throttles = [['throttle_id' => 0, 'max_count' => 0, 'period' => 'hour', 'deleted' => 0]];
}

foreach ($throttles as $throttle) {
    $throttleId = $throttle['throttle_id'];
    $maxCount = $throttle['max_count'];
    $period = $throttle['period'];
    ++$i;
?>

    <div class="form-group form-group-sm" style="<?= ($throttle['deleted'] ?? 0) ? 'display: none;' : 'display: block;' ?>">
        <div class="col-xs-offset-4 col-sm-offset-3 col-md-offset-2 col-xs-8 col-sm-9 col-md-10">
            <div style="display: flex; align-items: center;">
                <div style="width: 90px; flex: 0 0 auto;">
                    <?php $formData = [
                        'type'  => 'number',
                        'min'   => 0,
                        'name'  => 'throttle_count_' . $throttleId,
                        'id'    => 'throttle_count_' . $throttleId,
                        'class' => 'form-control input-sm required digits',
                        'value' => $maxCount
                    ];
                    ($throttle['deleted'] ?? 0) && $formData['disabled'] = 'disabled';
                    echo form_input($formData);
                    ?>
                </div>
                <span style="flex: 0 0 auto; padding: 0 0.5em; font-weight: bold;">/</span>
                <div style="width: 90px; flex: 0 0 auto;">
                    <?php $dropdownAttrs = ['id' => 'throttle_period_' . $throttleId, 'class' => 'form-control input-sm required'];
                    ($throttle['deleted'] ?? 0) && $dropdownAttrs['disabled'] = 'disabled';
                    echo form_dropdown(
                        'throttle_period_' . $throttleId,
                        [
                            'minute' => lang('Jobs.throttle_period_minute'),
                            'hour'   => lang('Jobs.throttle_period_hour'),
                            'day'    => lang('Jobs.throttle_period_day'),
                            'month'  => lang('Jobs.throttle_period_month')
                        ],
                        $period,
                        $dropdownAttrs
                    );
                    ?>
                </div>
                <button type="button" class="add_throttle glyphicon glyphicon-plus" style="flex: 0 0 auto; padding-left: 0.5em; border: none; background: none;" aria-label="<?= lang('Jobs.add_throttle') ?>"></button>
                <span style="flex: 0 0 auto;">&nbsp;&nbsp;</span>
                <button type="button" class="remove_throttle glyphicon glyphicon-minus" style="flex: 0 0 auto; border: none; background: none;" aria-label="<?= lang('Jobs.remove_throttle') ?>"></button>
            </div>
        </div>
    </div>

<?php } ?>
