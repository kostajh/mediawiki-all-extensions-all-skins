bs.util.registerNamespace( 'bs.vec.ui.widget' );

bs.vec.ui.widget.VerticalTextAlignmentWidget = function( contextItem ) {
	bs.vec.ui.widget.VerticalTextAlignmentWidget.parent.call( this, contextItem );

	var styles = contextItem.getStyles();
	this.verticalTextAlignment = styles.verticalTextAlignment || 'middle';

	this.positionSelector = new OO.ui.ButtonSelectWidget( {
		items: [
			new OO.ui.ButtonOptionWidget( {
				data: 'top',
				label: OO.ui.deferMsg( 'bs-vec-table-style-cell-text-vert-align-top' )
			} ),
			new OO.ui.ButtonOptionWidget( {
				data: 'middle',
				label: OO.ui.deferMsg( 'bs-vec-table-style-cell-text-vert-align-middle' )
			} ),
			new OO.ui.ButtonOptionWidget( {
				data: 'bottom',
				label: OO.ui.deferMsg( 'bs-vec-table-style-cell-text-vert-align-bottom' )
			} )
		]
	} );
	this.positionSelector.connect( this, {
		select: this.onChange
	} );
	this.positionSelector.selectItemByData( this.verticalTextAlignment );
	this.$element.append( this.positionSelector.$element );
	this.$element.addClass( 'bs-vec-vertical-text-alignment-widget' );
};

OO.inheritClass( bs.vec.ui.widget.VerticalTextAlignmentWidget, bs.vec.ui.widget.CommandWidget );

bs.vec.ui.widget.VerticalTextAlignmentWidget.prototype.onChange = function( selected ) {
	if ( !selected ) {
		return;
	}
	this.verticalTextAlignment = selected.data;
	this.executeAction();
};

bs.vec.ui.widget.VerticalTextAlignmentWidget.prototype.executeAction = function() {
	if ( !this.shouldExecute() ) {
		return;
	}
	this.verticalTextAlignment = this.verticalTextAlignment || 'middle';
	this.contextItem.execCommand( 'verticalTextAlignment', { verticalTextAlignment: this.verticalTextAlignment } );
};
