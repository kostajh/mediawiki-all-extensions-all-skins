bs.util.registerNamespace( 'bs.vec.ui.widget' );

bs.vec.ui.widget.CommandWidget = function( contextItem ) {
	this.contextItem = contextItem;
	this.preventExecution = false;

	bs.vec.ui.widget.CommandWidget.parent.call( this );
	this.$input.remove();

	this.$element.addClass( 'bs-vec-command-widget' );
};

OO.inheritClass( bs.vec.ui.widget.CommandWidget, OO.ui.InputWidget );

bs.vec.ui.widget.CommandWidget.static.tagName = 'div';

bs.vec.ui.widget.CommandWidget.prototype.executeAction = function() {
	if ( this.preventExecution ) {
		return false;
	}
	return true;
};

bs.vec.ui.widget.CommandWidget.prototype.shouldExecute = function() {
	return !this.preventExecution;
};

bs.vec.ui.widget.CommandWidget.prototype.setShouldExecute = function( value ) {
	this.preventExecution = !value;
};
