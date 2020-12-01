( function ( mw, $ ) {
	/**
	 * Collect image information. Make a ajax request per image or use Resource Timing API and check
	 * the size from the response header.
	 */
	var imageSizeCollector = function runImageSizeCollector() {
		var warningLimitInBytes = 1000000,
			totalSize = 0,
			warnings = 0,
			maxImageNameLength = 60,
			images = [],
			humanSize = module.exports.humanSize;

		function isResourceTimingWithSizeSupported() {
			var wp = window.performance;
			// Right now we don't support Firefox, we did try it out but Firefox
			// has a bug where local cached assets are not included, meaning using it
			// we would miss images for Firefox users:
			// https://bugzilla.mozilla.org/show_bug.cgi?id=1113676
			// we have have at least Resource Timing V1
			if ( wp && wp.getEntriesByType && wp.getEntriesByType( 'resource' ).length > 0 ) {
				// do we have support size but not nextHop?
				// Then we are sure we are not FF but in the long run this
				// check is evil, Chrome and others will support nextHop soon.
				if (
					wp.getEntriesByType( 'resource' )[ 0 ].encodedBodySize &&
					!wp.getEntriesByType( 'resource' )[ 0 ].nextHopProtocol
				) {
					return true;
				}
			}
			return false;
		}

		function getImageName( name ) {
			if ( name.length > maxImageNameLength ) {
				return name.substr( 0, maxImageNameLength - 1 );
			}
			return name;
		}

		function isImage( name ) {
			return ( name.match( /\.(jpeg|jpg|gif|png)$/i ) !== null );
		}

		function fetchUsingRT() {
			var resources = window.performance.getEntriesByType( 'resource' ),
			images = [],
			i;

			for ( i = 0; i < resources.length; i++ ) {
				if ( isImage( resources[ i ].name ) ) {
					images.push( { url: resources[ i ].name,
						contentLength: resources[ i ].encodedBodySize } );
				}
			}
			return images;
		}

		function fetchUsingAjax() {
			var img = document.getElementsByTagName( 'img' ),
			promises = [],
			i;
			function fetchContent( url ) {
				return $.ajax( {
						url: url
					} ).then( function ( data, textStatus, jqXHR ) {
						return {
							url: url,
							contentLength: jqXHR.getResponseHeader( 'Content-Length' )
						};
					} );
			}
			for ( i = 0; i < img.length; i++ ) {
				if ( img[ i ].currentSrc && img[ i ].currentSrc.indexOf( 'data:image' ) === -1 ) {
					promises.push( fetchContent( img[ i ].currentSrc ) );
				}
			}
			return promises;
		}

		function getImages() {
			var promises, deferred = new $.Deferred();

			// The browser supports Resource Timing V2
			if ( isResourceTimingWithSizeSupported() ) {
				promises = fetchUsingRT();
			} else {
				promises = fetchUsingAjax();
			}

			$.when.apply( $, promises ).done( function () {
						var values = arguments,
						sizeRaw,
						i;
						for ( i = 0; i < values.length; i++ ) {
							sizeRaw = Number( values[ i ].contentLength );
							images.push( {
									name: getImageName(
										values[ i ].url.substring( values[ i ].url.lastIndexOf( '/' ) + 1 )
									),
									url: values[ i ].url,
									sizeRaw: sizeRaw,
									size: humanSize( sizeRaw ),
									warning: values[ i ].contentLength > warningLimitInBytes
								} );

							totalSize += sizeRaw;
						}
						images.sort( function ( a, b ) {
							return b.sizeRaw - a.sizeRaw;
						} );
						deferred.resolve( {
							summary: {
								imagesSummary: mw.msg(
									'performanceinspector-modules-summary-images',
									images.length,
									humanSize( totalSize ),
									warnings
								)
							},
							view: {
								name: 'performanceinspector-imagesize-name',
								label: 'performanceinspector-imagesize-label',
								template: mw.template.get( 'ext.PerformanceInspector.analyze', 'imagesize.mustache' ),
								data: {
									totalImageSize: totalSize,
									images: images
								}
							}
						} );
					} );
			return deferred.promise();
		}

		return getImages();

	};
	module.exports.collectors.push( imageSizeCollector );
}( mediaWiki, jQuery ) );
