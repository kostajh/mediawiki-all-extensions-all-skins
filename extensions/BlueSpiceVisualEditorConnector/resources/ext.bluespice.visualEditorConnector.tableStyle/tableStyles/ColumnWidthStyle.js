bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.ColumnWidthStyle = function() {
	bs.vec.ui.ColumnWidthStyle.super.apply( this );
	this.section = bs.vec.ui.TableStyle.static.SECTION_COLUMN;
	this.applyTo = bs.vec.ui.TableStyle.static.ELEMENT_CELL;
};

OO.inheritClass( bs.vec.ui.ColumnWidthStyle, bs.vec.ui.TableStyle );

bs.vec.ui.ColumnWidthStyle.prototype.getAttribute = function() {
	return 'width';
};

bs.vec.ui.ColumnWidthStyle.prototype.getUnit = function() {
	return bs.vec.ui.TableStyle.static.UNIT_NONE;
};

bs.vec.ui.ColumnWidthStyle.prototype.getTool = function() {
	return {
		widget: bs.vec.ui.widget.ColumnWidthWidget,
		displaySection: bs.vec.ui.TableStyle.static.TYPE_ADDITIONAL,
		label: OO.ui.deferMsg( 'bs-vec-table-style-column-width' )
	};
};

bs.vec.ui.ColumnWidthStyle.prototype.executeAction = function( subject, args, action ) {
	var fragment, unit, value = args.hasOwnProperty( 'columnWidth' ) ? args.columnWidth : null,
		surfaceModel = action.surface.getModel();

	if ( value === null ) {
		delete( subject.node.element.columnWidth );
	} else {
		if ( value.substr( value.length -1 ) === '%' ) {
			// In order to use percent values for columns, table must have a width
			// Easiest way is to make it 100%. Doing it like this, will also
			// set table width to 100% in table properties, so we are consistent
			fragment = surfaceModel.getLinearFragment(
				surfaceModel.getSelection().tableRange, true
			);

			fragment.changeAttributes( {
				tableWidth: 100
			} );
		}
		subject.node.element.columnWidth = value;
	}
	return subject;
};

bs.vec.ui.ColumnWidthStyle.prototype.getModelProperty = function() {
	return 'columnWidth';
};

bs.vec.registry.TableStyle.register( "columnWidth", new bs.vec.ui.ColumnWidthStyle() );
