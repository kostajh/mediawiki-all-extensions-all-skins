( function ( mw, $ ) {
	var moduleCollector = function runModuleCollector( data ) {
		var modulesTemplate;

		function dashCase( str ) {
			return str.replace( /([A-Z])/g, function ( all, letter ) {
				return '-' + letter.toLowerCase();
			} );
		}

		function toImageView( name, stats, index ) {
			if ( !stats.unused.length ) {
				return null;
			}
			return {
				name: name,
				used: mw.msg(
						'performanceinspector-modules-css-used-selectors-values',
						stats.total !== 0 ? ( stats.used / stats.total * 100 ).toFixed( 2 ) + '%' : '100%',
						stats.used,
						stats.total
					),
				results: stats.unused,
				index: index
			};
		}

		function toView( name, stats, index ) {
			return {
				name: name,
				selectors: mw.msg(
						'performanceinspector-modules-css-used-selectors-values',
						stats.total !== 0 ? ( stats.matched / stats.total * 100 ).toFixed( 2 ) + '%' : null,
						stats.matched,
						stats.total
					),
				unmatchedSelectors: stats.unmatched,
				index: index
			};
		}

		function analyze( css ) {
			var result = {
					rules: {
						total: 0, matched: 0, unmatched: []
					},
					images: {
						total: 0, used: 0, unused: []
					}
				},
				style = document.createElement( 'style' ),
				// Only match embedded data URIs.
				urlRegex = /url\(\s*['"]?(data:[^)'"]*?)['"]?\s*\)/g;
			style.textContent = css;
			document.body.appendChild( style );
			$.each( style.sheet.cssRules, function ( index, rule ) {
				var i, prop, urlMatches, images = [];
				// Only pickup CSSStyleRule
				if ( rule.style && rule.selectorText ) {
					result.rules.total++;
					// CSSStyleDeclaration objects have all possible keys predefined.
					// In Firefox it even predefines variations of the same property
					// (e.g. background-image and backgroundImage). And, they're all
					// enumerable. Instead, loop over .length, since the object also
					// has a built-in numerical map of the keys that were actually
					// authored in the stylesheet (and no duplicates here).
					for ( i = 0; i < rule.style.length; i++ ) {
						prop = rule.style[ i ];
						if ( prop === 'cssText' ) {
							continue;
						}
						// Ignore default/empty values and numerical values
						urlMatches = rule.style[ prop ] &&
							typeof rule.style[ prop ] === 'string' &&
							rule.style[ prop ].match( urlRegex );
						if ( urlMatches ) {
							images.push( {
								prop: dashCase( prop ),
								value: String( rule.style[ prop ] )
							} );
						}
					}
					result.images.total += images.length;

					// document.querySelector() on prefixed pseudo-elements can throw exceptions
					// in Firefox and Safari. Ignore these exceptions.
					// https://bugs.webkit.org/show_bug.cgi?id=149160
					// https://bugzilla.mozilla.org/show_bug.cgi?id=1204880
					try {
						if ( document.querySelector( rule.selectorText ) !== null ) {
							result.rules.matched++;
							result.images.used += images.length;
						} else {
							result.rules.unmatched.push( rule.selectorText );
							$.each( images, function ( i, match ) {
								result.images.unused.push( {
									selector: rule.selectorText,
									prop: match.prop,
									value: match.value
								} );
							} );
						}
					} catch ( e ) {}
				}
			} );
			document.body.removeChild( style );
			return result;
		}

		function cssResourceLoader( auditFn, viewFn ) {
			var modules = [];
			$.each( data.inspect.modules, function ( index, mod ) {
				var css, stats, viewData, module = mw.loader.moduleRegistry[ mod.name ];
				try {
					css = module.style.css.join();
				} catch ( e ) { return; } // skip

				stats = auditFn( css );
				viewData = viewFn( mod.name, stats, index );
				if ( viewData ) {
					modules.push( viewData );
				}
			} );
			return modules;
		}

		// Not sure if we want this for the first release
		function cssFromURL( auditer, viewFn ) {
			var styles = document.styleSheets,
			cssFromFiles = [];

			$.each( styles, function ( index, style ) {
				var stats, viewData,
					allStyles = '';

				// We have styles from a link rel="stylesheet" href
				// it would be cool if we could separated inline CSS
				// from resource loaded CSS?
				if ( style.href && style.cssRules ) {
					$.each( style.cssRules, function ( index, rule ) {
						allStyles += rule.cssText;
					} );

					stats = auditer( allStyles );
					viewData = viewFn( style.href, stats, index );
					if ( viewData ) {
						cssFromFiles.push( viewData );
					}
				}
			} );

			return cssFromFiles;
		}

		function auditSelectors( css ) {
			return analyze( css ).rules;
		}

		function auditImages( css ) {
			return analyze( css ).images;
		}

		function postProcess( $html ) {

			$html.find( '.mw-pi-button' ).each( function () {
				var button = new OO.ui.ButtonWidget( {
						label: mw.msg( 'performanceinspector-modules-css-show-details' ),
						icon: 'info',
						title: mw.msg( 'performanceinspector-modules-css-show-details' )
					} );
				button.on( 'click', function () {
						$( button.$element ).closest( 'tr' ).next( 'tr.toggleable' ).toggle();
					} );
				$( this ).replaceWith( button.$element );
			} );
		}

		modulesTemplate = mw.template.get( 'ext.PerformanceInspector.analyze', 'modulescss.mustache' );

		return {
			summary: {
			},
			view: {
				name: 'performanceinspector-modules-css-name',
				label: 'performanceinspector-modules-css-label',
				template: modulesTemplate,
				postProcess: postProcess,
				data: {
					cssResourceLoader: cssResourceLoader( auditSelectors, toView ),
					cssFromFiles: cssFromURL( auditSelectors, toView ),
					cssUnusedEmbeds: cssResourceLoader( auditImages, toImageView )
						.concat( cssFromURL( auditImages, toImageView ) )
				}
			}
		};
	};

	module.exports.collectors.push( moduleCollector );
}( mediaWiki, jQuery ) );
