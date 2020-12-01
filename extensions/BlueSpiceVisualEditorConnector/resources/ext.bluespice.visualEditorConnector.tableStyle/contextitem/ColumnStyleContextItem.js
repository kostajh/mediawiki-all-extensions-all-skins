bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.ColumnStyleContextItem = function ( context, model, config ) {

	this.actions = {
		duplicateColumn: {
			icon: 'tableDuplicateCol',
			label: OO.ui.deferMsg( 'bs-vec-contextitem-column-duplicate' ),
			displaySection: 'quick',
			expensive: true
		},
		insertColumnAfter: {
			icon: 'tableAddColumnAfter',
			label: OO.ui.deferMsg( 'visualeditor-table-insert-col-after' ),
			displaySection: 'quick',
			expensive: true
		},
		moveColumnBefore: {
			icon: 'tableMoveColumnBefore',
			label: OO.ui.deferMsg( 'visualeditor-table-move-col-before' ),
			displaySection: 'quick',
			expensive: true
		},
		moveColumnAfter: {
			icon: 'tableMoveColumnAfter',
			label: OO.ui.deferMsg( 'visualeditor-table-move-col-after' ),
			displaySection: 'quick',
			expensive: true
		},
		deleteColumn: {
			icon: 'trash',
			label: OO.ui.deferMsg( 'visualeditor-table-delete-col' ),
			displaySection: 'quick',
			expensive: true
		}
	};

	// Parent constructor
	bs.vec.ui.ColumnStyleContextItem.super.call( this, context, model, config );
};

OO.inheritClass( bs.vec.ui.ColumnStyleContextItem, bs.vec.ui.TableContextItem  );

bs.vec.ui.ColumnStyleContextItem.static.name = 'columnStyle';

bs.vec.ui.ColumnStyleContextItem.static.icon =  null;

bs.vec.ui.ColumnStyleContextItem.static.label = OO.ui.deferMsg( 'bs-vec-table-inspector-column' );

bs.vec.ui.ColumnStyleContextItem.prototype.getStyles = function () {
	if ( this.model.element.type !== 'tableCell' ) {
		return {};
	}
	return this.model.element;
};

bs.vec.ui.ColumnStyleContextItem.prototype.getSection = function () {
	return 'col';
};

bs.vec.ui.ColumnStyleContextItem.prototype.getAdditionalOptionsTitle = function () {
	return OO.ui.deferMsg( 'bs-vec-contextitem-column-style-add-options-title' );
};

ve.ui.contextItemFactory.register( bs.vec.ui.ColumnStyleContextItem );
