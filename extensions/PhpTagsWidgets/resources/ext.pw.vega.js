/**
 *
 */
( function ( $, mw, vg ) {
	'use strict';

	$.fn.extpwVega = function ( $data ) {
		vg.config.safeMode = true;
		vg.config.domainWhiteList = mw.config.get( 'ext.phptags.Widgets' ).vega.domainWhiteList;
		this.each( function ( index, element ) {
			vg.parse.spec( $data, function ( chart ) { chart( { el: element } ).update(); } );
		} );
	};

}( jQuery, mediaWiki, vg ) );
