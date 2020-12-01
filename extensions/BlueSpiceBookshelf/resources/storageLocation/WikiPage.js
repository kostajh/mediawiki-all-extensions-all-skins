( function ( mw, bs, $ ) {
	bs.bookshelf.storageLocation.WikiPage = function( cfg ) {};

	OO.initClass( bs.bookshelf.storageLocation.WikiPage );

	bs.bookshelf.storageLocation.WikiPage.prototype.getSaveAction = function( title, content ) {
		return new BS.action.APIEditPage( {
			pageTitle: title,
			pageContent: content
		} );
	};

	bs.bookshelf.storageLocation.WikiPage.prototype.appendText = function( record, content, modifyTag ) {
		var api = new mw.Api(),
			pageId = record.get( 'page_id' ),
			dfd = $.Deferred();

		api.postWithToken( 'csrf', {
			action: 'edit',
			pageid: pageId,
			appendtext: content
		} ).done( function( response, xhr ){
				if( modifyTag ) {
					var copyAction = new BS.action.APICopyPage({
						sourceTitle: mw.config.get( 'wgPageName' ),
						targetTitle: mw.config.get( 'wgPageName' ) //We just want to modify the content
					});
					copyAction.on( 'beforesaveedit', function( action, edit ) {
						edit.content = edit.content.replace(/<(bs:)?bookshelf.*?(src|book)=\"(.*?)\".*?\/>/gi, function( fullmatch, group ) {
							return '';
						});
						edit.content = '<bs:bookshelf src="' + response.edit.title + '" />' + "\n" + edit.content.trim();
					});
					copyAction.execute().done( function() {
						dfd.resolve();
					} );
				}
				else {
					dfd.resolve();
				}
			} ).fail( function() {
				dfd.reject();
			} );

		return dfd.promise();
	};

	bs.bookshelf.storageLocation.WikiPage.prototype.delete = function( record ) {
		var pageId = record.get('page_id'),
			dfd = $.Deferred();

		var api = new mw.Api();
		api.postWithToken( 'csrf', {
			action: 'bs-bookshelf-manage',
			task: 'deleteBook',
			taskData: JSON.stringify( {
				'book_page_id': pageId
			} )
		} )
		.fail( function( protocol, response ) {
			dfd.reject( response.exception );
		} )
		.done( function( response, xhr ){
			dfd.resolve( response.success, response );
		} );

		return dfd.promise();
	};

	bs.bookshelf.storageLocation.WikiPage.prototype.getBookPageText = function( data ) {
		var api = new mw.Api(),
			dfd = $.Deferred();

		api.get( {
			action: 'query',
			pageids: data.page_id,
			prop: 'revisions',
			rvprop: 'content',
			indexpageids : ''
		} ).done(function( resp, jqXHR ){
			var pageId = resp.query.pageids[0];
			dfd.resolve( resp.query.pages[pageId].revisions[0]['*'] );
		} );

		return dfd.promise();
	};

	bs.bookshelf.storageLocation.WikiPage.prototype.isTitleBased = function() {
		return true;
	};
} ) ( mediaWiki, blueSpice, jQuery );
