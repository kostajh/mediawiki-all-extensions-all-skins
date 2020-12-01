bs.util.registerNamespace( 'bs.vec.command' );

bs.vec.command.SoftHyphen = function() {
	// Parent constructor
	bs.vec.command.SoftHyphen.super.call(
		this, 'bsInsertSoftHyphen'
	);
	this.warning = null;
	this.supportedSelections = ['linear'];
	this.action = 'content';
	this.method = 'insert';
};

/* Inheritance */

OO.inheritClass( bs.vec.command.SoftHyphen, ve.ui.Command );

/* Methods */

/**
 * @inheritdoc
 */
bs.vec.command.SoftHyphen.prototype.execute = function () {
	var surfaceModel = ve.init.target.getSurface().getModel();
	surfaceModel.getFragment()
		.adjustLinearSelection( 1 )
		.collapseToStart()
		.insertContent( [
			// \uOOAD => &shy;
			{ type: 'softHyphen', attributes: { character: '\u00AD' } }
		] );

	return true;
};

/* Registration */
ve.ui.commandRegistry.register( new bs.vec.command.SoftHyphen() );
