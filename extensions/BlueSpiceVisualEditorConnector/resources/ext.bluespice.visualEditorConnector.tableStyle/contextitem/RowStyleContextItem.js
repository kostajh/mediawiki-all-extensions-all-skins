bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.RowStyleContextItem = function ( context, model, config ) {

	this.actions = {
		duplicateRow: {
			icon: 'tableDuplicateRow',
			label: OO.ui.deferMsg( 'bs-vec-contextitem-row-duplicate' ),
			displaySection: 'quick',
			expensive: true
		},
		insertRowAfter: {
			icon: 'tableAddRowAfter',
			label: OO.ui.deferMsg( 'visualeditor-table-insert-row-after' ),
			displaySection: 'quick',
			expensive: true
		},
		moveRowBefore: {
			icon: 'tableMoveRowBefore',
			label: OO.ui.deferMsg( 'visualeditor-table-move-row-before' ),
			displaySection: 'quick',
			expensive: true
		},
		moveRowAfter: {
			icon: 'tableMoveRowAfter',
			label: OO.ui.deferMsg( 'visualeditor-table-move-row-after' ),
			displaySection: 'quick',
			expensive: true
		},
		deleteRow: {
			icon: 'trash',
			label: OO.ui.deferMsg( 'visualeditor-table-delete-row' ),
			displaySection: 'quick',
			expensive: true
		}
	};

	// Parent constructor
	bs.vec.ui.RowStyleContextItem.super.call( this, context, model, config );
};

OO.inheritClass( bs.vec.ui.RowStyleContextItem, bs.vec.ui.TableContextItem  );

bs.vec.ui.RowStyleContextItem.static.name = 'rowStyle';

bs.vec.ui.RowStyleContextItem.static.icon = null;

bs.vec.ui.RowStyleContextItem.static.label = OO.ui.deferMsg( 'bs-vec-table-inspector-row' );

bs.vec.ui.RowStyleContextItem.static.embeddable = false;

bs.vec.ui.RowStyleContextItem.prototype.getStyles = function () {
	if ( this.model.parent.element.type !== 'tableRow' ) {
		return {};
	}
	return this.model.parent.element;
};

bs.vec.ui.RowStyleContextItem.prototype.getSection = function () {
	return 'row';
};

bs.vec.ui.RowStyleContextItem.prototype.getAdditionalOptionsTitle = function () {
	return OO.ui.deferMsg( 'bs-vec-contextitem-row-style-add-options-title' );
};

ve.ui.contextItemFactory.register( bs.vec.ui.RowStyleContextItem );