/**
 * @licence GNU GPL v2+
 * @author Simon A. Eugster <simon.eu@gmail.com>
 */

$().ready( function ( mw ) {

	i18n.init( {

		lng: mw.config.get( 'wgUserLanguage' ),
		fallbackLng: 'en',
		useCookie: false,
		debug: true,
		ns: 'translation',
		resGetPath: '../../extensions/LifeWebCore/resources/script/locales/__lng__/__ns__.json'

	}, function ( t ) {

		var $dom = $( '#filter' );
		$dom.empty();

		new LWF.KeyUi( $dom, {
			dKey: {
				taxonPrefilters: [ 'selectedDegrees', 'selectedTaxa', 'nameFilter' ]
			}
		}, t );

	} );

}( mediaWiki ) );
