Ext.override( Ext.grid.Panel, {
	constructor: function( cfg ) {
		cfg.listeners = $.extend( {
			staterestore: this.onStateRestore
		}, cfg.listeners || {} );
		this.callParent( arguments );
	},
	onStateRestore: function( grid, state ) {
		// There is a bug in the rendering of columns, if the grid is lockable
		// when the state is restored. We re-show column if the column is visible
		if( grid.isLockable() === false ) {
			return;
		}
		showVisibleColumns( state.columns );

		function showVisibleColumns( columns ) {

			$.each( columns, function( k, column ) {
				if( column.columns ) {
					return showVisibleColumns( column.columns );
				}
				if( column.hidden === false ) {
					var gridColumn = this.getColumnByDataIndex( column.id );
					if( gridColumn !== false ) {
						gridColumn.show();
					}
				}
			}.bind( grid ) );
		};
	},
	getColumnByDataIndex: function( dataIndex ) {
		var gridColumns = this.getColumns();
		for( var index in gridColumns ) {
			if( gridColumns[ index ].dataIndex === dataIndex ) {
				return gridColumns[ index ];
			}
		}
		return false;
	},
	isLockable: function() {
		// Grid is lockable if single column is lockable
		var gridColumns = this.getColumns();
		for( var index in gridColumns ) {
			if( gridColumns[ index ].isLockable() ) {
				return true;
			}
		}
		return false;
	}
} );
