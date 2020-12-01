bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.RowHeightStyle = function() {
	bs.vec.ui.RowHeightStyle.super.apply( this );
	this.section = bs.vec.ui.TableStyle.static.SECTION_ROW;
	this.applyTo = bs.vec.ui.TableStyle.static.ELEMENT_ROW;
};

OO.inheritClass( bs.vec.ui.RowHeightStyle , bs.vec.ui.TableStyle );

bs.vec.ui.RowHeightStyle.prototype.getAttribute = function() {
	return 'height';
};

bs.vec.ui.RowHeightStyle.prototype.getUnit = function() {
	return bs.vec.ui.TableStyle.static.UNIT_PIXEL;
};

bs.vec.ui.RowHeightStyle.prototype.getTool = function() {
	return {
		widget: bs.vec.ui.widget.RowHeightWidget,
		displaySection: bs.vec.ui.TableStyle.static.TYPE_ADDITIONAL,
		label: OO.ui.deferMsg( 'bs-vec-table-style-row-height' )
	};
};

bs.vec.ui.RowHeightStyle.prototype.executeAction = function( subject, args ) {
	if ( !args.hasOwnProperty( 'rowHeight' ) ) {
		return;
	}
	var height = args.rowHeight;

	return ve.dm.TableRowNode.static.applyStylings( subject, { rowHeight: height } );
};

bs.vec.ui.RowHeightStyle.prototype.getModelProperty = function() {
	return 'rowHeight';
};

bs.vec.registry.TableStyle.register( "rowHeight", new bs.vec.ui.RowHeightStyle() );