bs.util.registerNamespace( 'bs.insertlink.ui' );

bs.insertlink.ui.MWFileLinkAnnotationWidget = function BsInsertlinkUiMWFileLinkAnnotationWidget() {
	bs.insertlink.ui.MWFileLinkAnnotationWidget.super.apply( this, arguments );
	this.addOtherTools();
	this.isExternal = false;
	this.input.$element.hide();
};

OO.inheritClass( bs.insertlink.ui.MWFileLinkAnnotationWidget, ve.ui.MWExternalLinkAnnotationWidget );

bs.insertlink.ui.MWFileLinkAnnotationWidget.static.createExternalLinkInputWidget = function ( config ) {
	return new bs.insertlink.widget.LocalFilePathInputWidget( config );
};

bs.insertlink.ui.MWFileLinkAnnotationWidget.prototype.onTextChange = function ( value ) {
	if ( this.isExternal ) {
		return bs.insertlink.ui.MWFileLinkAnnotationWidget.parent.prototype.onTextChange.call( this, value );
	}

	this.internalFilePicker.query.getValidity()
		.done( function () {
			this.setAnnotation( this.constructor.static.getAnnotationFromText( value, true ), true );
		}.bind( this ) )
		.fail( function () {
			this.setAnnotation( null, true );
		}.bind( this ) );
};


bs.insertlink.ui.MWFileLinkAnnotationWidget.prototype.addOtherTools = function () {
	this.internalFilePicker = new mw.widgets.TitleSearchWidget( {
		icon: 'search',
		showRedlink: true,
		namespace: bs.ns.NS_FILE,
		excludeCurrentPage: true,
		showImages: mw.config.get( 'wgVisualEditor' ).usePageImages,
		showDescriptions: mw.config.get( 'wgVisualEditor' ).usePageDescriptions,
		cache: ve.init.platform.linkCache
	} );
	this.internalFilePicker.$element.prepend( this.internalFilePicker.$query );
	this.internalFilePicker.query.connect( this, {
		change: 'onTextChange'
	} );

	this.typeSwitch = new OO.ui.ToggleSwitchWidget();
	this.typeSwitch.connect( this, {
		change: 'onTypeToggle'
	} );
	this.typeSwitchLayout = new OO.ui.FieldLayout( this.typeSwitch, {
		align: 'left',
		label: mw.message( "bs-insertlink-external-file-switch-label" ).text()
	} );

	this.$element.append( new OO.ui.FieldsetLayout( {
		items: [
			this.internalFilePicker,
			this.input,
			this.typeSwitchLayout
		]
	} ).$element );
};

bs.insertlink.ui.MWFileLinkAnnotationWidget.prototype.getInternalFilePicker = function () {
	return this.internalFilePicker;
};

bs.insertlink.ui.MWFileLinkAnnotationWidget.prototype.onTypeToggle = function ( value ) {
	this.isExternal = !!value;

	if ( value ){
		this.internalFilePicker.query.setValue( null );
		this.internalFilePicker.$element.hide();
		this.input.$element.show();
	} else {
		this.internalFilePicker.$element.show();
		this.input.$element.hide();
	}
};

bs.insertlink.ui.MWFileLinkAnnotationWidget.static.getAnnotationFromText = function ( value, internal ) {
	internal = internal || false;
	value = value.trim();

	// Keep annotation in sync with value
	if ( value === '' ) {
		return null;
	} else if( internal ) {
		var title = mw.Title.makeTitle( bs.ns.NS_FILE, value );
		return bs.vec.dm.InternalFileLinkAnnotation.static.newFromTitle( title );
	} else {
		return new bs.insertlink.dm.ExternalFileLinkAnnotation( {
			type: 'link/fileExternal',
			attributes: {
				href: value
			}
		} );
	}
};

bs.insertlink.ui.MWFileLinkAnnotationWidget.prototype.setAnnotation = function ( annotation, fromText ) {
	bs.insertlink.ui.MWFileLinkAnnotationWidget.parent.prototype.setAnnotation.call( this, annotation, fromText );
	if ( !this.annotation )  {
		return;
	}
	this.isExternal = annotation instanceof bs.insertlink.dm.ExternalFileLinkAnnotation;

	if ( this.annotation instanceof bs.insertlink.dm.ExternalFileLinkAnnotation ) {
		this.typeSwitch.setValue( true );
	}
	if ( !fromText ) {
		this.internalFilePicker.query.setValue( this.constructor.static.getTextFromAnnotation( this.annotation ) );
	}

	return this;
};

bs.insertlink.ui.MWFileLinkAnnotationWidget.static.getTextFromAnnotation = function ( annotation ) {
	if ( !annotation ) {
		return '';
	}
	var type = annotation.name;
	if ( type === 'link/internalFile' ) {
		return annotation.element.attributes.normalizedTitle;
	} else {
		return annotation.element.attributes.href;
	}
};
