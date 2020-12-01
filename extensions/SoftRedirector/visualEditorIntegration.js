/*!
 * VisualEditor DataModel MWSoftRedirectMetaItem class.
 *
 * @copyright 2011-2014 VisualEditor Team
 * @license The MIT License (MIT); see COPYING
 */

/**
 * DataModel soft redirect meta item (for __SOFTREDIR__).
 *
 * @class
 * @extends ve.dm.MetaItem
 * @constructor
 * @param {Object} element Reference to element in meta-linmod
 */
ve.dm.MWSoftRedirectMetaItem = function VeDmMWSoftRedirectMetaItem( element ) {
	// Parent constructor
	ve.dm.MetaItem.call( this, element );
};

/* Inheritance */

OO.inheritClass( ve.dm.MWSoftRedirectMetaItem, ve.dm.MetaItem );

/* Static Properties */

ve.dm.MWSoftRedirectMetaItem.static.name = 'mwSoftRedirect';

ve.dm.MWSoftRedirectMetaItem.static.group = 'mwSoftRedirect';

ve.dm.MWSoftRedirectMetaItem.static.matchTagNames = [ 'meta' ];

ve.dm.MWSoftRedirectMetaItem.static.matchRdfaTypes = [ 'mw:PageProp/softredirect' ];

ve.dm.MWSoftRedirectMetaItem.static.toDataElement = function () {
	return { type: this.name };
};

ve.dm.MWSoftRedirectMetaItem.static.toDomElements = function ( dataElement, doc ) {
	var meta = doc.createElement( 'meta' );
	meta.setAttribute( 'property', 'mw:PageProp/softredirect' );
	return [ meta ];
};

/* Registration */

ve.dm.modelRegistry.register( ve.dm.MWSoftRedirectMetaItem );

ve.ui.MWSettingsPage.static.addMetaCheckbox(
	'mwSoftRedirect',
	mw.msg( 'visualeditor-dialog-meta-settings-softredirect-label' )
);
