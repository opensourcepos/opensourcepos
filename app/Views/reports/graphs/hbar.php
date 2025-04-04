<?php
/**
 * @var array $labels_1
 * @var string $yaxis_title
 * @var array $series_data_1
 * @var bool $show_currency
 * @var string $xaxis_title
 * @var array $config
 */
?>

<script type="text/javascript">
    // Labels and data series
    var data = {
        labels: <?= json_encode(esc($labels_1, 'js')) ?>,
        series: [{
            name: '<?= esc($yaxis_title, 'js') ?>',
            data: <?= json_encode(esc($series_data_1, 'js')) ?>
        }]
    };

    // We are setting a few options for our chart and override the defaults
    var options = {

        // Specify a fixed width for the chart as a string (i.e. '100px' or '50%')
        width: '100%',

        // Specify a fixed height for the chart as a string (i.e. '100px' or '50%')
        height: '100%',

        // Padding of the chart drawing area to the container element and labels as a number or padding object {top: 5, right: 5, bottom: 5, left: 5}
        chartPadding: {
            top: 20,
            bottom: 100
        },

        // Set the bar chart to be horizontal
        horizontalBars: true,

        // X-Axis specific configuration
        axisX: {
            // Lets offset the chart a bit from the labels
            offset: 120,
            position: 'end',
            // The label interpolation function enables you to modify the values
            // used for the labels on each axis.
            labelInterpolationFnc: function(value) {
                <?php
                if ($show_currency) {
                    if (is_right_side_currency_symbol()) {
                ?>
                        return value + '<?= esc($config['currency_symbol'], 'js') ?>';
                    <?php } else { ?>
                        return '<?= esc($config['currency_symbol'], 'js') ?>' + value;
                    <?php
                    }
                } else {
                    ?>
                    return value;
                <?php } ?>
            }
        },

        // Y-Axis specific configuration
        axisY: {
            // Lets offset the chart a bit from the labels
            offset: 120
        },

        // Plugins configuration
        plugins: [
            Chartist.plugins.ctAxisTitle({
                axisX: {
                    axisTitle: '<?= esc($xaxis_title, 'js') ?>',
                    axisClass: 'ct-axis-title',
                    offset: {
                        x: -100,
                        y: 100
                    },
                    textAnchor: 'middle'
                },
                axisY: {
                    axisTitle: '<?= esc($yaxis_title, 'js') ?>',
                    axisClass: 'ct-axis-title',
                    offset: {
                        x: 0,
                        y: 0
                    },
                    textAnchor: 'middle',
                    flipTitle: false
                }
            }),

            Chartist.plugins.ctBarLabels(),

            Chartist.plugins.ctPointLabels({
                textAnchor: 'middle'
            })
        ]
    };

    var responsiveOptions = [
        ['screen and (min-width: 640px)', {
            height: '80%',
            chartPadding: {
                top: 20,
                bottom: 0
            },
        }]
        /* ,
         * ['screen and (min-width: 1024px)', {
         *     labelOffset: 80,
         *     chartPadding: 20
         * }]
         */
    ];

    new Chartist.Bar('#chart1', data, options, responsiveOptions);
</script>
