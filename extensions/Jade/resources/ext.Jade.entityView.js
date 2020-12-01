/**
 * Render Jade Entity page content.
 *
 * @requires jade.widgets.DiffWidget
 * @requires jade.widgets.FacetListWidget
 *
 * @license GPL-3.0-or-later
 * @author Andy Craze < acraze@wikimedia.org >
 * @author Kevin Bazira < kbazira@wikimedia.org >
 */
$( function () {
	'use strict';
	var DiffWidget = require( 'jade.widgets' ).DiffWidget;
	var FacetListWidget = require( 'jade.widgets' ).FacetListWidget;

	// Check if entity page does not exist yet.
	// eslint-disable-next-line no-jquery/no-global-selector
	if ( $( '.noarticletext' )[ 0 ] ) {
	// eslint-disable-next-line no-jquery/no-global-selector
		$( '.noarticletext' ).hide();
	}

	/**
	 * Remove Jade SessionStorage item if it exists.
	 *
	 * @method removeJadeSessionStorageItem
	 * @description Remove Jade SessionStorage item.
	 * @param item
	 */
	this.removeJadeSessionStorageItem = function ( item ) {
		if ( sessionStorage.getItem( item ) ) {
			sessionStorage.removeItem( item );
		}
	};

	// Show bubble notification based on sessionStorage data
	if ( sessionStorage.loadBubbleNotificationAfterPageLoad ) {
		mw.notify( mw.message( sessionStorage.bubbleNotificationMessage ), {
			autoHide: true,
			autoHideSeconds: 6,
			type: 'info'
		} );
		this.removeJadeSessionStorageItem( 'loadBubbleNotificationAfterPageLoad' );
		this.removeJadeSessionStorageItem( 'bubbleNotificationMessage' );
	}

	this.diff = new DiffWidget();

	/**
	 * Load entityData sent from server.
	 * If entityData is empty, then render an empty Jade entity.
	 *
	 * @method loadEntityData
	 * @description Load entityData sent from server.
	 * @return {Object} entity data
	 */
	this.loadEntityData = function () {
		var data = mw.config.get( 'entityData' );
		if ( Object.keys( data ).length === 0 || data === '{}' ) {
			// entityData is empty, so render an empty Jade entity.
			data = { facets: { editquality: { proposals: [] } } };
		}
		return data;
	};

	this.facetsList = new FacetListWidget( {
		entityData: this.loadEntityData()
	} );

	this.stack = new OO.ui.StackLayout( {
		items: [
			new OO.ui.PanelLayout( {
				classes: [ 'jade-entity-diff-panel' ],
				$content: this.diff.$element,
				padded: true,
				scrollable: true,
				expanded: true
			} )
		],
		continuous: true,
		classes: [ 'jade-entity-view-stack' ]
	} );

	var $hrElement = $( '<hr>' ).addClass( 'jade-entity-view-split' );
	var jadeSecondaryIntegrationPage = mw.config.get( 'jadeSecondaryIntegrationPage' );
	var $jadeSecondaryIntegrationElement = $( '<div>' )
		// The following classes are used here:
		// * jade-secondaryIntegrationPage
		// * jade-specialDiffPage
		// * jade-undoEditPage
		// * jade-rollbackPage
		.addClass( 'jade-secondaryIntegrationPage jade-' + jadeSecondaryIntegrationPage )
		.attr( 'id', 'jade-secondary-integration' );
	var $viewportMeta = $( '<meta>' )
		.attr( {
			name: 'viewport',
			content: 'width=device-width, initial-scale=1'
		} );
	// eslint-disable-next-line no-jquery/no-global-selector
	$( 'head' ).append( $viewportMeta );

	if ( jadeSecondaryIntegrationPage === 'specialDiffPage' ) {
		$jadeSecondaryIntegrationElement
			.insertBefore( '#mw-oldid' )
			.append( this.facetsList.$element );
	} else if ( jadeSecondaryIntegrationPage === 'undoEditPage' ) {
		$jadeSecondaryIntegrationElement
			.insertAfter( '#wikiDiff' )
			.append( this.facetsList.$element );
	} else if ( jadeSecondaryIntegrationPage === 'rollbackPage' ) {
		$jadeSecondaryIntegrationElement
			.insertAfter( '#mw-returnto' )
			.append( this.facetsList.$element, $hrElement );
	} else {
		// eslint-disable-next-line no-jquery/no-global-selector
		$( '#mw-content-text' ).append(
			this.stack.$element,
			$hrElement,
			this.facetsList.$element
		);
	}

} );
