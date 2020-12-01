var ptg_datemultilinegraph_controller = ( function ( mw, $ ) {
	'use strict';

	return {
		initialize: function () {
			var config = mw.config.get( 'PTG_DateMultiLineGraphConfig' );
			var self = this;
			config.forEach(function(graph) {
				self.showGraph(graph.id, graph.data, graph.width, graph.height,
				graph.xaxis, graph.yaxis);
			});
		},
		showGraph: function (id, data, width, height, xaxis, yaxis) {
			var margin = {top: 20, right: 20, bottom: 100, left: 50};
			width = width - margin.left - margin.right;
			height = height - margin.top - margin.bottom;

			var x = d3.scaleTime().range([0, width]);
			var y = d3.scaleLinear().range([height, 0]);

			var div = d3.select('#' + id);

			var colorScale = d3.scaleOrdinal(d3.schemeDark2);

			var tablerow = div
				.append('table')
				.append('tr');

			var graphcell = tablerow
				.append('td');

			this.appendLegend(tablerow, data, colorScale);

			var svg = graphcell
				.append('svg')
				.attr('width', width + margin.left + margin.right)
				.attr('height', height + margin.top + margin.bottom)
				.append('g')
				.attr('transform',
					'translate(' + margin.left + ',' + margin.top + ')');

			var parseTime = d3.timeParse('%Y-%m-%d');
			var xvalues = [];
			var yvalues = [];
			data.forEach(function(d) {
				d.data.forEach(function(d) {
					d[0] = parseTime(d[0]);
					xvalues.push(d[0]);
					yvalues.push(d[1]);
				});
			});

			x.domain(d3.extent(xvalues));
			y.domain([0, d3.max(yvalues)]);

			data.forEach(function(d,i) {

				var valueline = d3.line()
					.x(function(d) {return x(d[0]);})
					.y(function(d) {return y(d[1]);});

				svg.append("path")
					.data([d.data])
					.attr('d', valueline)
					.style('fill', 'none')
					.style('stroke-width', 2)
					.style('stroke', colorScale(i));

				svg.selectAll('line-circle')
					.data(d.data)
					.enter()
					.append('circle')
					.attr('r', 2)
					.style('stroke', colorScale(i))
					.style('fill', colorScale(i))
					.attr('cx', function(d) {return x(d[0]);})
					.attr('cy', function(d) {return y(d[1]);});
			});

			svg.append('g')
				.attr('transform', 'translate(0,' + height + ')')
				.call(d3.axisBottom(x)
					.tickFormat(d3.timeFormat('%Y-%m-%d')))
				.selectAll('text')
					.style('text-anchor', 'end')
					.attr('dx', '-.8em')
					.attr('dy', '.15em')
					.attr('transform', 'rotate(-65)');


			svg.append('g')
				.attr('class', 'grid')
				.attr('transform', 'translate(0,' + height + ')')
				.call(d3.axisBottom(x)
					.ticks(7)
					.tickSize(-height)
					.tickFormat(''));

			if (xaxis) {
				svg.append('text')
					.attr('x', width / 2)
					.attr('y', height + margin.top + 60)
					.style('text-anchor', 'middle')
					.text(xaxis);
			}

			svg.append('g')
				.call(d3.axisLeft(y));

			svg.append('g')
				.attr('class', 'grid')
				.call(d3.axisLeft(y)
					.ticks(5)
					.tickSize(-width)
					.tickFormat(''));

			if (yaxis) {
				svg.append('text')
					.attr('transform', 'rotate(-90)')
					.attr('y', 0 - margin.left)
					.attr('x', 0 - (height / 2))
					.attr('dy', '1em')
					.style('text-anchor', 'middle')
					.text(yaxis);
			}
		},
		appendLegend: function (tablerow, data, colorScale) {
			if (data.length < 2) {
				return;
			}

			var legendtable = tablerow
				.append('td')
				.style('vertical-align', 'top')
				.style('text-align', 'left')
				.append('table');

			data.forEach(function(d,i) {
				legendtable
					.append('tr')
					.append('td')
					.append('span')
					.style('color', colorScale(i))
					.style('line-height', '1em')
					.style('font-weight', 'bold')
					.html('<span style="font-size:x-large;">&middot;</span> ' + d.name);
			});
		}

	};
}( mediaWiki, jQuery ) );

window.PTG_DateMultiLineGraphController = ptg_datemultilinegraph_controller;

( function ( mw, $ ) {
	$( document )
		.ready( function () {
			if ( mw.config.exists( 'PTG_DateMultiLineGraphConfig' ) ) {
				window.PTG_DateMultiLineGraphController.initialize();
			}
		} );
}( mediaWiki, jQuery ) );
