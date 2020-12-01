flexiskin.ui.ColorPalleteWidget = function ( cfg ) {
	flexiskin.ui.ColorPalleteWidget.parent.call( this, cfg );

	this.colors = cfg.colors;
	this.$colorContainer = $( '<div>' ).addClass( 'color-container' );
	this.updateColors( this.colors );

	this.$element.append( this.$colorContainer );
};

OO.inheritClass( flexiskin.ui.ColorPalleteWidget, OO.ui.Widget );

flexiskin.ui.ColorPalleteWidget.prototype.updateColors = function ( colors ) {
	this.$colorContainer.children().remove();

	for ( var key in colors ) {
		if ( !colors.hasOwnProperty( key ) ) {
			continue;
		}
		if ( !colors[ key ] ) {
			continue;
		}

		this.$colorContainer.append(
			$( '<div>' )
				.addClass( 'color-item' )
				.css( 'background-color', colors[ key ] )
		);
	}
};

flexiskin.ui.ColorPalleteWidget.prototype.getColors = function () {
	return this.colors;
};
