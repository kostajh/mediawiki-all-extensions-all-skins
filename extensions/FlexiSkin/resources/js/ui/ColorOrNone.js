flexiskin.ui.widget.ColorOrNone = function ( cfg ) {
	cfg = cfg || {};
	flexiskin.ui.widget.ColorOrNone.parent.call( this, cfg );
	this.$input.remove();

	this.noneCheck = new OO.ui.CheckboxInputWidget();

	this.colorWidget = new flexiskin.ui.widget.ColorPicker();
	this.$colorWidgetWrap = $( '<div>' ).append( this.colorWidget.$element ).addClass( 'color-picker-wrapper' );
	this.noneCheck.connect( this, {
		change: 'onNoneChange'
	} );

	this.$element.append( this.noneCheck.$element, this.$colorWidgetWrap );
	this.colorWidget.$element.hide();

	this.$element.addClass( 'flexiskin-widget-color-or-none' );
};

OO.inheritClass( flexiskin.ui.widget.ColorOrNone, OO.ui.InputWidget );

flexiskin.ui.widget.ColorOrNone.prototype.onNoneChange = function ( value ) {
	if ( value ) {
		this.colorWidget.$element.show();
	} else {
		this.colorWidget.$element.hide();
	}
};

flexiskin.ui.widget.ColorOrNone.prototype.getValue = function () {
	if ( this.noneCheck.isSelected() === false ) {
		return 'none';
	}
	return this.colorWidget.getValue();
};

flexiskin.ui.widget.ColorOrNone.prototype.setValue = function ( value ) {
	if ( !value || value === 'none' ) {
		return;
	}

	this.noneCheck.setSelected( true );
	this.colorWidget.setValue( value );
};
