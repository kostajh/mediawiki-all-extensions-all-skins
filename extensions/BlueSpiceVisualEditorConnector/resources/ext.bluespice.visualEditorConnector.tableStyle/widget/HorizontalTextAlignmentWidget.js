bs.util.registerNamespace( 'bs.vec.ui.widget' );

bs.vec.ui.widget.HorizontalTextAlignmentWidget = function( contextItem ) {
	bs.vec.ui.widget.HorizontalTextAlignmentWidget.parent.call( this, contextItem );

	var styles = contextItem.getStyles();
	this.horizontalTextAlignment = styles.horizontalTextAlignment || 'left';

	this.positionSelector = new OO.ui.ButtonSelectWidget( {
		items: [
			new OO.ui.ButtonOptionWidget( {
				data: 'left',
				label: OO.ui.deferMsg( 'bs-vec-table-style-cell-text-hor-align-left' )
			} ),
			new OO.ui.ButtonOptionWidget( {
				data: 'center',
				label: OO.ui.deferMsg( 'bs-vec-table-style-cell-text-hor-align-center' )
			} ),
			new OO.ui.ButtonOptionWidget( {
				data: 'right',
				label: OO.ui.deferMsg( 'bs-vec-table-style-cell-text-hor-align-right' )
			} ),
			new OO.ui.ButtonOptionWidget( {
				data: 'justify',
				label: OO.ui.deferMsg( 'bs-vec-table-style-cell-text-hor-align-justify' )
			} )
		]
	} );
	this.positionSelector.connect( this, {
		select: this.onChange
	} );
	this.positionSelector.selectItemByData( this.horizontalTextAlignment );
	this.$element.append( this.positionSelector.$element );
	this.$element.addClass( 'bs-vec-horizontal-text-alignment-widget' );
};

OO.inheritClass( bs.vec.ui.widget.HorizontalTextAlignmentWidget, bs.vec.ui.widget.CommandWidget );

bs.vec.ui.widget.HorizontalTextAlignmentWidget.prototype.onChange = function( selected ) {
	if ( !selected ) {
		return;
	}
	this.horizontalTextAlignment = selected.data;
	this.executeAction();
};

bs.vec.ui.widget.HorizontalTextAlignmentWidget.prototype.executeAction = function() {
	if ( !this.shouldExecute() ) {
		return;
	}
	this.horizontalTextAlignment = this.horizontalTextAlignment || 'middle';
	this.contextItem.execCommand( 'horizontalTextAlignment', { horizontalTextAlignment: this.horizontalTextAlignment } );
};
