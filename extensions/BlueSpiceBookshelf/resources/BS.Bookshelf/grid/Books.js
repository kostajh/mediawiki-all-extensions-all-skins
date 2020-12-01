Ext.define('BS.Bookshelf.grid.Books', {
	extend: 'Ext.grid.Panel',

	hideHeaders: true,
	stripeRows: false,
	title: mw.message('bs-bookshelf-book-grid-title').plain(),
	tools : [{
		type: 'gear',
		tooltip: mw.message( 'bs-bookshelf-specialpage-allpages-ns-book-text' ).plain(),
		cls: 'bs-bookshelf-edit-book-tool',
		handler: function(event, toolEl, panel){
			window.location.href = mw.util.getUrl(
				'Special:BookshelfBookManager'
			);
		},
		scope: this
	}],
	columns: [{
		dataIndex: 'page_title',
		flex: 1,
		renderer: function( val, meta, record ) {
			return mw.html.element(
				'a',
				{
					'title': val,
					'href': mw.util.getUrl( record.get('book_first_chapter_prefixedtext') ),
					'data-bs-title': record.get('book_prefixedtext')
				},
				val.replace( /_/g, ' ' )
			);
		}
	}],

	stateful: true,
	stateEvents: ['collapse', 'expand'],

	currentData: null,

	initComponent: function() {
		var me = this;
		this.store = new Ext.data.JsonStore({
			fields: [ 'page_id', 'page_title', 'page_namespace',
				'book_prefixedtext', 'book_first_chapter_prefixedtext' ],
			data: []
		});

		var api = new mw.Api();
		api.get({
			action: 'query',
			list: 'bsbookshelf'
		}).done(function(result, xhr){
			if( result.query && result.query.bsbookshelf ){
				var data = [];
				for( var i = 0; i < result.query.bsbookshelf.length; i++ ) {
					var dataset = result.query.bsbookshelf[i];
					//If we are on a page that belongs to a book we do not
					//want this book to show up in the list
					if( dataset.book_prefixedtext === me.currentData.bsSrc ) {
						continue;
					}

					data.push( dataset );
				}

				me.fireEvent('ready');
				me.store.loadData( data );
			}
		});

		this.callParent( arguments );
	},

	getState: function() {
		return {
			// this.collapsed may be false of 'top'
			collapsed: this.collapsed === false ? false : true
		};
	}
});
