bs.util.registerNamespace( 'bs.hidetitle.dm' );

/**
 * DataModel for __HIDETITLE__
 *
 * @class
 * @extends ve.dm.MetaItem
 * @constructor
 * @param {Object} element Reference to element in meta-linmod
 */
bs.hidetitle.dm.BSHideTitleMetaItem = function BsHideTitleDmBsHideTitleMetaItem() {
	// Parent constructor
	bs.hidetitle.dm.BSHideTitleMetaItem.super.apply( this, arguments );
};

/* Inheritance */

OO.inheritClass( bs.hidetitle.dm.BSHideTitleMetaItem, ve.dm.MetaItem );

/* Static Properties */

bs.hidetitle.dm.BSHideTitleMetaItem.static.name = 'bsHideTitle';

bs.hidetitle.dm.BSHideTitleMetaItem.static.group = 'bsHideTitle';

bs.hidetitle.dm.BSHideTitleMetaItem.static.matchTagNames = [ 'meta' ];

bs.hidetitle.dm.BSHideTitleMetaItem.static.matchRdfaTypes = [ 'mw:PageProp/bs_hidetitle' ];

bs.hidetitle.dm.BSHideTitleMetaItem.static.toDomElements = function ( dataElement, doc ) {
	var meta = doc.createElement( 'meta' );
	meta.setAttribute( 'property', 'mw:PageProp/bs_hidetitle' );
	return [ meta ];
};

/* Registration */

ve.dm.modelRegistry.register( bs.hidetitle.dm.BSHideTitleMetaItem );