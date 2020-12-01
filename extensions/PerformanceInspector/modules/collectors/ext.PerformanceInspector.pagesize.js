( function ( mw ) {
	/**
	 * Collect the size of the page using the Navigation Timing API v2
	 */
	var pageSizeCollector = function runPageSizeCollector() {
			var humanSize = module.exports.humanSize,
			entry,
			wp = window.performance;

			if ( wp && wp.getEntriesByType && wp.getEntriesByType( 'navigation' ) ) {
				entry = wp.getEntriesByType( 'navigation' )[ 0 ];
			}

			if ( !entry ) {
				return {};
			}

			return {
				summary: {
						pageSize: mw.msg( 'performanceinspector-pagesize-summary', humanSize( entry.decodedBodySize ), humanSize( entry.transferSize ) )
					}
			};
		};

	module.exports.collectors.push( pageSizeCollector );
}( mediaWiki ) );
