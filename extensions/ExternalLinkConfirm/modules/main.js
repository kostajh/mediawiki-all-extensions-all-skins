( function () {
	'use strict';

	var whitelist = mw.config.get( 'ExternalLinkConfirmWhitelist' ),
		target = mw.config.get( 'ExternalLinkConfirmTarget' ),
		defaultTarget = mw.config.get( 'ExternalLinkConfirmDefaultTarget' );

	function getHostname( url ) {
		var a = document.createElement( 'a' );
		a.href = url;
		return a.hostname;
	}

	function getWildcardMatchedDomain( listDomains, domain ) {
		var domainPieces = domain.split( '.' ),
			valid, listedDomain, listedDomainPieces, i, k, piece;

		if ( listDomains.length === 0 ) {
			return false;
		}

		for ( k in listDomains ) {
			if ( Object.prototype.hasOwnProperty.call( listDomains, k ) ) {
				listedDomain = listDomains[ k ];
				listedDomainPieces = listedDomain.split( '.' );
				if ( domainPieces.length === listedDomainPieces.length ) {
					valid = true;
					for ( i = 0; i < listedDomainPieces.length; i++ ) {
						piece = listedDomainPieces[ i ];
						if ( piece !== '*' && piece !== domainPieces[ i ] ) {
							valid = false;
							break;
						}
					}
					if ( valid ) {
						return listedDomain;
					}
				}
			}
		}

		return false;
	}

	function isWhitelistedDomain( domain ) {
		if ( whitelist.length === 0 ) {
			return false;
		}

		return !!getWildcardMatchedDomain( whitelist, domain );
	}

	function getTargetForDomain( domain ) {
		var targetKeys = Object.keys( target ),
			key = getWildcardMatchedDomain( targetKeys, domain );

		if ( key !== false ) {
			return target[ key ];
		}
		return defaultTarget;
	}

	function openLink( href, host, target ) {
		if ( target === undefined ) {
			target = getTargetForDomain( host );
		}

		window.open( href, target );
	}

	function onHandledLinkClick( e ) {
		var href = $( this ).data( 'ExternalLinkConfirmHref' ),
			host = getHostname( href ),
			allowed = isWhitelistedDomain( host ),
			options;

		e.preventDefault();
		if ( allowed ) {
			openLink( href, host );
		} else {
			options = {
				actions: [
					// Note that OO.ui.alert() and OO.ui.confirm() rely on these.
					{ action: 'accept', label: OO.ui.deferMsg( 'externallinkconfirm-confirmation-accept' ), flags: 'primary' },
					{ action: 'reject', label: OO.ui.deferMsg( 'externallinkconfirm-confirmation-reject' ), flags: 'safe' }
				]
			};
			OO.ui.confirm( mw.msg( 'externallinkconfirm-confirmation-message' ), options ).done( function ( confirmed ) {
				if ( confirmed ) {
					openLink( href, host );
				}
			} );
		}
	}

	/**
	 * @param {jQuery} $element
	 */
	function handleExternalLinks( $element ) {
		var $unhandledLinks = $element.find( 'a[href*="//"]:not( .ExternalLinkConfirmHandled )' );

		$unhandledLinks
			.each( function () {
				var $element = $( this ),
					href = $element.attr( 'href' ),
					hrefHost = getHostname( href );

				if ( hrefHost !== window.location.hostname ) {
					$element
						.data( 'ExternalLinkConfirmHref', href )
						.attr( 'href', '' )
						.off( 'click' )
						.on( 'click', onHandledLinkClick );
				}
			} )
			.addClass( 'ExternalLinkConfirmHandled' );
	}

	mw.hook( 'wikipage.content' ).add( handleExternalLinks );
	// eslint-disable-next-line no-jquery/no-global-selector
	handleExternalLinks( $( 'body' ) );

}() );
