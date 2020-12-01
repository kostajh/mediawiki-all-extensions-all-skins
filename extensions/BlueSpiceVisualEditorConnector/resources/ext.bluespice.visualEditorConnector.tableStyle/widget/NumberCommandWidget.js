bs.util.registerNamespace( 'bs.vec.ui.widget' );

bs.vec.ui.widget.NumberCommandWidget = function( contextItem ) {
	bs.vec.ui.widget.NumberCommandWidget.parent.call( this, contextItem );

	this.min = 0;
	this.max = 1000;
	this.step = 1;
	this.value = 0;

	this.command = '';
	this.property = '';
};

OO.inheritClass( bs.vec.ui.widget.NumberCommandWidget, bs.vec.ui.widget.CommandWidget );

bs.vec.ui.widget.NumberCommandWidget.prototype.executeAction = function() {
	var data = {};
	if ( !this.shouldExecute() ) {
		return;
	}
	this.numberWidget.getValidity()
		.done( function() {
			data[this.property] = this.numberWidget.getValue();
			this.contextItem.execCommand( this.command, data );
		}.bind( this ) );

};

bs.vec.ui.widget.NumberCommandWidget.prototype.initWidget = function() {
	this.numberWidget = new OO.ui.NumberInputWidget( {
		value: this.value,
		min: this.min,
		max: this.max,
		step: this.step
	} );

	this.numberWidget.connect( this, {
		change: 'executeAction'
	} );

	this.$element.append( this.numberWidget.$element );
	this.$element.addClass( 'bs-vec-number-widget' );
};
