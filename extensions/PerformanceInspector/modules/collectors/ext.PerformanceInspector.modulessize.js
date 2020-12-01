( function ( mw ) {

	var moduleCollector = function runModuleCollector( data ) {
		var totalSize = 0,
			barMetrics = [],
			modulesTemplate = mw.template.get( 'ext.PerformanceInspector.analyze', 'modulessize.mustache' ),
			humanSize = module.exports.humanSize;

		// Add the data needed for generating the bar chart
		data.inspect.modules.forEach( function ( module ) {
			if ( module.sizeInBytes !== null ) {
				barMetrics.push( {
					name: module.name,
					size: humanSize( module.sizeInBytes ),
					sizeInBytes: module.sizeInBytes
				} );
				totalSize += module.sizeInBytes;
			}
		} );

		function postProcess( html ) {
			var generate = require( 'ext.PerformanceInspector.analyze' ).barChart;
			generate( html, '.bar' );
		}
		return {
			summary: {
				modulesSummarySize: humanSize( totalSize )
			},
			view: {
				name: 'performanceinspector-modules-size-name',
				label: 'performanceinspector-modules-size-label',
				template: modulesTemplate,
				postProcess: postProcess,
				data: {
					modules: data.inspect.modules,
					series: barMetrics
				}
			}
		};
	};

	module.exports.collectors.push( moduleCollector );
}( mediaWiki ) );
