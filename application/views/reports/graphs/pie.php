<style>
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
		series: [<?php echo $series_data_1; ?>]
	};

	var sum = function(a, b) { return a + b };
	
	// We are setting a few options for our chart and override the defaults
	var options = {
		chartPadding: 50,
		labelPosition: 'outside',
		// interpolate labels to show lable, value and %
		labelInterpolationFnc: function(label, index) {
			return label + ": " + data.series[index] + " / " + Math.round(data.series[index] / data.series.reduce(sum) * 100) + '%';
		}
	};
	
/*	var responsiveOptions = [
		['screen and (min-width: 640px)', {
			chartPadding: 30,
			labelOffset: 100,
			labelDirection: 'explode',
			labelInterpolationFnc: function(value) {
				return value;
			}
		}],
		['screen and (min-width: 1024px)', {
			labelOffset: 80,
			chartPadding: 20
		}]
	];*/

	new Chartist.Pie('#chart1', data, options/*, responsiveOptions*/);
</script>