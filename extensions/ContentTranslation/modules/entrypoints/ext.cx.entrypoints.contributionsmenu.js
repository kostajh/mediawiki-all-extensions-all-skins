/*!
 * Content Translation invitation from the 'contributions' link in pages.
 *
 * @copyright See AUTHORS.txt
 * @license GPL-2.0-or-later
 */
( function () {
	'use strict';

	var CAMPAIGN = 'contributionsmenu';

	function getTranslationsItem() {
		var $link, cxUrlParams = {
			campaign: CAMPAIGN,
			to: mw.config.get( 'wgContentLanguage' )
		};

		$link = $( '<a>' )
			.text( mw.msg( 'cx-campaign-contributionsmenu-mytranslations' ) )
			.prop( 'href', mw.util.getUrl( 'Special:ContentTranslation', cxUrlParams ) );

		return $( '<li>' )
			.addClass( 'cx-campaign-translations' )
			.append( $link );
	}

	function attachMenu( $trigger ) {
		var $myContributions, $myTranslations, $myUploads,
			$menu, callout;

		$myContributions = $( '<li>' )
			.addClass( 'cx-campaign-contributions' )
			.append( $( '<a>' )
				.text( mw.msg( 'cx-campaign-contributionsmenu-mycontributions' ) )
				.attr( 'href', $trigger.find( 'a' ).attr( 'href' ) )
			);

		$myTranslations = getTranslationsItem();

		if ( $( '.mw-special-Preferences' ).length ) {
			$myTranslations.addClass( 'cx-campaign-new-beta-feature' );
		}

		$myUploads = $( '<li>' )
			.addClass( 'cx-campaign-uploads' )
			.append( $( '<a>' )
				.text( mw.msg( 'cx-campaign-contributionsmenu-myuploads' ) )
				.attr( 'href', '//commons.wikimedia.org/wiki/Special:MyUploads' )
			);

		$menu = $( '<ul>' )
			.append( $myContributions, $myTranslations, $myUploads );

		$trigger.callout( {
			trigger: 'hover',
			classes: 'cx-campaign-contributionsmenu',
			direction: $.fn.callout.autoDirection( '1' ),
			content: $menu
		} );

		callout = $trigger.data( 'callout' );

		mw.hook( 'mw.cx.betafeature.enabled' ).add( function () {
			// Show after a few milliseconds to get all position calculation correct
			setTimeout( function () {
				callout.show();
			}, 500 );
			mw.hook( 'mw.cx.cta.shown' ).fire( CAMPAIGN );
		} );

	}

	function showFeatureDiscovery( $trigger ) {
		var fd, $container = $( '<div>' ).addClass( 'cx-feature-discovery-container' );

		$trigger.append( $container );
		fd = new mw.cx.ui.FeatureDiscoveryWidget( {
			title: mw.msg( 'cx-feature-discovery-title' ),
			content: mw.msg( 'cx-feature-discovery-content' ),
			dismissLabel: mw.msg( 'cx-feature-discovery-dismiss' ),
			$container: $container,
			onClose: function () {
				// After dismissing the informative dialog, the action should be continued
				// and Contributions page opened
				location.href = $trigger.find( 'a' ).attr( 'href' );
			}
		} );
		$container.append( fd.$element );
		$trigger.one( 'click', function () {
			var api = new mw.Api();
			// Prevent default click action.
			fd.show();
			// Never show this again.
			api.postWithToken( 'csrf', {
				action: 'globalpreferences',
				optionname: 'cx-entrypoint-fd-status',
				optionvalue: 'shown'
			} ).then( function ( res ) {
				if ( res.error ) {
					mw.log.error( res.error );
				}
			} );
			return false;
		} );
	}

	$( function () {
		var $trigger = $( '#pt-mycontris' );

		if ( mw.config.get( 'wgContentTranslationEntryPointFD' ) ) {
			mw.loader.using( 'mw.cx.ui.FeatureDiscoveryWidget' ).then( function () {
				showFeatureDiscovery( $trigger );
			} );
		} else {
			attachMenu( $trigger );
		}

		// Change the menu when creating a new article using VE
		mw.hook( 've.activationComplete' ).add( function () {
			// Rebuild menu
			$trigger.removeData( 'callout' );
			attachMenu( $trigger );
		} );
	} );
}() );
