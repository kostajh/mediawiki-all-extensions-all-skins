flexiskin.ui.widget.FontPicker = function ( cfg ) {
	cfg = cfg || {};

	cfg.options = this.makeOptionsFromFlat( [
		"Arial",
		"Times New Roman",
		"Courier",
		"Verdana",
		"Georgia",
		"Palatino",
		"Tahoma",
		"Trebuchet MS"
	] );
	flexiskin.ui.widget.FontPicker.parent.call( this, cfg );

	this.applyFonts();

	this.$element.addClass( 'flexiskin-widget-font-picker' );
};

OO.inheritClass( flexiskin.ui.widget.FontPicker, OO.ui.DropdownInputWidget );

flexiskin.ui.widget.FontPicker.prototype.setValue = function( value ) {
	if ( value ) {
		// Un-quote value
		value = value.replace( /^"(.+)"$/,'$1' );
	}
	flexiskin.ui.widget.FontPicker.parent.prototype.setValue.call( this, value );
};

flexiskin.ui.widget.FontPicker.prototype.getValue = function() {
	var value = flexiskin.ui.widget.FontPicker.parent.prototype.getValue.call( this );
	if ( /\s/.test( value ) ) {
		// Quote values with spaces
		return '\"' + value + '\"';
	}
};

flexiskin.ui.widget.FontPicker.prototype.makeOptionsFromFlat = function( flat ) {
	var options = [], i = 0;
	for ( i; i < flat.length; i++ ) {
		options.push( {
			data: flat[i],
			label: flat[i]
		} );
	}

	return options;
};

flexiskin.ui.widget.FontPicker.prototype.applyFonts = function() {
	// Make options text actually be in the font it refers to
	var items = this.dropdownWidget.getMenu().items;
	for ( var i = 0; i < items.length; i++ ) {
		var item = items[i], data = item.data;
		if ( /\s/.test( data ) ) {
			// Quote values with spaces
			data = '\"' + data + '\"';
		}
		item.$element.css( 'font-family', item.data );
	}
};
