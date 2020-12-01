/*!
 * ContentTranslation extension
 * A tool that allows editors to translate pages from one language
 * to another with the help of machine translation and other translation
 *
 * @copyright See AUTHORS.txt
 * @license GPL-2.0-or-later
 */
( function () {
	'use strict';
	/**
	 * TranslationView
	 *
	 * @class
	 * @param {Object} [config] Configuration object
	 */
	mw.cx.ui.TranslationView = function ( config ) {
		this.header = new mw.cx.ui.Header( config );
		this.columns = new mw.cx.ui.Columns( config );
		// Configuration initialization
		this.config = $.extend( {}, config, {
			continuous: true,
			expanded: false,
			items: [ this.header, this.columns ],
			classes: [ 'cx-widget' ],
			scrollable: false
		} );
		// Parent constructor
		mw.cx.ui.TranslationView.super.call( this, this.config );
		this.publishButton = null;
		this.publishSettings = null;
		this.preparePublishButton();
	};

	/* Setup */

	OO.inheritClass( mw.cx.ui.TranslationView, OO.ui.StackLayout );

	mw.cx.ui.TranslationView.prototype.preparePublishButton = function () {
		this.setupPublishButton();
		this.attachPublishButton();
	};

	mw.cx.ui.TranslationView.prototype.setupPublishButton = function () {
		this.publishButton = new OO.ui.ButtonWidget( {
			disabled: true,
			flags: [ 'progressive', 'primary' ],
			label: mw.msg( 'cx-publish-button' )
		} );

		this.publishSettings = new mw.cx.ui.PublishSettingsWidget( {
			destination: mw.cx.getDefaultTargetNamespace()
		} );
		this.publishSettings.connect( this, {
			choose: 'onPublishNamespaceChange'
		} );
		this.publishButton.connect( this, {
			click: 'onPublishButtonClick'
		} );

		mw.hook( 'mw.cx.progress' ).add( function ( weights ) {
			this.publishButton.setDisabled( weights.any === 0 );
		}.bind( this ) );
		mw.hook( 'mw.cx.draft.restored' ).add( this.onTranslationRestore.bind( this ) );
		mw.hook( 'mw.cx.translation.title.change' ).add(
			this.onTargetTitleChange.bind( this )
		);
	};

	/**
	 * Translation restore event handler
	 */
	mw.cx.ui.TranslationView.prototype.onTranslationRestore = function () {
		// Restore the namespace choice
		this.onTargetTitleChange( mw.cx.targetTitle );
	};

	mw.cx.ui.TranslationView.prototype.onTargetTitleChange = function ( newTargetTitle ) {
		// Restore the namespace choice
		var currentTitleObj, currentNamespace;
		currentTitleObj = mw.Title.newFromText( newTargetTitle );
		if ( !currentTitleObj ) {
			mw.log.error( '[CX] Invalid target title' );
			return;
		}
		currentNamespace = currentTitleObj.getNamespaceId();
		this.publishSettings.setDestinationNamespace( currentNamespace );
	};

	/**
	 * Target namespace change handler
	 *
	 * @param {number} namespaceId
	 */
	mw.cx.ui.TranslationView.prototype.onPublishNamespaceChange = function ( namespaceId ) {
		var currentTitleObj, title, newTitle, currentNamespace, username;

		currentTitleObj = mw.Title.newFromText( mw.cx.targetTitle );
		if ( !currentTitleObj ) {
			mw.log.error( '[CX] Invalid target title' );
			return;
		}
		currentNamespace = currentTitleObj.getNamespaceId();
		if ( namespaceId === currentNamespace ) {
			// No change.
			return;
		}

		// Get the current title string
		title = currentTitleObj.getMainText();
		if ( currentNamespace === mw.config.get( 'wgNamespaceIds' ).user ) {
			// User namespace. Get the title part alone after removing User:username/ part
			title = title.substr( title.indexOf( '/' ) + 1 );
		}

		if ( namespaceId === mw.config.get( 'wgNamespaceIds' ).user ) {
			username = mw.user.getName();
			title = mw.Title.newFromText( username + '/' + title, namespaceId ).toText();
		}
		newTitle = mw.Title.newFromText( title, namespaceId ).toText();

		mw.cx.targetTitle = newTitle;
		this.columns.translationColumn.setTargetTitle( newTitle );
		mw.log( '[CX] Target title changed to ' + mw.cx.targetTitle );
		// Namespace changed. Enable the publish button
		this.publishButton.setDisabled( false );
	};

	/**
	 * Add the publish button to the user interface.
	 */
	mw.cx.ui.TranslationView.prototype.attachPublishButton = function () {
		this.header.$headerBar.append( new OO.ui.HorizontalLayout( {
			classes: [ 'cx-header__publish' ],
			items: [ this.publishSettings, this.publishButton ]
		} ).$element );
	};

	mw.cx.ui.TranslationView.prototype.onPublishButtonClick = function () {
		this.publish();
	};

	mw.cx.ui.TranslationView.prototype.publish = function () {
		var publisher, self = this;

		// Disable the trigger button
		this.publishButton.setDisabled( true ).setLabel( mw.msg( 'cx-publish-button-publishing' ) );
		publisher = new mw.cx.Publish( this.publishButton, this.config.siteMapper );
		publisher.publish( {
			title: mw.cx.targetTitle
		} ).always( function () {
			self.publishButton.setDisabled( true ).setLabel( mw.msg( 'cx-publish-button' ) );
		} );
	};

}() );
