var phabtaskgraph_controller = ( function ( mw, $ ) {
	'use strict';

	return {
		initialize: function () {
			var graph = mw.config.get( 'PhabTaskGraphConfig' );
			this.showGraph(graph.id, graph.selected_tasks, graph.selected_projects,
				graph.nodes, graph.links, graph.projects, graph.people, graph.url,
				graph.width, graph.height);
		},
		showGraph: function (id, selected_tasks, selected_projects, nodes, links,
			projects, people, url, width, height) {
			var dragging = false;
			var selectedNode = -1;

			var linkedByIndex = {};
			links.forEach(function (d) {
				linkedByIndex[d.source + ',' + d.target] = 1;
			});


			var div = d3.select('#' + id);

			var svg = div
				.append('svg')
				.attr('width', width)
				.attr('height', height)
				.attr('class', 'phabtaskgraph-graph');

			var simulation = d3.forceSimulation();
			simulation.force('link',
				d3.forceLink()
					.id(function(d) { return d.id; })
					.distance(100)
				);
			simulation.force('charge', d3.forceManyBody());
			simulation.force('center', d3.forceCenter(width / 2, height / 2));

			svg
				.append('defs')
				.selectAll('marker')
				.data(['arrow'])
				.enter()
				.append('marker')
				.attr('id', function (d) {
					return d;
				})
				.attr('viewBox', '0 0 10 10')
				.attr('refX', 25)
				.attr('refY', 5)
				.attr('markerWidth', 10)
				.attr('markerHeight', 10)
				.attr('orient', 'auto')
				.append('path')
				.attr('d', 'M 0 0 L 10 5 L 0 10 z')
				.attr('class', 'phabtaskgraph-marker');

			var g = svg.append('g');

			var link = g.append('g')
				.attr('class', 'links')
				.selectAll('line')
				.data(links)
				.enter()
				.append('line')
				.attr('class', 'phabtaskgraph-link')
				.style('marker-end', 'url(#arrow)');

			var node = g.append('g')
				.attr('class', 'nodes')
				.selectAll('g')
				.data(nodes)
				.enter()
				.append('g')
				.attr('class', 'phabtaskgraph-node');

			var circle = node
				.append('circle')
				.attr('r', 15)
				.style('fill', function (d) {
					return d.color;
				})
				.attr('stroke-width', 4)
				.attr('stroke', function (d) {
					if (selected_tasks.includes(String(d.id))) {
						return '#00f';
					}
					var key;
					for (key in d.projects) {
						if (selected_projects.includes(key)) {
							return '#00f';
						}
					}
					return d.color;
				})
				.attr('class', 'phabtaskgraph-circle')
				.call(d3.drag()
					.on('start', dragstarted)
					.on('drag', dragged)
					.on('end', dragended));

			node
				.append('text')
				.attr('dx', 20)
				.attr('dy', '.35em')
				.text(function (d) {
					var name = d.name;
					if (name.length > 50) {
						name = name.substring(0,50) + '...';
					}
					return name;
				})
				.attr('class', 'phabtaskgraph-text');

			node
				.on('dblclick', function (d) {
					window.open(url + '/' + d.taskid, '_blank');
				});

			node
				.on('click', function (d) {
					highlightConnected(d);
				});

			var tooltip = div
				.append('div')
				.attr('class', 'phabtaskgraph-tooltip')
				.style('opacity', 0);

			node
				.on('mouseover', function (d) {
					if (dragging) return;
					if (d3.event.target.tagName != 'circle') return;
					if (!d3.event.active) simulation.alphaTarget(0);
					tooltip
						.transition()
						.duration(200)
						.style('opacity', .9);
					tooltip
						.html(format_tooltip(d))
						.style("left", (d3.event.offsetX + 30) + "px")
						.style("top", (d3.event.offsetY - 30) + "px");
				});

			node
				.on('mouseout', function (d) {
					if (!d3.event.active) simulation.alphaTarget(0.3).restart();
					tooltip
						.transition()
						.duration(500)
						.style('opacity', 0);
				});

			var zoom = d3
				.zoom()
				.on('zoom', zoom_actions);
			zoom(svg);
			svg.on('dblclick.zoom', null);

			simulation
				.nodes(nodes)
				.on('tick', ticked);

			simulation
				.force('link')
				.links(links);

			function ticked() {
				link
					.attr('x1', function (d) { return d.source.x; })
					.attr('y1', function (d) { return d.source.y; })
					.attr('x2', function (d) { return d.target.x; })
					.attr('y2', function (d) { return d.target.y; });
				node
					.attr('transform', function (d) {
						return 'translate(' + d.x + ',' + d.y + ')';
					});
			}

			function dragstarted(d) {
				if (!d3.event.active) simulation.alphaTarget(0.3).restart();
				d.fx = d.x;
				d.fy = d.y;
				dragging = true;
			}

			function dragged(d) {
				d.fx = d3.event.x;
				d.fy = d3.event.y;
			}

			function dragended(d) {
				if (!d3.event.active) simulation.alphaTarget(0);
				d.fx = null;
				d.fy = null;
				dragging = false;
			}

			function zoom_actions() {
				g.attr('transform', d3.event.transform);
			}

			function format_tooltip (d) {
				var html = d.taskid + ' (' + d.status + ')<br />';
				html += d.name + '<br />';
				if (d.author) {
					var author = people[d.author];
					html +=
						mw.message( 'phabtaskgraph-author-field-label' ).text() +
 						': ' + author.username;
					if (author.realname) {
						 html += ' (' + author.realname + ')';
					}
					html += '<br />';
				}
				if (d.owner) {
					var owner = people[d.owner];
					html +=
						mw.message( 'phabtaskgraph-owner-field-label' ).text() +
						': ' + owner.username;
					if (owner.realname) {
					 html += ' (' + owner.realname + ')';
					}
					html += '<br />';
				}
				Object.keys(d.projects).forEach( function (key) {
					var color = projects[key].color;
					if (color == 'disabled') {
						color = 'grey';
					}
					html += '<span style="color:' + color + ';">';
					html += d.projects[key];
					html += '</span>';
					html += '<br />';
				});
				return html;
			}

			function neighboring(a, b) {
				return a.id == b.id || linkedByIndex[a.id + ',' + b.id];
			}

			function highlightConnected(d) {
				if (selectedNode == d.id) {
					node.style('opacity', 1);
					link.style('opacity', 1);
					selectedNode = -1;
				} else {
					node.style('opacity', function (o) {
						return neighboring(d, o) | neighboring(o, d) ? 1 : 0.1;
					});
					link.style('opacity', function (o) {
						return d.id==o.source.id | d.id==o.target.id ? 1 : 0.1;
					});
					selectedNode = d.id;
				}
				d3.selectAll('.phabtaskgraph-circle')
					.attr('stroke', function (d) {
						if (selectedNode == d.id) {
							return '#00f';
						} else {
							return d.color;
						}
					});
			}
		}
	};
}( mediaWiki, jQuery ) );

window.PhabTaskGraphController = phabtaskgraph_controller;

( function ( mw, $ ) {
	$( document )
		.ready( function () {
			if ( mw.config.exists( 'PhabTaskGraphConfig' ) ) {
				window.PhabTaskGraphController.initialize();
			}
		} );
}( mediaWiki, jQuery ) );
