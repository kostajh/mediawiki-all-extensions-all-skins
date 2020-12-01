( ( function ( $, mw ) {

	'use strict';

	/**
	 * Popup alerts manager class
	 * @class
	 * @constructor
	 */
	function PopupAlertsManager() {
		this.popupSources = [];
		this.activePopup = null;
		this.bindHandlers();
		this.findPopups();
		this.maybeDisplay();
	}

	/**
	 * Cookie prefix constant
	 * @constant
	 * @static
	 * @type {string}
	 */
	PopupAlertsManager.cookiePrefix = 'popup-alerts-';

	/**
	 * Binds necessary event handlers
	 */
	PopupAlertsManager.prototype.bindHandlers = function () {
		$( document ).on( 'keyup', this.onKeyUp.bind( this ) );
	};

	/**
	 * Keyup handler to support ESC key
	 * @param {Event} event
	 */
	PopupAlertsManager.prototype.onKeyUp = function ( event ) {
		if ( this.activePopup && this.activePopup.isVisible && event.keyCode === 27 ) {
			this.activePopup.closePopup();
			this.activePopup = null;
		}
	};

	/**
	 * Looks for popups on the page
	 */
	PopupAlertsManager.prototype.findPopups = function () {
		var $sources = $( '.popup-alerts-source' ),
			self = this;
		if ( $sources.length ) {
			$sources.each( function ( i, source ) {
				self.popupSources.push( new mw.PopupAlert( {
					hash: $( source ).data( 'hash' ),
					content: $( source ).html(),
					expire: $( source ).data( 'expire' )
				} ) );
			} );
		}
	};

	/**
	 * Tries to find a candidate to be displayed
	 */
	PopupAlertsManager.prototype.maybeDisplay = function () {
		if ( !this.popupSources.length ) {
			return;
		}
		// We're going to pick the topmost popup to prevent any
		// type of race conditions and keep it simple for now
		this.activePopup = this.popupSources[ 0 ];
		this.activePopup.tryDisplay( PopupAlertsManager.cookiePrefix );
	};

	mw.PopupAlertsManager = PopupAlertsManager;

} )( jQuery, mediaWiki ) );
