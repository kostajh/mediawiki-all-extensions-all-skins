( function ( mw, $ ) {
	QUnit.module( 'ext.performanceInspector', QUnit.newMwEnvironment() );

	QUnit.test( 'Test collector structure', function ( assert ) {
				var collectors = require( 'ext.PerformanceInspector.analyze' ).collectors,
				promises = [],
				dummyModuleData = {
						inspect: {
							modules: [ {
								name: 'ext.centralauth.centralautologin',
								size: '1.8 KiB',
								sizeInBytes: 1830
							} ],
							css: [ {
								allSelectors: 262,
								matchedSelectors: 11,
								module: 'site',
								percentMatched: '4.20%'
							} ],
							store: [ {
								enabled: true,
								expired: 2,
								hits: 98,
								misses: 3,
								totalSize: '1.4 MiB'
							} ]
						}
					};

				mw.config.set( 'wgPageParseReport', { limitreport: {
					cputime: '4.255',
					walltime: '4.769',
					ppvisitednodes: {
							value: 37554,
							limit: 1000000
						},
					ppgeneratednodes: {
							value: 0,
							limit: 1500000
						},
					postexpandincludesize: {
							value: 1610819,
							limit: 2097152
						},
					templateargumentsize: {
							value: 206426,
							limit: 2097152
						},
					expansiondepth: {
							value: 17,
							limit: 40
						},
					expensivefunctioncount: {
							value: 20,
							limit: 500
						},
					timingprofile: [
							'100.00% 3961.870      1 -total',
							' 63.94% 2533.225      1 Template:Reflist',
							' 35.17% 1393.228    363 Template:Cite_news',
							' 12.99%  514.694    140 Template:Cite_web',
							'  7.98%  316.089      2 Template:Navboxes',
							'  7.68%  304.127      1 Template:Infobox_president',
							'  7.26%  287.584      7 Template:Infobox',
							'  7.03%  278.476     18 Template:Navbox',
							'  3.08%  122.189     17 Template:Infobox_officeholder/office',
							'  2.60%  103.177     50 Template:Flagicon'
					]
				},
					scribunto: {
									'limitreport-timeusage': {
													value: '2.255',
													limit: '10.000'
												},
									'limitreport-memusage': {
													value: 10231838,
													limit: 52428800
												}
								},
					EntityAccessCount: 1,
					cachereport: {
									origin: 'mw1248',
									timestamp: '20160811033932',
									ttl: 3600,
									transientcontent: true
								}
				} );

				assert.ok( collectors.length > 0, 'We should pick up the collectors' );

				collectors.forEach( function ( collector ) {
					promises.push( collector( dummyModuleData ) );
				} );

				// instead of Promise.all, is there a better way of doing it with jQuery?
				$.when.apply( $, promises ).then( function () {
					$.makeArray( arguments ).forEach( function ( result ) {
						var name = result.view && result.view.name ? result.view.name : 'unknown';
						assert.ok( result.view, 'Each collector should produce a view ' + name );
						assert.ok( result.view.name, 'Each view should have a name ' + name );
						assert.ok( result.view.label, 'Each view should have a label ' + name );
						assert.ok( result.view.template, 'Each view should have a mustache template ' + name );
						assert.ok( result.view.data, 'Each view should have some data to show ' + name );
						assert.ok( result.summary, 'Each collector should produce a summary ' + name );
					} );
				} );
			} );

}( mediaWiki, jQuery ) );
