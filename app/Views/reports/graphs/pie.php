<?php
/**
 * @var array $labels_1
 * @var array $series_data_1
 * @var bool $show_currency
 * @var array $config
 */
?>

<script type="text/javascript">
    // Labels and data series
    const data = {
        labels: <?= json_encode(esc($labels_1, 'js')) ?>,
        series: <?= json_encode(esc($series_data_1, 'js')) ?>
    };

    // We are setting a few options for our chart and override the defaults
    const options = {

        // Specify a fixed width for the chart as a string (i.e. '100px' or '50%')
        width: '100%',

        // Specify a fixed height for the chart as a string (i.e. '100px' or '50%')
        height: '100%',

        // Padding of the chart drawing area to the container element and labels as a number or padding object {top: 5, right: 5, bottom: 5, left: 5}
        chartPadding: 20,

        // This option can be set to 'inside', 'outside' or 'center'.
        // Show the labels on the border with the pie chart
        labelPosition: 'outside',
        labelDirection: 'explode',

        <?php
            $currency_symbol = esc($config['currency_symbol'], 'js');
            $currency_prefix = '';
            $currency_suffix = '';

            if ($show_currency) {
                if (is_right_side_currency_symbol()) {
                    $currency_suffix = $currency_symbol;
                } else {
                    $currency_prefix = $currency_symbol;
                }
            }
        ?>

        plugins: [
            Chartist.plugins.tooltip({
                transformTooltipTextFnc: function(value) {
                    return '<?= $currency_prefix ?>' + value + '<?= $currency_suffix ?>';
                }
            })
        ]    };

    const responsiveOptions = [
        ['screen and (min-width: 640px)', {
            height: '80%',
            chartPadding: 20

        }]
        /* ,
         * ['screen and (min-width: 1024px)', {
         *     labelOffset: 80,
         *     chartPadding: 20
         * }]
         */
    ];

    chart = new Chartist.Pie('#chart1', data, options, responsiveOptions);

    // Generate random colours for the pie sliced because Chartist is currently limited to 15 colours
    chart.on('draw', function(data) {
        if (data.type === 'slice') {
            const r = Math.floor(Math.random() * 256);
            const g = Math.floor(Math.random() * 256);
            const b = Math.floor(Math.random() * 256);

            data.element.attr({
                style: 'fill: #' + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1)
            });
        }
    });
</script>
