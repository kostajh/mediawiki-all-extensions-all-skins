flexiskin.ui.widget.BorderWidget = function ( cfg ) {
	flexiskin.ui.widget.BorderWidget.parent.call( this, cfg );

	this.currentColorWidget = cfg.currentColor || false;

	this.widthWidget = new OO.ui.DropdownInputWidget( {
		options: [
			{ data: '1px' },
			{ data: '2px' },
			{ data: '3px' },
			{ data: '4px' },
			{ data: '5px' }
		]
	} );
	this.typeWidget = new OO.ui.DropdownInputWidget( {
		options: [
			{ data: 'solid', label: mw.message( 'flexiskin-ui-widget-border-solid' ).text() },
			{ data: 'dashed', label: mw.message( 'flexiskin-ui-widget-border-dashed' ).text() },
			{ data: 'dotted', label: mw.message( 'flexiskin-ui-widget-border-dotted' ).text() },
			{ data: 'double', label: mw.message( 'flexiskin-ui-widget-border-double' ).text() }
		]
	} );

	this.widthWidget.$element.insertBefore( this.$colorWidgetWrap );
	this.typeWidget.$element.insertBefore( this.$colorWidgetWrap );

	this.widthWidget.$element.hide();
	this.typeWidget.$element.hide();
};

OO.inheritClass( flexiskin.ui.widget.BorderWidget, flexiskin.ui.widget.ColorOrNone );

flexiskin.ui.widget.BorderWidget.prototype.onNoneChange = function ( value ) {
	flexiskin.ui.widget.BorderWidget.parent.prototype.onNoneChange.call( this, value );
	if ( value ) {
		this.widthWidget.$element.show();
		this.typeWidget.$element.show();
	} else {
		this.widthWidget.$element.hide();
		this.typeWidget.$element.hide();
	}
};

flexiskin.ui.widget.BorderWidget.prototype.getValue = function () {
	if ( !this.noneCheck.isSelected() ) {
		return 'none';
	}

	var color = this.colorWidget.getValue();
	if ( !color ) {
		color = 'currentColor';
	}

	return [
		this.widthWidget.getValue(),
		this.typeWidget.getValue(),
		color
	].join( ' ' );
};

flexiskin.ui.widget.BorderWidget.prototype.setValue = function ( value ) {
	if ( !value || value === 'none' ) {
		return;
	}

	var bits = value.split( ' ' );
	if ( bits.length !== 3 ) {
		return;
	}
	var color = bits.pop(),
		type = bits.pop(),
		size = bits.pop();

	if ( color === 'currentColor' ) {
		if ( this.currentColorWidget ) {
			color = this.currentColorWidget.getValue();
		}
	}
	if ( color.startsWith( '#' ) ) {
		this.colorWidget.setValue( color );
	}

	this.typeWidget.setValue( type );
	this.widthWidget.setValue( size );
	this.noneCheck.setSelected( true );
};
