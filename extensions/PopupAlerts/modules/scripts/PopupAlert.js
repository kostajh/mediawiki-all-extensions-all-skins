( ( function ( $, mw ) {

	'use strict';

	/**
	 * Popup window class
	 * @class
	 * @constructor
	 *
	 * @param {Object} config Configuration object (hash, content, expire)
	 */
	function PopupAlert( config ) {
		this.hash = config.hash;
		this.content = config.content;
		this.expire = config.expire;
		this.$popup = null;
		this.isVisible = false;
		this.initialize();
	}

	/**
	 * Generates popup body and adds it into the dom
	 */
	PopupAlert.prototype.initialize = function () {
		var template, html, days, daysLeft;
		days = null;
		if ( this.expire ) {
			daysLeft = ( Math.floor( this.expire / 60 / 24 ) );
			if ( daysLeft ) {
				days = mw.msg( 'popupalerts-expiration-info', daysLeft );
			}
		}
		template = mw.template.get( 'ext.popupalerts.alert', 'popup.mustache' );
		html = template.render( {
			hash: this.hash,
			content: this.content,
			expire: this.expire,
			days: days
		} );
		$( html ).find( '.popup-alerts-closer' ).on( 'click', this.onClose.bind( this ) );
		this.$popup = $( html );
		$( 'body' ).append( this.$popup );
	};

	/**
	 * Tries to display the popup
	 * @param {string} prefix
	 */
	PopupAlert.prototype.tryDisplay = function ( prefix ) {
		var expire, cookie = mw.cookie.get( prefix + this.hash );
		if ( cookie === null ) {
			expire = this.expire ? this.expire : 60 * 60 * 24 * 30 * 12 * 100; // 100 years!
			mw.cookie.set( prefix + this.hash, 'true', { expires: expire } );
			this.display();
		}
	};

	/**
	 * Schedules the popup block display
	 */
	PopupAlert.prototype.display = function () {
		setTimeout( this.showPopup.bind( this ), 0 );
	};

	/**
	 * Close marker click event handler
	 * @param {Event} event
	 */
	PopupAlert.prototype.onClose = function ( event ) {
		event.preventDefault();
		this.closePopup();
	};

	/**
	 * Closes the popup block
	 */
	PopupAlert.prototype.closePopup = function () {
		this.$popup.removeClass( 'popup-alerts-wrapper--visible' );
		this.isVisible = false;
	};

	/**
	 * Displays the popup block
	 */
	PopupAlert.prototype.showPopup = function () {
		this.$popup.addClass( 'popup-alerts-wrapper--visible' );
		this.isVisible = true;
	};

	mw.PopupAlert = PopupAlert;

} )( jQuery, mediaWiki ) );
