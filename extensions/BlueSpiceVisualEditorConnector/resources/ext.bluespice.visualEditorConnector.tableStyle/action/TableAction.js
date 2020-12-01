bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.TableAction = function () {
	// Parent constructor
	ve.ui.TableAction.super.apply( this, arguments );
	this.tableStyleRegistry =  bs.vec.registry.TableStyle.registry;

	var action = this;
	for( var key in this.tableStyleRegistry ) {
		if ( !this.tableStyleRegistry.hasOwnProperty( key ) ) {
			continue;
		}
		var handler = this.tableStyleRegistry[key];
		if ( handler.shouldOverrideAction() === true ) {
			// This will create a function for each command
			bs.vec.ui.TableAction.prototype[key] = function( args ) {
				action.executeAction.call( this, action, args );
			}.bind( handler );
		}
	}
};

/* Inheritance */

OO.inheritClass( bs.vec.ui.TableAction, ve.ui.TableAction );

/* Static Properties */

bs.vec.ui.TableAction.static.name = 'bs-vec-table';

bs.vec.ui.TableAction.static.methods = Object.keys( bs.vec.registry.TableStyle.registry ) + [
	'duplicateRow', 'duplicateColumn'
];

/**
 * Gets the selection and runs each appropriate node( row, cell ) though
 * the handler's executeAction method, afterwards, restores the original selection
 *
 * Context here is handler for the action, one of this.tableStyleRegistry handlers
 *
 * @param bs.vec.ui.TableAction action
 * @param array args
 */
bs.vec.ui.TableAction.prototype.executeAction = function( action, args ) {
	var surfaceModel = action.surface.getModel(),
		selection = surfaceModel.getSelection(),
		tableNode = selection.getTableNode( surfaceModel.getDocument() ),
		oldSelection = [ selection.fromCol, selection.fromRow, selection.toCol, selection.toRow ],
		section;

	section = this.getSection();
	if ( section ) {
		action.forEach( section, selection, surfaceModel, tableNode, this.executeAction.bind( action ), args );
	}

	action.restoreSelection( surfaceModel, selection, tableNode, oldSelection );
};

bs.vec.ui.TableAction.prototype.forEach = function( what, selection, surfaceModel, tableNode, cb, cbParams ) {
	var matrix = tableNode.getMatrix(),
		cellIndex = 0, rowIndex, colIndex, newRow, rowNode, cells;

	if ( what === 'col' ) {
		cells = [];
		for( colIndex = selection.startCol; colIndex <= selection.endCol; colIndex++ ) {
			cells = matrix.getColumn( colIndex );
			for ( cellIndex = 0; cellIndex < cells.length; cellIndex++ ) {
				cells[cellIndex] = cb( cells[cellIndex], cbParams, this );
			}
			this.replace( selection, surfaceModel.getDocument(), what, colIndex, { cells: cells } );
		}
		return;
	} else {
		for( rowIndex = selection.startRow; rowIndex <= selection.endRow; rowIndex++ ) {
			cells = matrix.getRow( rowIndex );
			rowNode = matrix.getRowNode( rowIndex );
			if ( what === 'row' ) {
				newRow = {
					row: cb( rowNode, cbParams, this ),
					cells: cells
				};
			} else if ( what === 'cell' ) {
				for ( cellIndex = 0; cellIndex < cells.length; cellIndex++ ) {
					if ( cellIndex >= selection.startCol && cellIndex <= selection.endCol ) {
						cells[cellIndex] = cb( cells[cellIndex], cbParams, this );
					}
				}

				newRow = {
					row: ve.dm.TableRowNode.static.applyStylings( matrix.getRowNode( rowIndex ), {} ),
					cells: cells
				};
			}
			this.replace( selection, surfaceModel.getDocument(), 'row', rowIndex, newRow );
		}
	}

	return true;
};

bs.vec.ui.TableAction.prototype.restoreSelection = function( surfaceModel, selection, tableNode, oldSelection ) {
	surfaceModel.setSelection( new ve.dm.TableSelection(
		// tableNode range was changed by deletion
		tableNode.getOuterRange(),
		oldSelection[ 0 ], oldSelection[ 1 ], oldSelection[ 2 ], oldSelection[ 3 ]
	) );
};

bs.vec.ui.TableAction.prototype.hasMutliRowCells = function( cells ) {
	var cellIndex, cell;

	for( cellIndex = 0; cellIndex < cells.length; cellIndex++ ) {
		cell = cells[cellIndex];
		if ( !cell.node.element.attributes.hasOwnProperty( 'rowspan' ) ) {
			continue;
		}
		if ( cell.node.element.attributes.rowspan < 2 ) {
			continue;
		}
		return true;
	}
	return false;
};

