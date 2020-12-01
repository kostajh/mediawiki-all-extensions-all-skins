(function( mw, $, bs ) {
	bs.util.registerNamespace( 'bs.bookshelf' );

	bs.bookshelf.BookTreeExport = function( tree, options ) {
		this.tree = tree;
		this.options = options;

		//Allow exportmodules to add their menu item
		var exportMenuItems = [];
		$( document ).trigger('BSUniversalExportMenuItems', [this, exportMenuItems] );
		if ( exportMenuItems.length > 0 ) {
			var menu = new Ext.menu.Menu( {
				ignoreParentClicks: true,
				items: exportMenuItems
			} );
			this.btnExportSelection = Ext.create( 'Ext.Button', {
				id: this.options.id + '-btn-export',
				text: mw.message('bs-bookshelfui-export-selection').plain(),
				menu: menu
			});
			menu.on( 'click', this.onBookExport, this );
		}
	};

	bs.bookshelf.BookTreeExport.prototype.getExportButton = function() {
		return this.btnExportSelection || null;
	};

	bs.bookshelf.BookTreeExport.prototype.onBookExport = function( menu, item, e, eOpts ){
		var selectedArticles = BS.Bookshelf.TreeHelper.serializeTree(
			this.tree, { onlyChecked: true }
		);

		if( selectedArticles === '[]' ) { //Nothing was selected
			bs.util.alert(
				'bs-bui-editor-alert-nothingtoexport',
				{
					textMsg: 'bs-bookshelfui-nothingtoexport-text'
				}
			);
			return;
		}

		if ( this.options.hasOwnProperty( 'setLoadCallback' ) ) {
			this.options.setLoadCallback( mw.message('bs-bookshelfui-export-book-text').plain() );
		}
		var me = this;
		var targetUrl = mw.util.getUrl(
			'Special:UniversalExport/' + this.tree.getRootNode().get( 'articleTitle' )
		);
		var params = {
			'ue[module]': item.exportModule,
			'ue[articles]': selectedArticles,
			'book_type': this.options.bookType
		};

		if ( this.options.hasOwnProperty( 'storageLocation') && !this.options.storageLocation.isTitleBased() ) {
			params.content = '-';
		}

		var encParams = $.param( params );
		var fileExtension = item.fileExtension;

		//HINT: https://nehalist.io/downloading-files-from-post-requests/
		var request = new XMLHttpRequest();
		request.open( 'POST', targetUrl, true );
		request.setRequestHeader(
			'Content-Type',
			'application/x-www-form-urlencoded; charset=UTF-8'
		);
		request.responseType = 'blob';
		request.onload = function() {
			if( request.status === 200 ) {
				// Try to find out the filename from the content disposition `filename` value
				var disposition = request.getResponseHeader( 'content-disposition' );
				var matches = /"([^"]*)"/.exec( disposition );
				var filename = matches !== null && matches[1]
					? matches[1]
					: 'file.' + fileExtension;

				//Even though the HTTP headers look fine in the network panel,
				//`XMLHttpRequest::getResponseHeader` double UTF8 encodes the value.
				//This is something like "decode_utf8".
				filename = decodeURIComponent( escape( filename ) );

				var blob = request.response;

				if (navigator.msSaveOrOpenBlob) {
					navigator.msSaveOrOpenBlob( blob, filename );
				} else {
					var objectUrl = window.URL.createObjectURL( blob );

					//HINT: Alternatively `window.open( objectUrl );` would open the file in a new tab.
					//But then again we'd have no filename and the user would need to save the file
					//manually.
					var link = document.createElement( 'a' );
					link.href = objectUrl;
					link.download = filename;

					document.body.appendChild( link );
					link.click();
					document.body.removeChild( link );
					window.URL.revokeObjectURL( objectUrl );
				}
			}
			if ( me.options.hasOwnProperty( 'setLoadCallback' ) ) {
				me.options.setLoadCallback( false );
			}
		};

		request.send( encParams );
	};
})( mediaWiki, jQuery, blueSpice );
