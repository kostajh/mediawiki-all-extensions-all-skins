bs.util.registerNamespace( 'bs.vec.ce' );

bs.vec.ce.TableCellNode = function VeCeTableCellNode( options ) {
	// Parent constructor
	bs.vec.ce.TableCellNode.super.apply( this, arguments );

	var applicator = new bs.vec.ui.TableStyleApplicator( {
		model: options.element,
		domEl: this.$element
	} );
	applicator.apply();
};

/* Inheritance */

OO.inheritClass( bs.vec.ce.TableCellNode, ve.ce.TableCellNode );

ve.ce.nodeFactory.register( bs.vec.ce.TableCellNode );
