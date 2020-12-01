bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.InternalFileLinkContextItem = function() {
	// Parent constructor
	bs.vec.ui.InternalFileLinkContextItem .super.apply( this, arguments );

	// Initialization
	this.$element.addClass( 've-ui-internalFileLinkContextItem' );
};

/* Inheritance */
OO.inheritClass( bs.vec.ui.InternalFileLinkContextItem, ve.ui.MWInternalLinkContextItem );

/* Static Properties */

bs.vec.ui.InternalFileLinkContextItem.static.name = 'link/internalFile';

bs.vec.ui.InternalFileLinkContextItem.static.modelClasses = [ bs.vec.dm.InternalFileLinkAnnotation, bs.vec.dm.InternalMediaLinkAnnotation ];

bs.vec.ui.InternalFileLinkContextItem.static.commandName = 'media';

bs.vec.ui.InternalFileLinkContextItem.prototype.onEditButtonClick = function () {
	var command = this.getCommand();

	if ( command ) {
		command.execute( this.context.getSurface() );
		var windowManager = this.context.getSurface().getDialogs();
		windowManager.connect( this, {
			opening: 'switchToInfoPanel'
		} );
	}
};


bs.vec.ui.InternalFileLinkContextItem.prototype.switchToInfoPanel = function ( win, opened, data ) {
	var windowManager = this.context.getSurface().getDialogs(),
		annotation = this.getOneAnnotation();

	if ( win.constructor.name !== 'BsVecUiMWMediaDialog' ) {
		return;
	}
	opened.done( function() {
		win.setFileLinkEditMode( annotation );
		this.emit( 'command' );
	}.bind( this ) );

	windowManager.disconnect( this, {
		opening: 'switchToInfoPanel'
	} );
};

bs.vec.ui.InternalFileLinkContextItem.prototype.getOneAnnotation = function() {
	var annotations = this.context.getSurface().getModel().getFragment().getAnnotations(),
		annotationStoreHash;

	annotationStoreHash = annotations.getAnnotationsByName( 'link/internalFile' );
	if ( annotationStoreHash.storeHashes.length > 0 ) {
		return annotationStoreHash.get()[0];
	}
	annotationStoreHash = annotations.getAnnotationsByName( 'link/internalMedia' );
	if ( annotationStoreHash.storeHashes.length > 0 ) {
		return annotationStoreHash.get()[0];
	}
	return null;
};

/* Registration */
ve.ui.contextItemFactory.register( bs.vec.ui.InternalFileLinkContextItem );
