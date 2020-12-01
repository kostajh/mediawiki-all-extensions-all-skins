bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.ButtonStyleTool = function( config ) {
	config = config || {};

	this.fragment = config.fragment;
	this.surface = config.surface;
	this.surfaceModel = config.surface.model;
	this.popup = config.popup;

	this.annotationAction = config.annotationAction || null;

	config.icon = this.getIcon();
	config.title = this.getLabel();
	config.framed = false;

	bs.vec.ui.ButtonStyleTool.parent.call( this, config );

	this.changeActive();

	this.connect( this, {
		click: 'annotate'
	} );
};

OO.inheritClass( bs.vec.ui.ButtonStyleTool, OO.ui.ButtonWidget );

bs.vec.ui.ButtonStyleTool.prototype.annotate = function() {
	var method, annotationData;
	if ( !this.annotationAction ) {
		this.annotationAction = new ve.ui.AnnotationAction( this.surface );
	}
	method = this.getMethod();
	if ( ve.ui.AnnotationAction.static.methods.indexOf( method ) === -1 ) {
		return;
	}
	annotationData = {
		attributes: this.getData(),
		type: this.getName()
	};

	if ( this.annotationAction[method]( this.getName(), annotationData ) ) {
		this.changeActive();
	}
};

bs.vec.ui.ButtonStyleTool.prototype.clearAnnotation = function() {
	if ( !this.annotationAction ) {
		this.annotationAction = new ve.ui.AnnotationAction( this.surface );
	}
	this.annotationAction.clear( this.getName() );
};

bs.vec.ui.ButtonStyleTool.prototype.getName = function() {
	// STUB
};

bs.vec.ui.ButtonStyleTool.prototype.getData = function() {
	// undefined
	return;
};

bs.vec.ui.ButtonStyleTool.prototype.getMethod = function() {
	return 'toggle';
};

bs.vec.ui.ButtonStyleTool.prototype.getIcon = function() {
	// STUB
};

bs.vec.ui.ButtonStyleTool.prototype.getLabel = function() {
	// STUB
};

bs.vec.ui.ButtonStyleTool.prototype.changeActive = function() {
	if ( this.isActive() ) {
		this.setFlags( [ 'primary', 'progressive' ] );
	} else {
		this.clearFlags();
	}
};

bs.vec.ui.ButtonStyleTool.prototype.isActive = function() {
	var annotations = this.fragment.getAnnotations().getAnnotationsByName( this.getName() );
	if ( annotations.getHashes().length > 0 ) {
		return true;
	}
	return false;
};
