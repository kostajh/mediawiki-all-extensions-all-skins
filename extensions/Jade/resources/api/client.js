'use strict';

/**
 * Basic client for calling Jade api modules.
 *
 * @requires jade.widgets.FacetListWidget
 *
 * @class
 * @classdesc Basic client for calling Jade api modules.
 * @license GPL-3.0-or-later
 * @author Andy Craze < acraze@wikimedia.org >
 * @author Kevin Bazira < kbazira@wikimedia.org >
 */

var BaseClient = function BaseClient() {

	var moduleName = this.moduleName;
	var FacetListWidget = require( 'jade.widgets' ).FacetListWidget;

	/**
	 * Check whether current page is a secondary integration page.
	 *
	 * @method isSecondaryIntegrationPage
	 * @description Check whether current page is a secondary integration page.
	 * @return {boolean} Response on whether current page is or is not a secondary integration.
	 */
	var isSecondaryIntegrationPage = function () {
		return [ 'specialDiffPage', 'undoEditPage', 'rollbackPage' ]
			.indexOf( mw.config.values.jadeSecondaryIntegrationPage ) > -1;
	};

	/**
	 * Close open window manager modals.
	 *
	 * @method closeOpenWindowManagerModals
	 * @description Close any open window manager modals.
	 */
	var closeOpenWindowManagerModals = function () {
		// eslint-disable-next-line no-jquery/no-global-selector
		if ( $( '.oo-ui-windowManager-modal' ).length ) {
			// eslint-disable-next-line no-jquery/no-global-selector
			$( '.oo-ui-windowManager-modal' ).remove();
			// eslint-disable-next-line no-jquery/no-global-selector
			$( 'html, body' ).css( {
				overflow: 'auto',
				height: 'auto'
			} );
		}
	};

	/**
	 * Update Jade entity data.
	 *
	 * @method updateJadeEntityData
	 * @description Update Jade entity data in the mw.config object.
	 * @param {Object} data - The latest Jade entity data.
	 */
	var updateJadeEntityData = function ( data ) {
		mw.config.values.entityData = data;
	};

	/**
	 * Update Jade elements on secondary integration page.
	 *
	 * @method updateJadeElementsOnSecondaryIntegrationPage
	 * @description Update Jade elements that are on a secondary integration page.
	 * @param {Object} data - The latest Jade entity data.
	 */
	var updateJadeElementsOnSecondaryIntegrationPage = function ( data ) {
		var secondaryIntegrationPageFacetsList = new FacetListWidget( {
			entityData: data
		} );
		// eslint-disable-next-line no-jquery/no-global-selector
		$( '#jade-secondary-integration' ).html( secondaryIntegrationPageFacetsList.$element );
	};

	/**
	 * Reload the page if no error found in data, otherwise return data.
	 *
	 * @callback requestCallback
	 * @method requestCallback
	 * @description Reload page or return error data.
	 * @param {Object} data - The data returned from api response.
	 * @param {Object} err
	 */
	this.requestCallback = function ( data, err ) {
		if ( !data.error ) {
			var bubbleNotificationMessageKey = moduleName.replace( 'jade', 'jade-' );
			if ( isSecondaryIntegrationPage() ) {
				closeOpenWindowManagerModals();
				updateJadeEntityData( data.data );
				updateJadeElementsOnSecondaryIntegrationPage( data.data );
				mw.notify( mw.message( bubbleNotificationMessageKey ), {
					autoHide: true,
					autoHideSeconds: 6,
					type: 'info'
				} );
			} else {
				sessionStorage.loadBubbleNotificationAfterPageLoad = true;
				sessionStorage.bubbleNotificationMessage = bubbleNotificationMessageKey;
				location.reload();
			}
		} else {
			return data;
		}
	};

	/**
	 * Execute call to MW api.
	 *
	 * @method execute
	 * @description Execute call to MW api.
	 * @param {Object} params - The form data to be sent to api module.
	 * @return {Promise} Promise object represents the api response.
	 */
	this.execute = function ( params ) {
		var cleanedParams = this.buildParams( moduleName, params );
		var api = new mw.Api();
		var res = api.postWithEditToken( cleanedParams ).then( this.requestCallback )
			.catch( function ( err ) { return JSON.stringify( err ); } );
		return res;
	};

};

BaseClient.prototype.moduleName = '';

/**
 * Create an object of cleaned params that are expected by api module.
 *
 * @method buildParams
 * @description Create an object of cleaned params that are expected by api module.
 * @param {string} actionName - The name of the Action Api module to be executed.
 * @param {Object} data - The form data to be sent to api module.
 * @return {Object} Cleaned params that are expected by api module.
 */
BaseClient.prototype.buildParams = function ( actionName, data ) {
	return {
		action: actionName
	};
};

module.exports = BaseClient;