bs.vec.ui.TableAction.prototype.duplicate = function( mode ) {
	var surfaceModel = this.surface.getModel(),
		document = surfaceModel.getDocument(),
		selection = surfaceModel.getSelection(),
		tableNode = selection.getTableNode( surfaceModel.getDocument() ),
		matrix = tableNode.getMatrix(),
		oldSelection = [ selection.fromCol, selection.fromRow, selection.toCol, selection.toRow ],
		newRow, cells, itemIndex, indexAfterAddditions;

	if ( !( selection instanceof ve.dm.TableSelection ) ) {
		return false;
	}

	if ( mode === 'row' ) {
		indexAfterAddditions = selection.toRow;
		for ( itemIndex = selection.fromRow; itemIndex <= indexAfterAddditions; itemIndex++ ) {
			cells = matrix.getRow( itemIndex );
			// If row contains cells that span over multiple rows, we cannot duplicate - alert user
			if ( this.hasMutliRowCells( cells ) ) {
				OO.ui.alert( OO.ui.deferMsg( 'bs-vec-row-duplicate-has-merged' ) );
				return;
			}
			newRow = {
				row: ve.dm.TableRowNode.static.applyStylings( matrix.getRowNode( itemIndex ), {} ),
				cells: cells
			};
			// Don't ask...
			this.replace( selection, document, 'row', itemIndex, newRow );
			this.insertRowOrCol( tableNode, 'row', itemIndex, 'after', null, newRow );
			// Number of rows changed
			itemIndex++;
			indexAfterAddditions++;
		}
	} else if ( mode === 'col' ) {
		indexAfterAddditions = selection.toCol;
		for ( itemIndex = selection.fromCol; itemIndex <= indexAfterAddditions; itemIndex++ ) {
			cells = matrix.getColumn( itemIndex );
			this.replace( selection, document, mode, itemIndex, { cells: cells }  );
			this.insertRowOrCol( tableNode, mode, itemIndex, 'after', null, { cells: cells } );
			itemIndex++;
			indexAfterAddditions++;
		}
	}
	this.restoreSelection( surfaceModel, selection, tableNode, oldSelection );
};

bs.vec.ui.TableAction.prototype.replace = function( selection, document, mode, index, replacement ) {
	var tableNode = selection.getTableNode( document ),
		matrix = tableNode.getMatrix(),
		position;

	if ( !( selection instanceof ve.dm.TableSelection ) ) {
		return false;
	}
	this.deleteRowsOrColumns( matrix, mode, index, index );

	position = 'after';
	if ( index === 0 ) {
		position = 'before';
	} else {
		index--;
	}

	this.insertRowOrCol( tableNode, mode, index, position, null, replacement );
};

bs.vec.ui.TableAction.prototype.cellBorder = function( args ) {
	var surfaceModel = this.surface.getModel(),
		selection = surfaceModel.getSelection(),
		tableNode = selection.getTableNode( surfaceModel.getDocument() ),
		oldSelection = [ selection.fromCol, selection.fromRow, selection.toCol, selection.toRow ],
		handler = this.tableStyleRegistry.cellBorder, isCollapsed;

	if ( args.hasOwnProperty( 'value' ) ) {
		// Normalization
		args = args.value;
	}

	isCollapsed = args.isCollapsed || true;

	// Get all cells that are targeted with this action, decorating them with appropriate attributes
	// This method will be called recursively internally allowing setting multiple properties on one cell -
	// eg. top left corner cell need to have both left and top border. This method allows being called
	// multiple times, once for top and once for left, and returns single cell that has both top and left set
	var targets = this.getTargetCells( selection, args, isCollapsed );

	this.execBorderStyle( targets, surfaceModel, selection, handler.executeAction.bind( this ), args );

	this.restoreSelection( surfaceModel, selection, tableNode, oldSelection );
};

