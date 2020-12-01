bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.VerticalTextAlignmentStyle = function() {
	bs.vec.ui.VerticalTextAlignmentStyle.super.apply( this );
	this.section = bs.vec.ui.TableStyle.static.SECTION_CELL;
	this.applyTo = bs.vec.ui.TableStyle.static.ELEMENT_CELL;
};

OO.inheritClass( bs.vec.ui.VerticalTextAlignmentStyle , bs.vec.ui.TableStyle );

bs.vec.ui.VerticalTextAlignmentStyle.prototype.getAttribute = function() {
	return 'vertical-align';
};

bs.vec.ui.VerticalTextAlignmentStyle.prototype.getUnit = function() {
	return bs.vec.ui.TableStyle.static.UNIT_NONE;
};

bs.vec.ui.VerticalTextAlignmentStyle.prototype.getTool = function() {
	return {
		widget: bs.vec.ui.widget.VerticalTextAlignmentWidget,
		displaySection: bs.vec.ui.TableStyle.static.TYPE_ADDITIONAL,
		label: OO.ui.deferMsg( 'bs-vec-table-style-cell-text-vert-align' )
	};
};

bs.vec.ui.VerticalTextAlignmentStyle.prototype.executeAction = function( subject, args ) {
	var value = args.hasOwnProperty( 'verticalTextAlignment' ) ? args.verticalTextAlignment : null;

	if ( value === null ) {
		delete( subject.node.element.verticalTextAlignment );
	} else {
		subject.node.element.verticalTextAlignment = value;
	}
	return subject;
};

bs.vec.ui.VerticalTextAlignmentStyle.prototype.getModelProperty = function() {
	return 'verticalTextAlignment';
};

bs.vec.ui.VerticalTextAlignmentStyle.prototype.decorate = function ( $element ) {
	if ( !this.getAttribute() ) {
		return;
	}

	if ( !this.value ) {
		return;
	}

	$element.css( this.getAttribute(), this.getValue() );
};

bs.vec.registry.TableStyle.register( "verticalTextAlignment", new bs.vec.ui.VerticalTextAlignmentStyle() );
