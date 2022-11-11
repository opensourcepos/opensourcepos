<script>
	// Labels and data series
	var data = {
		labels: <?php echo json_encode($labels_1); ?>,
		series: [{
			name: '<?php echo $yaxis_title; ?>',
			data: <?php echo json_encode($series_data_1); ?>
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

		// X-Axis specific configuration
		axisX: {
			// Lets offset the chart a bit from the labels
			offset: 120,
			position: 'end'
		},

		// Y-Axis specific configuration
		axisY: {
			// Lets offset the chart a bit from the labels
			offset: 60,
			// The label interpolation function enables you to modify the values
			// used for the labels on each axis.
			labelInterpolationFnc: function(value) {
				<?php
				if( $show_currency )
				{
					if( currency_side() )
					{
				?>
						return value + '<?php echo $this->config->item('currency_symbol'); ?>';
					<?php
					}
					else
					{
					?>
						return '<?php echo $this->config->item('currency_symbol'); ?>' + value;				
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
					axisTitle: '<?php echo $xaxis_title; ?>',
					axisClass: 'ct-axis-title',
					offset: {
						x: -100,
						y: 100
					},
					textAnchor: 'middle'
				},
				axisY: {
					axisTitle: '<?php echo $yaxis_title; ?>',
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
		}] /*,
		['screen and (min-width: 1024px)', {
			labelOffset: 80,
			chartPadding: 20
		}]*/
	];

	new Chartist.Bar('#chart1', data, options, responsiveOptions);
</script>