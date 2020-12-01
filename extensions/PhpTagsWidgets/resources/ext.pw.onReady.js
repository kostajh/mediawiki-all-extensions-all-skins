/**
 *
 */
( function ( $, mw ) {
	'use strict';

	function onReady() {
		var $phptagsWidgets = mw.config.get( 'ext.phptags.Widgets' ),
			$prefix = $phptagsWidgets.prefix,
			$whenReady = $phptagsWidgets.whenReady,
			$data,
			$k,
			mwPhpTagsWidgetsDoIt = function ( $fn ) {
				for ( $k in $fn ) {
					if ( $fn.hasOwnProperty( $k ) ) {
						$data = ( $phptagsWidgets.data && $phptagsWidgets.data[ $k ] ) || {};
						$.fn[ $fn[ $k ] ].apply( $( '.' + $prefix + $k ), $data );
					}
				}
			},
			$key;

		if ( $whenReady ) {
			for ( $key in $whenReady ) {
				if ( $whenReady.hasOwnProperty( $key ) ) {
					$.when(
						$whenReady[ $key ].fn,
						$.ready,
						mw.loader.using( $whenReady[ $key ].modules )
					).done(
						mwPhpTagsWidgetsDoIt
					);
				}
			}
		}

		// The code below is needed only for compatibility with the previous version,
		// Without it pages from the cache will be broken :-(
		if ( $phptagsWidgets.wait && $phptagsWidgets.onReady ) {
			$.when(
				$.ready,
				mw.loader.using( $phptagsWidgets.wait )
			).done(
				function () {
					for ( $key in $phptagsWidgets.onReady ) {
						if ( $phptagsWidgets.onReady.hasOwnProperty( $key ) ) {
							$.fn[ $phptagsWidgets.onReady[ $key ] ].apply( $( '.' + $prefix + $key ), $phptagsWidgets.data[ $key ] );
						}
					}
				}
			);
		}
	}

	$( document ).ready( onReady );

}( jQuery, mediaWiki ) );
