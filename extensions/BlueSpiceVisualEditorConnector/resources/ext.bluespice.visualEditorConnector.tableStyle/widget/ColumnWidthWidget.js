bs.util.registerNamespace( 'bs.vec.ui.widget' );

bs.vec.ui.widget.ColumnWidthWidget = function( contextItem ) {

	bs.vec.ui.widget.ColumnWidthWidget.parent.call( this, contextItem );

	this.property = 'columnWidth';
	this.command = 'columnWidth';

	this.units = [ 'px', '%' ];
	this.unit = this.units[0];
	this.value = contextItem.getStyles()[this.property] || 0;
	this.splitValue();
	this.min = 0;
	this.step = 5;

	this.$element.addClass( 'bs-vec-unit-selectable' );

	this.initWidget();
};

OO.inheritClass( bs.vec.ui.widget.ColumnWidthWidget, bs.vec.ui.widget.NumberCommandWidget );

bs.vec.ui.widget.ColumnWidthWidget.prototype.executeAction = function() {
	if ( !this.shouldExecute() ) {
		return;
	}
	var data = {};
	data[this.property] = this.numberWidget.getValue() + this.unit;
	this.contextItem.execCommand( this.command, data );
};

bs.vec.ui.widget.ColumnWidthWidget.prototype.initWidget = function() {
	var options = [], i = 0, split;
	bs.vec.ui.widget.ColumnWidthWidget.parent.prototype.initWidget.call( this );

	for( i; i < this.units.length; i++ ) {
		options.push( {
			data: this.units[i],
			label: this.units[i]
		} );
	}
	this.unitSelector = new OO.ui.DropdownInputWidget( {
		options: options,
		value: this.unit
	} );
	this.unitSelector.on( 'change', this.onUnitChange.bind( this ) );

	this.$element.append( this.unitSelector.$element );
};

bs.vec.ui.widget.ColumnWidthWidget.prototype.onUnitChange = function( value ) {
	this.unit = value;
	if ( this.unit === '%' && this.numberWidget.getValue() > 100  ) {
		this.numberWidget.setValue( 100 );
	}
	this.executeAction();
};

/**
 * Separates the value into value and unit
 * @returns true|false if value cannot be split
 */
bs.vec.ui.widget.ColumnWidthWidget.prototype.splitValue = function() {
	if ( typeof this.value !== 'string' || this.value === '' ) {
		return false;
	}
	for( var i = 0; i < this.units.length; i++ ) {
		if ( this.value.endsWith( this.units[i] ) ) {
			this.value = this.value.split( this.units[i] )[0];
			this.unit = this.units[i];
			return true;
		}
	}
	return false;
};