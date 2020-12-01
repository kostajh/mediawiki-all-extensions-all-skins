( function( mw, $, bs, d ){

	function _makeHTMLTableProvider( element ) {
		return {
			getHTMLTable: function() {
				var dfd = $.Deferred();
				dfd.resolve( '<table>' + $(element).html() + '</table>' );
				return dfd;
			}
		};
	}

	$( d ).on( 'contextmenu', mw.config.get( 'bsgExportTablesMenuTargetSelector' ), function( e ) {
		if( e.ctrlKey ) {
			return true;
		}
		var me = this;

		// remove style attribut 'width' for th and td
		var cells = $(me).find( 'th, td' );
		for ( var i = 0; i < cells.length; i++ ) {
			var style = cells[ i ].style;
			if ( style.width ) {
				style.width = '';

			}
		};

		mw.loader.using( 'ext.bluespice.extjs' ).done( function() {
			Ext.require( 'BS.ExportTables.menu.TableExport', function() {
				var menu = new BS.ExportTables.menu.TableExport({
					title: mw.message( 'bs-exporttables-menu' ).plain(),
					htmlTableProvider: _makeHTMLTableProvider( me )
				});
				menu.showAt(e.pageX, e.pageY);
			}, this);
		});
		e.preventDefault();

		return false;
	});

	$( d ).on( 'mouseover', mw.config.get( 'bsgExportTablesMenuTargetSelector' ), function( e ) {
		$(this).addClass( 'bs-et-highlight' );
	});
	$( d ).on( 'mouseout', mw.config.get( 'bsgExportTablesMenuTargetSelector' ), function( e ) {
		$(this).removeClass( 'bs-et-highlight' );
	});

} )( mediaWiki, jQuery, blueSpice, document );