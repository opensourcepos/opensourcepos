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
		// X-Axis specific configuration
		axisX: {
			// Lets offset the chart a bit from the labels
			offset: 120,
			position: 'end'
		},

		// Y-Axis specific configuration
		axisY: {
			// Lets offset the chart a bit from the labels
			offset: 60
		},

		width: '100%',
		height: '75%',

		// plugins configuration
		plugins: [
			Chartist.plugins.ctAxisTitle({
				axisX: {
					axisTitle: '<?php echo $xaxis_title; ?>',
					axisClass: 'ct-axis-title',
					offset: {
						x: 0,
						y: 80
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

			Chartist.plugins.ctPointLabels({
				textAnchor: 'middle'
			})
		]
	};

	new Chartist.Bar('#chart1', data, options);
</script>