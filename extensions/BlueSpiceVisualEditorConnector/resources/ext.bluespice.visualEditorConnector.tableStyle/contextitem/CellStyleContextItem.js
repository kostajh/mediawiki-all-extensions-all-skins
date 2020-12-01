bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.CellStyleContextItem = function ( context, model, config ) {
	this.actions = {
		bold: {
			icon: 'bold',
			label: OO.ui.deferMsg( 'visualeditor-annotationbutton-bold-tooltip' ),
			displaySection: 'quick'
		},
		italic: {
			icon: 'italic',
			label: OO.ui.deferMsg( 'visualeditor-annotationbutton-italic-tooltip' ),
			displaySection: 'quick'
		},
		underline: {
			icon: 'underline',
			label: OO.ui.deferMsg( 'visualeditor-annotationbutton-underline-tooltip' ),
			displaySection: 'quick'
		}
	};

	// Parent constructor
	bs.vec.ui.CellStyleContextItem.super.call( this, context, model, config );
};

OO.inheritClass( bs.vec.ui.CellStyleContextItem, bs.vec.ui.TableContextItem  );

bs.vec.ui.CellStyleContextItem.static.name = 'cellStyle';

bs.vec.ui.CellStyleContextItem.static.icon = null;

bs.vec.ui.CellStyleContextItem.static.label = OO.ui.deferMsg( 'bs-vec-table-inspector-cell' );

bs.vec.ui.CellStyleContextItem.static.embeddable = false;

bs.vec.ui.CellStyleContextItem.prototype.getStyles = function () {
	if ( this.model.element.type !== 'tableCell' ) {
		return {};
	}
	return this.model.element;
};

bs.vec.ui.CellStyleContextItem.prototype.getSection = function () {
	return 'cell';
};

bs.vec.ui.CellStyleContextItem.prototype.getAdditionalOptionsTitle = function () {
	return OO.ui.deferMsg( 'bs-vec-contextitem-cell-style-add-options-title' );
};

ve.ui.contextItemFactory.register( bs.vec.ui.CellStyleContextItem );