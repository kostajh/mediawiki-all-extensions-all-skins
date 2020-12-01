bs.util.registerNamespace( 'bs.vec.ui.widget' );

bs.vec.ui.widget.CellBackgroundWidget = function( contextItem ) {
	this.styles = contextItem.getStyles();
	if ( this.styles.hasOwnProperty( 'cellBackgroundColor' ) ) {
		if ( this.styles.cellBackgroundColor.hasOwnProperty( 'colorCode' ) ) {
			this.styles = { code: this.styles.cellBackgroundColor.colorCode };
		} else if ( this.styles.cellBackgroundColor.hasOwnProperty( 'colorClass' ) ) {
			this.styles = { class: this.styles.cellBackgroundColor.colorClass };
		} else {
			this.styles = {};
		}
	}
	this.command = 'cellBackgroundColor';
	this.widgetValue = this.styles;
	this.icon = 'cellBackgroundColor';

	bs.vec.ui.widget.CellBackgroundWidget.parent.call( this, contextItem );
};

OO.inheritClass( bs.vec.ui.widget.CellBackgroundWidget, bs.vec.ui.widget.ColorCommandWidget );
