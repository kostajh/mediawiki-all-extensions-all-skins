/**
 * @licence GNU GPL v2+
 * @author Simon A. Eugster <simon.eu@gmail.com>
 */

console.log( 'Editor .js loaded' );

$().ready( function ( mw ) {

	i18n.init( {

		lng: mw.config.get( 'wgUserLanguage' ),
		fallbackLng: 'en',
		useCookie: false,
		debug: true,
		ns: 'translation',
		resGetPath: '../../extensions/LifeWebCore/resources/script/locales/__lng__/__ns__.json'

	}, function () {

		var $dom = $( '#editor' );
		$dom.empty();
		LWE.UI.init( $dom );

	} );
}( mediaWiki ) );
