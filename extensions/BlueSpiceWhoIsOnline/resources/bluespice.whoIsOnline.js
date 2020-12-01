/**
 * Js for WhoIsOnline extension
 *
 * @author     Patric Wirth
 * @package    Bluespice_Extensions
 * @subpackage WhoIsOnline
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */
( function( mw, $, bs, d ){
	var interval = mw.config.get( 'bsgWhoIsOnlineInterval', 0 ) * 1000;
	if ( interval < 1 ) {
		return;
	}
	var listener = function( result, Listener ) {
		if( result.success !== true ) {
			return;
		}

		$( '.bs-whoisonline-portlet' ).each( function(){
			var portlet = result['portletItems'];
			$( this ).html( portlet );
		});

		$( '.bs-whoisonline-count').each( function(){
			$( this ).html( result['count'] );
		} );

		BSPing.registerListener( 'WhoIsOnline', interval, [], listener );
	}
	BSPing.registerListener( 'WhoIsOnline', interval, [], listener );

} )( mediaWiki, jQuery, blueSpice, document );