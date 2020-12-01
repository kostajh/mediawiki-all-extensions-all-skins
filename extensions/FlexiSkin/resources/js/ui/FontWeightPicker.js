flexiskin.ui.widget.FontWeightPicker = function ( cfg ) {
	cfg = cfg || {};
	cfg.options = this.makeOptions( {
		'300': mw.message( 'flexiskin-font-weight-light' ).text(),
		'400': mw.message( 'flexiskin-font-weight-regular' ).text(),
		'500': mw.message( 'flexiskin-font-weight-medium' ).text(),
		'700': mw.message( 'flexiskin-font-weight-bold' ).text()
	} );
	flexiskin.ui.widget.FontWeightPicker.parent.call( this, cfg );

	this.$element.addClass( 'flexiskin-widget-font-weight-picker' );
};

OO.inheritClass( flexiskin.ui.widget.FontWeightPicker, OO.ui.DropdownInputWidget );

flexiskin.ui.widget.FontWeightPicker.prototype.makeOptions = function( value ) {
	var options = [], key;
	for ( key in value ) {
		if ( !value.hasOwnProperty( key ) ) {
			continue;
		}
		options.push( {
			data: key,
			label: value[key]
		} );
	}

	return options;
};
