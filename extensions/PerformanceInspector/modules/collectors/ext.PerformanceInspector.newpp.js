/* global moment */
( function ( mw ) {

	var newppCollector = function runNewPPCollector() {
		var template = mw.template.get( 'ext.PerformanceInspector.analyze', 'newpp.mustache' ),
		report, parserReportSummary, mustacheView;

		function generateMustacheView( parserReport ) {
			var limitReport = [],
				timingProfile = [],
				templates =
					parserReport.limitreport &&
					parserReport.limitreport.timingprofile ||
					[],
				i, j, data, holder, item, cacheTime, ttlHuman;

			// Push the data for the limit report
			if ( parserReport.limitreport ) {
				Object.keys( parserReport.limitreport ).forEach( function ( key ) {
					if ( key !== 'timingprofile' ) {
						item = parserReport.limitreport[ key ];
						limitReport.push( { name: mw.msg( 'performanceinspector-newpp-' + key ), value: item.limit ? mw.msg( 'performanceinspector-newpp-value-and-limit', item.value, item.limit ) : item } );
					}
				} );
			}

			// TODO we could either fix the raw data to be JSON or make this in a regexp as long
			// as we know the output pattern
			for ( i = 0; i < templates.length; i++ ) {
				data = templates[ i ].split( ' ' );
				holder = [];
				for ( j = 0; j < data.length; j++ ) {
					if ( data[ j ] !== '' ) {
						holder.push( data[ j ] );
					}
				}
				timingProfile.push( {
					name: holder[ 3 ],
					percentreal: holder[ 0 ],
					real: holder[ 1 ],
					calls: holder[ 2 ]
				} );
			}

			cacheTime = moment( parserReport.cachereport.timestamp, 'YYYYMMDDHHmmss' );
			ttlHuman = parserReport.cachereport.ttl === 0 ? mw.msg( 'performanceinspector-newpp-cachereport-now' ) : moment.duration( parserReport.cachereport.ttl ).humanize();

			return { limitReport: limitReport,
				timingProfile: timingProfile,
				scribunto: parserReport.scribuntu,
				cacheReport: {
					timestamp: cacheTime.format( 'lll' ),
					ttl: parserReport.cachereport.ttl,
					ttlHuman: ttlHuman,
					transientcontent: parserReport.cachereport.transientcontent,
					origin: parserReport.cachereport.origin
				}
			};
		}

		// in some cases we don't have the wgPageParseReport, see https://phabricator.wikimedia.org/T145717
		report = mw.config.get( 'wgPageParseReport', {} );
		parserReportSummary = report.limitreport ? mw.msg( 'performanceinspector-newpp-summary', report.limitreport.expensivefunctioncount.value ) : mw.msg( 'performanceinspector-newpp-missing-report' );
		mustacheView = report.limitreport ? generateMustacheView( report ) : '';

		return {
			summary: {
				parserReportSummary: parserReportSummary
			},
			view: {
				name: 'performanceinspector-newpp-name',
				label: 'performanceinspector-newpp-label',
				template: template,
				data: {
					report: mustacheView
				}
			}
		};
	};
	module.exports.collectors.push( newppCollector );
}( mediaWiki ) );
