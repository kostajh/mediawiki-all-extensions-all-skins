bs.vec.registerComponentPlugin(
	bs.vec.components.LINK_ANNOTATION_INSPECTOR,
	function( component ) {
		component.linkTypeIndex.addTabPanels( [
			new OO.ui.TabPanelLayout( 'file', {
				label: ve.msg( 'bs-insertlink-tab-file' ),
				expanded: false,
				scrollable: false,
				padded: true
			} )
		] );

		component.localFileSystemAnnotationInput =
			new bs.insertlink.ui.MWFileLinkAnnotationWidget();

		component.linkTypeIndex.getTabPanel( 'file' ).$element.append(
			component.localFileSystemAnnotationInput.$element
		);
		component.localFileSystemAnnotationInput.connect( this, { change: function() {
			this.updateActions();
		}.bind( component ) } );

		component.localFileSystemAnnotationInput.getInternalFilePicker().results.connect( component, {
			add: function () {
				this.updateSize();
			}
		} );
	}
);

// FIXME: Not exactly cool, would be nice to have a better way of overriding, so that plugins
// would not only be called on initialize, but on every function from prototype
ve.ui.MWLinkAnnotationInspector.prototype.onLinkTypeIndexSet = function () {
	var text = this.annotationInput.getTextInputWidget().getValue(),
		end = text.length,
		isExternal = this.isExternal() || this.linkTypeIndex.getCurrentTabPanelName() === 'file',
		inputHasProtocol = ve.init.platform.getExternalLinkUrlProtocolsRegExp().test( text );

	if ( this.linkTypeIndex.getCurrentTabPanelName() === 'file' ) {
		this.annotationInput = this.localFileSystemAnnotationInput;
	} else {
		this.annotationInput = isExternal ? this.externalAnnotationInput : this.internalAnnotationInput;
	}

	this.updateSize();

	// If the user manually switches to internal links with an external link in the input, remember this
	if ( !isExternal && inputHasProtocol ) {
		this.allowProtocolInInternal = true;
	}

	this.annotationInput.getTextInputWidget().setValue( text ).focus();
	if ( this.linkTypeIndex.getCurrentTabPanelName() === 'file' ) {
		this.annotationInput.internalFilePicker.getQuery().setValue( text ).focus();
	}
	// Select entire link when switching, for ease of replacing entire contents.
	// Most common case:
	// 1. Inspector opened, internal-link shown with the selected-word prefilled
	// 2. User clicks external link tab (unnecessary, because we'd auto-switch, but the user doesn't know that)
	// 3. User pastes a link, intending to replace the existing prefilled link
	this.annotationInput.getTextInputWidget().$input[ 0 ].setSelectionRange( 0, end );
	// Focusing a TextInputWidget normally unsets validity. However, because
	// we're kind of pretending this is the same input, just in a different
	// mode, it doesn't make sense to the user that the focus behavior occurs.
	this.annotationInput.getTextInputWidget().setValidityFlag();

	this.updateActions();
};

var createAnnotationInput = ve.ui.MWLinkAnnotationInspector.prototype.createAnnotationInput;
ve.ui.MWLinkAnnotationInspector.prototype.createAnnotationInput = function () {
	if ( this.linkTypeIndex.getCurrentTabPanelName() === 'file' ) {
		this.annotationInput = this.localFileSystemAnnotationInput;
	}
	return createAnnotationInput.call( this );
};

ve.ui.MWLinkAnnotationInspector.prototype.getSetupProcess = function ( data ) {
	return ve.ui.MWLinkAnnotationInspector.super.prototype.getSetupProcess.call( this, data )
		.next( function () {
			var panel = 'internal';
			if ( this.initialAnnotation instanceof ve.dm.MWExternalLinkAnnotation ) {
				panel = 'external';
			}
			if ( this.initialAnnotation instanceof bs.insertlink.dm.ExternalFileLinkAnnotation ) {
				panel = 'file';
			}

			this.linkTypeIndex.setTabPanel( panel );
			this.annotationInput.setAnnotation( this.initialAnnotation );
		}, this );
};

var updateAction = ve.ui.LinkAnnotationInspector.prototype.updateActions;
ve.ui.LinkAnnotationInspector.prototype.updateActions = function () {
	var isValid = false,
		inspector = this,
		annotation = this.annotationInput.getAnnotation();

	if ( !( annotation instanceof bs.vec.dm.InternalFileLinkAnnotation ) ) {
		return updateAction.call( this );
	}

	this.annotationInput.getInternalFilePicker().query.getValidity()
		.then( function () { isValid = true; } )
		.always( function () {
			isValid = isValid && !!annotation;
			inspector.actions.forEach( { actions: [ 'done', 'insert' ] }, function ( action ) {
				action.setDisabled( !isValid );
			} );
		} );
};
