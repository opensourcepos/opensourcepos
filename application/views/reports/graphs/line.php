<style>
/* style X axis labels to be rotated of 60 degrees */
.ct-label.ct-horizontal {
	/* Safari */
	-webkit-transform: rotate(-60deg);

	/* Firefox */
	-moz-transform: rotate(-60deg);

	/* IE */
	-ms-transform: rotate(-60deg);

	/* Opera */
	-o-transform: rotate(-60deg);

	/* Internet Explorer */
	filter: progid:DXImageTransform.Microsoft.BasicImage(rotation=3);
}

/* set all lables to be black with font size 1.2rem */
.ct-label {
	fill: rgba(0,0,0,1);
	color: rgba(0,0,0,1);
	font-size: 1.2rem;
}
</style>

<script>
	// Labels and data series
	var data = {
		labels: [<?php echo $labels_1; ?>],
		series: [{
			name: '<?php echo $yaxis_title; ?>',
			data: [<?php echo $series_data_1; ?>]
		}]
	};

	// We are setting a few options for our chart and override the defaults
	var options = {
		// Draw the line chart points
		showPoint: true,

		// Disable line smoothing
		lineSmooth: false,

		// Padding of the chart drawing area to the container element and labels as a number or padding object {top: 5, right: 5, bottom: 5, left: 5}
		/*chartPadding: {
			top: 15,
			right: 15,
			bottom: 20,
			left: 10
		},*/

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
//			labelInterpolationFnc: function(value) {
//				return '$' + value;
//			}
		},

		// plugins configuration
		plugins: [
			Chartist.plugins.ctAxisTitle({
				axisX: {
					axisTitle: '<?php echo $xaxis_title; ?>',
					axisClass: 'ct-axis-title',
					offset: {
						x: 0,
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
			Chartist.plugins.ctPointLabels({
				textAnchor: 'middle'
			})
		]
	};

	new Chartist.Line('#chart1', data, options);
</script>