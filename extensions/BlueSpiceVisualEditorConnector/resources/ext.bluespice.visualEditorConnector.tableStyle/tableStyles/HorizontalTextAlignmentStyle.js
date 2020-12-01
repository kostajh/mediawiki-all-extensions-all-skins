bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.HorizontalTextAlignmentStyle = function() {
	bs.vec.ui.HorizontalTextAlignmentStyle.super.apply( this );
	this.section = bs.vec.ui.TableStyle.static.SECTION_CELL;
	this.applyTo = bs.vec.ui.TableStyle.static.ELEMENT_CELL;
};

OO.inheritClass( bs.vec.ui.HorizontalTextAlignmentStyle , bs.vec.ui.TableStyle );

bs.vec.ui.HorizontalTextAlignmentStyle.prototype.getAttribute = function() {
	return 'text-align';
};

bs.vec.ui.HorizontalTextAlignmentStyle.prototype.getUnit = function() {
	return bs.vec.ui.TableStyle.static.UNIT_NONE;
};

bs.vec.ui.HorizontalTextAlignmentStyle.prototype.getTool = function() {
	return {
		widget: bs.vec.ui.widget.HorizontalTextAlignmentWidget,
		displaySection: bs.vec.ui.TableStyle.static.TYPE_ADDITIONAL,
		label: OO.ui.deferMsg( 'bs-vec-table-style-cell-text-hor-align' )
	};
};

bs.vec.ui.HorizontalTextAlignmentStyle.prototype.executeAction = function( subject, args ) {
	var value = args.hasOwnProperty( 'horizontalTextAlignment' ) ? args.horizontalTextAlignment : null;

	if ( value === null ) {
		delete( subject.node.element.horizontalTextAlignment );
	} else {
		subject.node.element.horizontalTextAlignment = value;
	}
	return subject;
};

bs.vec.ui.HorizontalTextAlignmentStyle.prototype.getModelProperty = function() {
	return 'horizontalTextAlignment';
};

bs.vec.ui.HorizontalTextAlignmentStyle.prototype.decorate = function ( $element ) {
	if ( !this.getAttribute() ) {
		return;
	}

	if ( !this.value ) {
		return;
	}

	$element.css( this.getAttribute(), this.getValue() );
};

bs.vec.registry.TableStyle.register( "horizontalTextAlignment", new bs.vec.ui.HorizontalTextAlignmentStyle() );
