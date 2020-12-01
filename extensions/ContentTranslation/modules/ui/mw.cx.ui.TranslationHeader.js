'use strict';

/**
 * TranslationView Header
 *
 * @class
 * @extends OO.ui.PanelLayout
 * @param {Object} [config] Configuration object
 */
mw.cx.ui.TranslationHeader = function ( config ) {
	// Configuration initialization
	this.config = config || {};
	this.statusbar = null;
	this.$toolbar = null;

	// Parent constructor
	// eslint-disable-next-line mediawiki/class-doc
	mw.cx.ui.TranslationHeader.super.call( this, $.extend( {}, this.config, {
		continuous: true,
		expanded: false,
		$content: this.getContent(),
		classes: [ 'cx-translation-view-header' ].concat( this.config.classes )
	} ) );

	this.listen();
};

/* Setup */

OO.inheritClass( mw.cx.ui.TranslationHeader, OO.ui.PanelLayout );

mw.cx.ui.TranslationHeader.prototype.getContent = function () {
	var translationCenter = new OO.ui.ButtonWidget( {
		framed: false,
		icon: 'previous',
		classes: [ 'cx-header__translation-center' ],
		label: mw.msg( 'cx-header-all-translations' ),
		href: mw.util.getUrl( 'Special:ContentTranslation' )
	} );

	this.statusbar = new OO.ui.LabelWidget( {
		classes: [ 'cx-header-draft-status' ],
		title: mw.msg( 'cx-save-draft-tooltip' )
	} );

	this.$toolbar = $( '<div>' ).addClass( 'cx-header-tools-container oo-ui-toolbar-bar' );

	return [ translationCenter.$element, this.statusbar.$element, this.$toolbar ];
};

mw.cx.ui.TranslationHeader.prototype.listen = function () {
	mw.hook( 'mw.cx.draft.restored' ).add(
		this.setStatusMessage.bind( this, mw.msg( 'cx-draft-restored' ) )
	);

	if ( !mw.cx.supportsSticky() ) {
		$( window ).on( 'scroll resize', OO.ui.throttle( this.onWindowScroll.bind( this ), 100 ) );
	}
};

mw.cx.ui.TranslationHeader.prototype.setStatusMessage = function ( message ) {
	this.statusbar.setLabel( message );
};

mw.cx.ui.TranslationHeader.prototype.onWindowScroll = function () {
	var scrollTop = window.pageYOffset,
		$parent = this.$element.parent(),
		top = $parent.position().top;

	if ( scrollTop > top ) {
		this.$element.addClass( 'sticky' );
		// Adjust parent size with padding (in place for floated translation header) to avoid jumpiness
		$parent.css( 'padding-top', top );
	} else {
		this.$element.removeClass( 'sticky' );
		$parent.css( 'padding-top', 0 );
	}
};
