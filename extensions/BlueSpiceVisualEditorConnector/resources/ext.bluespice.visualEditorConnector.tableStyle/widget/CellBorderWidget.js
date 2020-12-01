bs.util.registerNamespace( 'bs.vec.ui.widget' );

bs.vec.ui.widget.CellBorderWidget = function( contextItem ) {
	this.styles = contextItem.getStyles();

	bs.vec.ui.widget.CellBorderWidget.parent.call( this, contextItem );
	bs.vec.mixin.TableBorderStylePopup.call( this );
	this.connect( this, {
		change: function( value ) {
			this.value = value;
			this.executeAction();
		}
	} );

	this.makeBorderPicker();
	this.$element.append( this.popup.$element );
	this.$element.append( this.borderPickerTrigger.$element );
	this.$element.addClass( 'bs-vec-border-widget' );
};

OO.inheritClass( bs.vec.ui.widget.CellBorderWidget, bs.vec.ui.widget.CommandWidget );
OO.mixinClass( bs.vec.ui.widget.CellBorderWidget, bs.vec.mixin.TableBorderStylePopup );

bs.vec.ui.widget.CellBorderWidget.prototype.makeBorderPicker = function() {
	this.borderPickerTrigger = new OO.ui.ButtonWidget( {
		icon: 'cellBorderAll',
		title: OO.ui.deferMsg( "bs-vec-table-border-borders" ),
		framed: false
	} );
	this.borderPickerTrigger.connect( this, {
		click: 'togglePopup'
	} );
};

bs.vec.ui.widget.CellBorderWidget.prototype.executeAction = function() {
	if ( !this.shouldExecute() ) {
		return;
	}
	// Get value and set
	this.contextItem.execCommand( 'cellBorder', { value: this.value } );
};

bs.vec.ui.widget.CellBorderWidget.prototype.togglePopup = function() {
	this.popup.toggle();
	var $mainPopup = this.$element.parents( '.oo-ui-popupWidget-popup' );
	if ( this.popup.isVisible() ) {
		$mainPopup.css( 'overflow', 'visible' );
	} else {
		$mainPopup.css( 'overflow', 'hidden' );
	}
};
