bs.util.registerNamespace( 'bs.vec.dm' );

bs.vec.dm.SoftHyphenNode = function () {
	// Parent constructor
	bs.vec.dm.SoftHyphenNode .super.apply( this, arguments );
};

/* Inheritance */
OO.inheritClass( bs.vec.dm.SoftHyphenNode, ve.dm.MWEntityNode );

/* Static Properties */
bs.vec.dm.SoftHyphenNode.static.name = 'softHyphen';


/* Registration */
ve.dm.modelRegistry.register( bs.vec.dm.SoftHyphenNode  );
