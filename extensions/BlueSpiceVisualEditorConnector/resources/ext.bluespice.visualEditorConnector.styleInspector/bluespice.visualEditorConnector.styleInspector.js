var instance = new StyleInspector();

function StyleInspector() {
	this.target = null;
}

OO.initClass( StyleInspector );

StyleInspector.prototype.setTarget = function( target ) {
	this.target = target;
	this.init();
};

StyleInspector.prototype.init = function() {
	this.surface = this.target.getSurface();
	this.surfaceModel = this.surface.getModel();
	this.popup = null;
	this.selection = null;
	this.selectedNode = null;
	this.earlySelection = null;
	this.position = {};

	this.enabled = true;

	this.surfaceModel.on( 'select', this.onSelection.bind( this )  );
	this.target.getToolbar().on( 'updateState', this.onToolbarUpdateState.bind( this ) );
	$( document ).on( 'mousedown', this.onMouseDown.bind( this ) );
	$( document ).on( 'mouseup', this.onMouseUp.bind( this ) );
	$( window ).on( 'resize', this.onResize.bind( this ) );
};

StyleInspector.prototype.setEnabled = function( val ) {
	this.enabled = !!val;
};

StyleInspector.prototype.onSelection = function( s ) {
	if ( !this.enabled ) {
		return;
	}
	if ( !( s instanceof ve.dm.LinearSelection ) ) {
		return;
	}
	this.selection = s;
	this.selectedNode = this.surfaceModel.getSelectedNode();
};

StyleInspector.prototype.onToolbarUpdateState = function() {
	if ( !this.enabled ) {
		return;
	}
	this.removePopup();
};

StyleInspector.prototype.onMouseDown = function ( e ) {
	if ( !this.enabled ) {
		return;
	}
	this.earlySelection = null;
	if ( e.which !== 1 ) {
		return;
	}

	if ( this.shouldRemovePopup( e ) ) {
		this.removePopup();
	}

	this.earlySelection = this.selection;
	this.position = {
		start: {
			x: e.pageX,
			y: e.pageY
		}
	};
};

StyleInspector.prototype.onMouseUp = function ( e ) {
	if ( !this.enabled ) {
		return;
	}
	var range;
	if ( e.which !== 1 ) {
		return;
	}
	var $target = $( e.target );
	if ( !$target.hasClass( 've-ce-branchNode' ) && !$target.hasClass( 've-ce-textStyleAnnotation' ) ) {
		return;
	}
	this.selection = ve.init.target.getSurface().getModel().getSelection();
	if( !this.selection || !this.selection.hasOwnProperty( 'range' ) ) {
		return;
	}
	if ( this.selectedNode !== null && this.selectedNode.type !== 'text' ) {
		// We only show this inspector when plain text is selected,
		// as soon as there is a node selected, its not plain text
		return;
	}
	this.position.end = {
		x: e.pageX,
		y: e.pageY
	};
	range = this.selection.getRange();
	if ( range.start === range.end ) {
		// Empty selection - but maybe we could still show the inspector
		return;
	}

	this.inspect( range );
};

StyleInspector.prototype.onResize = function() {
	if ( !this.enabled ) {
		return;
	}

	// Traditionally, popup must be attached to the element it appears next to.
	// Since, with selection, we don't have that element, we attach to the body
	// and position it to appear underneath the selection. This position becomes
	// obsolete once the window is resized, so hide the popup and make use select again
	if ( this.popup ) {
		this.removePopup();
	}
};

StyleInspector.prototype.shouldRemovePopup = function ( e ) {
	if ( !this.popup ) {
		return false;
	}
	var $target = $( e.target );
	if ( $target.hasClass( 'bs-vec-styleInspector' ) ) {
		return false;
	}
	if ( $target.parents( '.bs-vec-styleInspector' ).length !== 0 ) {
		return false;
	}

	return true;
};

StyleInspector.prototype.removePopup = function() {
	if ( !this.popup ) {
		return;
	}

	this.popup.$element.remove();
	this.popup = null;
};

StyleInspector.prototype.inspect = function( range ) {
	if ( this.popup ) {
		this.removePopup();
	}

	this.popup = new bs.vec.ui.TextStylePopup( {
		position: this.position,
		surface: this.surface,
		fragment: this.surfaceModel.getLinearFragment( range, true )
	} );

	this.popup.$element.on( 'mousedown', function( e ) {
		e.preventDefault();
		e.stopPropagation();
	} );
	this.popup.$element.on( 'mouseup', function( e ) {
		e.preventDefault();
		e.stopPropagation();
	} );

	$( document.body ).append( this.popup.$element );
	this.popup.toggle( true );
	window.vecBSTextStylePopup = function() {
		return this.popup;
	}.bind( this );
};

mw.hook( 've.activationComplete' ).add( function () {
	var target = ve.init.target;

	if ( target.getSurface().getMode() === 'visual' ) {
		this.setTarget( target );
		this.setEnabled( true );
	} else {
		this.setEnabled( false );
	}
}.bind( instance ) );
