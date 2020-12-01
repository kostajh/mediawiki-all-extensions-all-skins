bs.util.registerNamespace( 'bs.vec.ce' );

bs.vec.ce.TableRowNode = function VeCeTableRowNode( options ) {
	// Parent constructor
	bs.vec.ce.TableRowNode.super.apply( this, arguments );

	var applicator = new bs.vec.ui.TableStyleApplicator( {
		model: options.element,
		domEl: this.$element
	} );
	applicator.apply();
};

/* Inheritance */

OO.inheritClass( bs.vec.ce.TableRowNode, ve.ce.TableRowNode );

ve.ce.nodeFactory.register( bs.vec.ce.TableRowNode );
