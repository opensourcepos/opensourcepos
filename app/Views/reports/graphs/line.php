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
<script>
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

        // Draw the line chart points
        showPoint: true,

        // Disable line smoothing
        lineSmooth: false,

        // Padding of the chart drawing area to the container element and labels as a number or padding object {top: 5, right: 5, bottom: 5, left: 5}
        chartPadding: {
            top: 20,
            bottom: 120
        },

        // X-Axis specific configuration
        axisX: {
            // Lets offset the chart a bit from the labels
            offset: 120,
            position: 'end',
            // offset the labels a bit from the axis to avoid overlaps
            labelOffset: {
                x: 0,
                y: 20
            }
        },

        // Y-Axis specific configuration
        axisY: {
            // Lets offset the chart a bit from the labels
            offset: 80,
            // offset the labels a bit from the axis to avoid overlaps
            labelOffset: {
                x: -20,
                y: 0
            },
            // The label interpolation function enables you to modify the values
            // used for the labels on each axis.
            labelInterpolationFnc: function(value) {
                <?php
                if($show_currency)
                {
                    if( is_right_side_currency_symbol() )
                    {
                ?>
                        return value + '<?= esc($config['currency_symbol'], 'js') ?>';
                    <?php
                    }
                    else
                    {
                    ?>
                        return '<?= esc($config['currency_symbol'], 'js') ?>' + value;
                        <?php
                    }
                }
                else
                {
                ?>
                    return value;
                <?php
                }
                ?>
            }
        },

        // plugins configuration
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

            Chartist.plugins.ctPointLabels({
                textAnchor: 'middle',
                labelInterpolationFnc: function(value) {
                    <?php
                    if( $show_currency )
                    {
                        if( is_right_side_currency_symbol() )
                        {
                    ?>
                            return value + '<?= esc($config['currency_symbol'], 'js') ?>';
                        <?php
                        }
                        else
                        {
                        ?>
                            return '<?= esc($config['currency_symbol'], 'js') ?>' + value;
                    <?php
                        }
                    }
                    else
                    {
                    ?>
                        return value;
                    <?php
                    }
                    ?>
                }
            }),

            Chartist.plugins.tooltip({
                pointClass: 'ct-tooltip-point',
                transformTooltipTextFnc: function(value) {
                    <?php
                    if( $show_currency )
                    {
                        if( is_right_side_currency_symbol() )
                        {
                    ?>
                            return value + '<?= esc($config['currency_symbol'], 'js') ?>';
                        <?php
                        }
                        else
                        {
                        ?>
                            return '<?= esc($config['currency_symbol'], 'js') ?>' + value;
                    <?php
                        }
                    }
                    else
                    {
                    ?>
                        return value;
                    <?php
                    }
                    ?>
                }
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
        }] /*,
        ['screen and (min-width: 1024px)', {
            labelOffset: 80,
            chartPadding: 20
        }]*/
    ];

    chart = new Chartist.Line('#chart1', data, options, responsiveOptions);

    chart.on('draw', function(data) {
        // If the draw event was triggered from drawing a point on the line chart
        if(data.type === 'point') {
            // We are creating a new path SVG element that draws a triangle around the point coordinates
            var circle = new Chartist.Svg('circle', {
                cx: [data.x],
                cy: [data.y],
                r: [5],
                'ct:value': data.value.y,
                'ct:meta': data.meta,
                class: 'ct-tooltip-point',
            }, 'ct-area');

            // With data.element we get the Chartist SVG wrapper and we can replace the original point drawn by Chartist with our newly created triangle
            data.element.replace(circle);
        }
    });
</script>
