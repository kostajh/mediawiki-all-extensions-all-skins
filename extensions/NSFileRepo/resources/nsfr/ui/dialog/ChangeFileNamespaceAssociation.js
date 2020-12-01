window.nsfr = window.nsfr || {};
window.nsfr.ui = window.nsfr.ui || {};
window.nsfr.ui.dialog = window.nsfr.ui.dialog || {};

( function ( mw, $ ) {
	nsfr.ui.dialog.ChangeFileNamespaceAssociation = function NsfrUiDialogChangeFileNamespaceAssociation( config ) {
		nsfr.ui.dialog.ChangeFileNamespaceAssociation.parent.call( this, config );

		this.unprefixedFileName = config.unprefixedFileName;
		this.currentNS = config.currentNS;
		this.excludeNS = config.excludeNS;
		this.formattedNamespaces = config.formattedNamespaces;
		this.currentPage = config.currentPage;
	};
	OO.inheritClass( nsfr.ui.dialog.ChangeFileNamespaceAssociation, OO.ui.ProcessDialog );

	var theStatic = nsfr.ui.dialog.ChangeFileNamespaceAssociation.static;
	theStatic.name = 'nsrf-changefilenamespaceassoc-dialog';
	theStatic.title = mw.message( 'nsfilerepo-move-file-namespace-dialog-title' ).plain();
	theStatic.actions = [
		{
			action: 'save',
			label: mw.message( 'nsfilerepo-move-file-namespace-dialog-button-done' ).plain(),
			flags: 'primary'
		},
		{
			label: mw.message( 'nsfilerepo-move-file-namespace-dialog-button-cancel' ).plain(),
			flags: 'safe'
		}
	];

	var thePrototype = nsfr.ui.dialog.ChangeFileNamespaceAssociation.prototype;
	thePrototype.initialize = function () {
		nsfr.ui.dialog.ChangeFileNamespaceAssociation.parent.prototype.initialize.apply( this, arguments );
		this.content = new OO.ui.PanelLayout( { padded: true, expanded: false } );

		this.targetNamespaceSelector = new mw.widgets.NamespaceInputWidget( {
			label: mw.message( 'nsfilerepo-move-file-namespace-dialog-namespaceselector-label' ).plain(),
			value: this.currentNS,
			exclude: this.excludeNS,
			dropdown: {
				$overlay: true
			}
		} );
		this.targetNamespaceSelector.dropdownWidget.getMenu().connect( this, {
			toggle: 'onTargetNamespaceSelectorToggle'
		} );

		this.form = new OO.ui.FormLayout( {
			items: [ this.targetNamespaceSelector ]
		} );

		this.content.$element.append( this.form.$element );
		this.$body.append( this.content.$element );
	};

	thePrototype.getActionProcess = function ( action ) {
		var parentProcess = nsfr.ui.dialog.ChangeFileNamespaceAssociation.parent.prototype.getActionProcess.call( this, action ),
			dialog = this,
			selectedNamespace = parseInt( this.targetNamespaceSelector.getValue() ),
			namespaceAssocHasBeenChanged = this.currentNS !== selectedNamespace;

		if ( action === 'save' && namespaceAssocHasBeenChanged ) {
			var newFilePageName = this.makeNewFilePageName( selectedNamespace );
			dfd = new $.Deferred();
			mw.loader.using( 'mediawiki.api' ).done( function () {
				var mwApi = new mw.Api();
				mwApi.postWithEditToken( {
					action: 'move',
					from: dialog.currentPage,
					to: newFilePageName,
					movetalk: true,
					ignorewarnings: true
				} ).done( function () {
					dfd.resolve.apply( dialog, arguments );
					dialog.emit( 'move-filepage-complete', dialog.currentPage, newFilePageName );
				} ).fail( function () {
					dfd.reject.apply( dialog, arguments );
				} );
			} ).fail( function () {
				dfd.reject.apply( dialog, arguments );
			} );

			parentProcess.first( dfd.promise() );
		}

		if ( action ) {
			parentProcess.next( function () {
				dialog.close( { action: action } );
			} );
		}

		return parentProcess;
	};

	thePrototype.show = function () {
		if ( !this.windowManager ) {
			this.windowManager = new OO.ui.WindowManager();
			$( document.body ).append( this.windowManager.$element );
			this.windowManager.addWindows( [ this ] );
		}
		this.windowManager.openWindow( this );
	};

	/**
	 * Hack to make the drop-down-menu show up in-front of the dialog
	 * which has high z-index
	 *
	 * @param {boolean} visible
	 * @return undefined
	 */
	thePrototype.onTargetNamespaceSelectorToggle = function ( visible ) {
		var css = {
			'z-index': 4 // Default
		};
		if ( visible ) {
			css[ 'z-index' ] = 101;
		}
		$( '.oo-ui-defaultOverlay' ).children( '.oo-ui-menuSelectWidget' ).css( css );
	};

	thePrototype.makeNewFilePageName = function ( selectedNamespace ) {
		var prefix = this.formattedNamespaces[ 6 ] + ':'; // NS_FILE
		if ( selectedNamespace !== 0 ) { // NS_MAIN
			prefix += this.formattedNamespaces[ selectedNamespace ] + ':';
		}

		return prefix + this.unprefixedFileName;
	};

}( mediaWiki, jQuery ) );
