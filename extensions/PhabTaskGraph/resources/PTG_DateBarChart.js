var ptg_datebarchart_controller = ( function ( mw, $ ) {
	'use strict';

	return {
		initialize: function () {
			var config = mw.config.get( 'PTG_DateBarChartConfig' );
			var self = this;
			config.forEach(function(chart) {
				self.showChart(chart.id, chart.data, chart.width, chart.height,
					chart.xaxis, chart.yaxis);
			});
		},
		showChart: function (id, data, width, height, xaxis, yaxis) {
			var margin = {top: 20, right: 20, bottom: 100, left: 50};
			width = width - margin.left - margin.right;
			height = height - margin.top - margin.bottom;

			var x = d3.scaleBand().range([0, width]).padding(0.1);
			var y = d3.scaleLinear().range([height, 0]);

			var div = d3.select('#' + id);

			var svg = div
				.append('svg')
				.attr('width', width + margin.left + margin.right)
				.attr('height', height + margin.top + margin.bottom)
				.append('g')
				.attr('transform',
					'translate(' + margin.left + ',' + margin.top + ')');

			var parseTime = d3.timeParse('%Y-%m-%d');
			data.forEach(function(d) {
				d[0] = parseTime(d[0]);
				d[1] = +d[1];
			});

			x.domain(data.map(function(d) { return d[0]; }));
			y.domain([0, d3.max(data, function(d) { return d[1] })]);

 			svg.selectAll('bar')
				.data(data)
				.enter()
				.append('rect')
				.style('fill', 'steelblue')
				.attr('x', function(d) { return x(d[0]); })
				.attr('width', x.bandwidth())
				.attr('y', function(d) { return y(d[1]); })
				.attr('height', function(d) { return height - y(d[1]); });

			svg.selectAll('barlabels')
				.data(data)
				.enter()
				.append('text')
				.text(function (d) { return d[1]; })
				.attr('x',
					function (d) { return x(d[0]) + x.bandwidth() / 2 - 10; })
				.attr('y', function (d) { return y(d[1]) + 20; })
				.style('fill', 'white');

			svg.append('g')
				.attr('transform', 'translate(0,' + height + ')')
				.call(d3.axisBottom(x)
					.tickFormat(d3.timeFormat('%Y-%m-%d')))
				.selectAll('text')
					.style('text-anchor', 'end')
					.attr('dx', '-.8em')
					.attr('dy', '.15em')
					.attr('transform', 'rotate(-65)');

			if (xaxis) {
				svg.append('text')
					.attr('x', width / 2)
					.attr('y', height + margin.top + 60)
					.style('text-anchor', 'middle')
					.text(xaxis);
			}

			svg.append('g')
				.call(d3.axisLeft(y));

			if (yaxis) {
				svg.append('text')
					.attr('transform', 'rotate(-90)')
					.attr('y', 0 - margin.left)
					.attr('x', 0 - (height / 2))
					.attr('dy', '1em')
					.style('text-anchor', 'middle')
					.text(yaxis);
			}
		}
	};
}( mediaWiki, jQuery ) );

window.PTG_DateBarChartController = ptg_datebarchart_controller;

( function ( mw, $ ) {
	$( document )
		.ready( function () {
			if ( mw.config.exists( 'PTG_DateBarChartConfig' ) ) {
				window.PTG_DateBarChartController.initialize();
			}
		} );
}( mediaWiki, jQuery ) );
