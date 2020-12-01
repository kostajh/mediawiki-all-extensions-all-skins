/*!
 * @license GPL.2-0+
 */

$( function () {
	'use strict';

	var $checkbox = $( '#mw-input-wpcmethod-talkpage-elsewhere input' );

	function toggle() {
		$( '#mw-input-wpcmethod-talkpage-elsewhere-loc' ).toggle( $checkbox.prop( 'checked' ) );
	}

	toggle();
	$checkbox.change( toggle );
} );
