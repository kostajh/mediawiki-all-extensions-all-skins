bs.util.registerNamespace( 'bs.vec.ui.widget' );

bs.vec.ui.widget.RowHeightWidget = function( contextItem ) {

	bs.vec.ui.widget.RowHeightWidget.parent.call( this, contextItem );

	this.value = contextItem.getStyles().rowHeight || 25;
	this.min = 25;
	this.max = 400;
	this.step = 5;

	this.command = 'rowHeight';
	this.property = 'rowHeight';

	this.initWidget();
};

OO.inheritClass( bs.vec.ui.widget.RowHeightWidget, bs.vec.ui.widget.NumberCommandWidget );