bs.vec.ui.TableAction.prototype.getTargetCells = function( selection, args, isCollapsed ) {
	var mode = args.mode, cells = [];
	// Clone selection, so that recursive calls don't screw it up
	selection = $.extend( {}, selection );

	// For modes "clear", "all" and "none" all cells are targeted anyway, so no need to pre-clear them
	if ( mode !== 'clear' && mode !== 'all' && !args.internal ) {
		if ( mode === 'none' ) {
			// Reset all properties when settings no borders, as it might produce unexpected
			// results if some borders are set later on
			cells = this.getTargetCells( selection, { prop: {
				style: 'none',
				width: null,
				color: null
			}, mode: 'none', internal: true }, isCollapsed );
			return cells;
		}
		// If we are not explicitly setting no borders, just clear out borders for all non-targeted cell sides
		cells = this.getTargetCells( selection, { prop: { style: 'none' }, mode: 'none', internal: true }, isCollapsed );
	}

	switch( mode ) {
		case 'left':
			cells = cells.concat( this.getCol( selection, selection.startCol, args ) );
			if ( isCollapsed ) {
				// In a table with collapsed borders, left border of a cell is actually the right border of the cell to the left
				// Due to that we need to set the styling to both left border of the cell we are actually targeting
				// and to the right border of the cell left of it. Same is true for other borders.
				cells = cells.concat( this.getCol( selection, selection.startCol - 1, $.extend( args, {
					mode: 'right',
					adjacent: true
				} ) ) );
			}
			break;
		case 'right':
			cells = cells.concat( this.getCol( selection, selection.endCol, args ) );
			if ( isCollapsed ) {
				cells = cells.concat( this.getCol( selection, selection.endCol + 1, $.extend( args, {
					mode: 'left',
					adjacent: true
				} ) ) );
			}
			break;
		case 'top':
			cells = cells.concat( this.getRow( selection, selection.startRow, args ) );
			if ( isCollapsed ) {
				cells =  cells.concat( this.getRow( selection, selection.startRow - 1, $.extend( args, {
					mode: 'bottom',
					adjacent: true
				} ) ) );
			}
			break;
		case 'bottom':
			cells = cells.concat( this.getRow( selection, selection.endRow, args ) );
			if ( isCollapsed ) {
				cells = cells.concat( this.getRow( selection, selection.endRow + 1, $.extend( args, {
					mode: 'top',
					adjacent: true
				} ) ) );
			}
			break;
		case 'topbottom':
			cells = cells.concat( this.getTargetCells( selection, $.extend( args, { mode: 'top', internal: true } ), isCollapsed ) );
			cells = cells.concat( this.getTargetCells( selection, $.extend( args, { mode: 'bottom', internal: true } ), isCollapsed ) );
			break;
		case 'leftright':
			cells = cells.concat( this.getTargetCells( selection, $.extend( args, { mode: 'left', internal: true } ), isCollapsed ) );
			cells = cells.concat( this.getTargetCells( selection, $.extend( args, { mode: 'right', internal: true } ), isCollapsed ) );
			break;
		case 'round':
			cells = cells.concat( this.getTargetCells( selection, $.extend( args, { mode: 'left', internal: true } ), isCollapsed ) );
			cells = cells.concat( this.getTargetCells( selection, $.extend( args, { mode: 'right', internal: true } ), isCollapsed ) );
			cells = cells.concat( this.getTargetCells( selection, $.extend( args, { mode: 'top', internal: true } ), isCollapsed ) );
			cells = cells.concat( this.getTargetCells( selection, $.extend( args, { mode: 'bottom', internal: true } ), isCollapsed ) );
			break;
		case 'horizontal':
			cells = cells.concat( this.getTargetCells( selection, $.extend( args, { mode: 'top', internal: true } ), isCollapsed ) );
			cells = cells.concat( this.getTargetCells( selection, $.extend( args, {
				mode: 'inner', includeEdges: true, internal: true, applyMode: 'bottom'
			} ), isCollapsed ) );
			break;
		case 'roundhorizontal':
			cells = cells.concat( this.getTargetCells( selection, $.extend( args, { mode: 'round', internal: true } ), isCollapsed ) );
			cells = cells.concat( this.getTargetCells( selection, $.extend( args, { mode: 'horizontal', internal: true } ), isCollapsed ) );
			break;
		case 'roundvertical':
			cells = cells.concat( this.getTargetCells( selection, $.extend( args, { mode: 'round', internal: true } ), isCollapsed ) );
			cells = cells.concat( this.getTargetCells( selection, $.extend( args, {
				mode: 'inner', includeEdges: false, internal: true, applyMode: 'right'
			} ), isCollapsed ) );
			break;
		case 'inner':
			var applyMode = args.applyMode || 'all', i = 0;

			for( i = selection.startRow; i <= selection.endRow ; i++ ) {
				cells = cells.concat( this.getRow( selection, i, $.extend( args, { mode: applyMode } ) ) );
			}
			break;
		case 'all':
		case 'none':
		case 'clear':
			cells = cells.concat( this.getTargetCells( selection, $.extend( args, { mode: 'round', internal: true } ), isCollapsed ) );
			cells = cells.concat( this.getTargetCells( selection, $.extend( args, { mode: 'inner', internal:true } ), isCollapsed ) );
			break;
	}

	cells = this.mergeCellProperties( cells );
	return cells;
};

