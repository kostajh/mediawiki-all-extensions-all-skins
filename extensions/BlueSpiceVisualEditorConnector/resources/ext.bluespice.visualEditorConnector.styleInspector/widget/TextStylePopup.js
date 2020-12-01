bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.TextStylePopup = function( config ) {
	config = config || {};
	config.padded = false;
	config.width = 200;

	this.isOpen = false;

	this.absPos = {
		top: config.position.start.y + 10, // For anchor
		left: this.getHorizontalPos( config.position )
	};

	bs.vec.ui.TextStylePopup.super.call( this, config );

	this.surface = config.surface;
	this.fragment = config.fragment;

	this.$element.addClass( 'bs-vec-styleInspector' );
	this.addedTools = {};
	this.initTools();
};

OO.inheritClass( bs.vec.ui.TextStylePopup, OO.ui.PopupWidget );

bs.vec.ui.TextStylePopup.prototype.getHorizontalPos = function( position ) {
	var start, end;
	start = position.start.x;
	end = position.end.x;

	return start + Math.floor( ( end - start ) / 2 ) - 100;
};

bs.vec.ui.TextStylePopup.prototype.toggle = function( show ) {
	bs.vec.ui.TextStylePopup.parent.prototype.toggle.apply( this, [show] );
	this.isOpen = false;
	if ( show ) {
		this.$element.css( {
			position: 'absolute',
			top: this.absPos.top,
			left: this.absPos.left
		} );
		this.$anchor.css( {
			left: '100px'
		} );
		this.$element.removeClass( 'oo-ui-element-hidden' );
		this.isOpen = true;
	}
};

bs.vec.ui.TextStylePopup.prototype.initTools = function() {
	let registry = bs.vec.registry.TextStyleTool.registry;
	for( let name in registry ) {
		if ( registry[name].hasOwnProperty( 'constructor' ) === false ) {
			continue;
		}
		let tool = registry[name].constructor;
		let toolInstance = new tool( {
			fragment: this.fragment,
			surface: this.surface,
			popup: this
		} );
		this.addedTools[name] = toolInstance;
		this.$body.append( toolInstance.$element );
	}
};

bs.vec.ui.TextStylePopup.prototype.getTool = function( name ) {
	if ( this.addedTools.hasOwnProperty( name ) ) {
		return this.addedTools[name];
	}
	return null;
};

bs.vec.ui.TextStylePopup.prototype.isPopupOpen = function() {
	return this.isOpen;
};
