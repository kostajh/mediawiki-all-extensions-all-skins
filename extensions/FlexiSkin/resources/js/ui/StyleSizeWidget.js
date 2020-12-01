flexiskin.ui.StyleSizeWidget = function ( cfg ) {
	cfg = cfg || {};
	flexiskin.ui.StyleSizeWidget.parent.call( this, cfg );

	this.allowedUnits = cfg.allowedUnits || [ 'px', '%', 'em', 'rem' ];
	this.requireUnit = cfg.requireUnit || false;

	this.valueWidget = new OO.ui.NumberInputWidget( {
		min: 0,
		step: 0.001
	} );
	this.valueWidget.$element.css( {
		width: '150px'
	} );
	this.unitWidget = new OO.ui.DropdownInputWidget( {
		options: this.allowedUnits.map( function ( unit ) {
			return { data: unit, label: unit };
		} )
	} );
	this.unitWidget.$element.css( {
		width: '70px'
	} );

	this.$input.remove();
	this.$element.append( new OO.ui.HorizontalLayout( {
		items: [
			this.valueWidget,
			this.unitWidget
		]
	} ).$element );
};

OO.inheritClass( flexiskin.ui.StyleSizeWidget, OO.ui.InputWidget );

flexiskin.ui.StyleSizeWidget.prototype.getValue = function () {
	var value = this.valueWidget.getValue(),
	 unit = this.unitWidget.getValue();

	if ( !value || ( this.requireUnit && !unit ) ) {
		return '';
	}

	return value + unit;
};

flexiskin.ui.StyleSizeWidget.prototype.setValue = function ( value ) {
	if ( $.type( value ) !== 'string' ) {
		return;
	}
	var regex = /^([\d\.]*)(.*)$/gm,
		matches = regex.exec( value );

	if ( matches.length === 3 ) {
		this.valueWidget.setValue( matches[ 1 ] );
		this.unitWidget.setValue( matches[ 2 ] );
	}
};