bs.vec.ui.TableAction.prototype.getCol = function( selection, colIndex, args, cells ) {
	return this.getTableLine( 'col', selection, colIndex, args, cells );
};

bs.vec.ui.TableAction.prototype.getRow = function( selection, rowIndex, args, cells ) {
	return this.getTableLine( 'row', selection, rowIndex, args, cells );
};

bs.vec.ui.TableAction.prototype.getTableLine = function( type, selection, index, args, cells ) {
	cells = cells || [];
	args = this.parseArgs( args );

	var result = [], i;
	if ( type === 'row' ) {
		if (
			index < 0 ||
			( selection.hasOwnProperty( 'fromRow' ) && index >= selection.getRowCount() + 1 )
		) {
			return result;
		}
		for( i = selection.startCol; i <= selection.endCol; i++ ) {
			if ( this.getCellIndex( index, i, cells ) !== -1 ) {
				continue;
			}
			result.push( $.extend( {
				row: index,
				col: i
			}, args ) );
		}
	} else {
		if (
			index < 0 ||
			( selection.hasOwnProperty( 'fromCol' ) && index >= selection.getColCount() + 1 )
		) {
			return result;
		}
		for ( i = selection.startRow; i <= selection.endRow; i++ ) {
			if ( this.getCellIndex( i, index, cells ) !== -1 ) {
				continue;
			}
			result.push( $.extend( {
				row: i,
				col: index
			}, args ) );
		}
	}
	return result;
};

bs.vec.ui.TableAction.prototype.mergeCellProperties = function( cells ) {
	var newCells = [];
	for ( var i = 0; i < cells.length; i++ ) {
		var existingIndex = this.getCellIndex( cells[i].row, cells[i].col, newCells );
		if ( existingIndex === -1 ) {
			newCells.push( cells[i] );
		} else {
			newCells[existingIndex] = $.extend( newCells[existingIndex], cells[i] );
		}
	}
	return newCells;
};

bs.vec.ui.TableAction.prototype.getCellIndex = function( row, col, cells ) {
	for ( var i = 0; i < cells.length; i++ ) {
		if ( cells[i].row === row && cells[i].col === col ) {
			return i;
		}
	}
	return -1;
};

bs.vec.ui.TableAction.prototype.getTargetForCell = function( cells, row, col ) {
	for ( var i = 0; i < cells.length; i++ ) {
		if ( cells[i].row === row && cells[i].col === col ) {
			var data = $.extend( cells[i], {} );
			delete( data.row );
			delete( data.col );
			return data;
		}
	}
	return false;
};

bs.vec.ui.TableAction.prototype.parseArgs = function( args ) {
	var res = {}, basePositions = [ 'left', 'right', 'top', 'bottom' ], i;
	if ( basePositions.indexOf( args.mode ) !== -1 ) {
		// Only set current mode, others will be filled from existing props
		res[args.mode] = args.prop;
		return res;
	}
	if ( args.mode === 'all' || args.mode === 'none' || args.mode === 'clear' ) {
		for ( i = 0; i < basePositions.length; i++ ) {
			res[basePositions[i]] = args.prop;
		}
		return res;
	}
};

bs.vec.ui.TableAction.prototype.execBorderStyle = function( targets, surfaceModel, selection, cb, args ) {
	var tableNode = selection.getTableNode( surfaceModel.getDocument() ),
		matrix = tableNode.getMatrix(),
		cellIndex = 0, rowIndex, newRow, cells;

	for( rowIndex = selection.startRow - 1; rowIndex <= selection.endRow + 1; rowIndex++ ) {
		cells = matrix.getRow( rowIndex );
		if ( !cells ) {
			// Row not there - could be out of bounds
			continue;
		}
		for ( cellIndex = 0; cellIndex < cells.length + 1; cellIndex++ ) {
			if ( !cells[cellIndex] ) {
				// Cell out of bounds
				continue;
			}
			var targetIndex = this.getCellIndex( rowIndex, cellIndex, targets );
			if ( targetIndex === -1 ) {
				continue;
			}

			var data = $.extend( targets[targetIndex], {} );
			delete( data.row );
			delete( data.col );
			cells[cellIndex] = cb( cells[cellIndex], data, this );
		}

		newRow = {
			row: ve.dm.TableRowNode.static.applyStylings( matrix.getRowNode( rowIndex ), {} ),
			cells: cells
		};

		this.replace( selection, surfaceModel.getDocument(),'row', rowIndex, newRow );
	}
};

ve.ui.actionFactory.register( bs.vec.ui.TableAction );
