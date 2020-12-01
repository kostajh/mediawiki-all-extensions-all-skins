
/**
 * Select entire row/column on icon click
 *
 * @param {jQuery.Event} e Mouse down event
 */
ve.ui.TableLineContext.prototype.onIconMouseDown = function ( e ) {
	e.preventDefault();
	var surfaceModel = this.tableNode.surface.getModel(),
		selection = surfaceModel.getSelection(),
		matrix = selection.getTableNode( surfaceModel.getDocument() ).getMatrix(),
		newSelection = [];
	if ( this.itemGroup === 'col' ) {
		newSelection = [ selection.fromCol, 0, selection.toCol, matrix.getRowCount() - 1 ];
	} else {
		// We can only select one row at a time, since column count can vary between rows
		// Is it better to implicitly select the first row, or just bail out and don't change the selection?
		newSelection = [ 0, selection.fromRow, matrix.getMaxColCount() - 1, selection.toRow ];
	}

	surfaceModel.setSelection( new ve.dm.TableSelection(
		selection.getDocument(),
		// tableNode range was changed by deletion
		selection.getTableNode( surfaceModel.getDocument() ).getOuterRange(),
		newSelection[ 0 ], newSelection[ 1 ], newSelection[ 2 ], newSelection[ 3 ]
	) );
};

/**
 * No-op
 * @param e
 */
ve.ui.TableLineContext.prototype.onDocumentMouseDown = function ( e ) {
	return;
};
