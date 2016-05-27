<script>
	// Labels and data series
	var data = {
		labels: <?php echo json_encode($labels_1); ?>,
		series: <?php echo json_encode($series_data_1); ?>
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

		// This option can be set to 'inside', 'outside' or 'center'. 
		// show the labels on the border with the pie chart
		labelPosition: 'outside',
		labelDirection: 'explode',
		
		plugins: [
			Chartist.plugins.tooltip({
				transformTooltipTextFnc: function(value) {
					<?php
					if( $show_currency )
					{
						if( $this->config->item('currency_side') )
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
			})
		]
	};
	
	var responsiveOptions = [
		['screen and (min-width: 640px)', {
			height: '80%',
			chartPadding: {
				top: 20,
				bottom: 20
			},
		}] /*,
		['screen and (min-width: 1024px)', {
			labelOffset: 80,
			chartPadding: 20
		}]*/
	];

	new Chartist.Pie('#chart1', data, options, responsiveOptions);
</script>