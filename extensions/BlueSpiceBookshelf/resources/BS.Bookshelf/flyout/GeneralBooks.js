Ext.define( 'BS.Bookshelf.flyout.GeneralBooks', {
	extend: 'BS.flyout.TabbedDataViewBase',
	requires: [ 'BS.Bookshelf.data.BookStore' ],
	gridFeatures: [],
	storeGrouper: null,

	showAddIcon: function() {
		return false;
	},

	makeAddIconHref: function() {
		return mw.util.getUrl( 'Special:BookManager' );
	},

	makeCommonStore: function() {
		return new BS.Bookshelf.data.BookStore( {
			grouper: this.storeGrouper
		} );
	},

	makeDataViewThumbImageModuleName: function() {
		return 'bookshelfimage';
	},

	makeDataViewThumbImageTitletextValue: function( dataset ) {
		return dataset.book_prefixedtext;
	},

	makeDataViewItemLinkUrl: function( dataset ) {
		return mw.util.getUrl( dataset.book_first_chapter_prefixedtext );
	},

	makeDataViewThumbnailCaptionTitle: function( dataset ) {
		if( dataset.book_meta && dataset.book_meta.title ) {
			return dataset.book_meta.title;
		}

		return dataset.book_displaytext;
	},

	makeDataViewItemMetaItems: function( dataset ) {
		if( dataset.book_meta && dataset.book_meta.author1 ) {
			return [
				{ itemHtml: '<span>' + dataset.book_meta.author1 + '</span>' }
			];
		}
		return [];
	},

	makeGridPanelColums: function() {
		return [{
			header: mw.message( 'bs-bookshelfui-manager-title' ).plain(),
			dataIndex: 'book_displaytext',
			flex: 1,
			filter: {
				type: 'string'
			},
			renderer: function( value, metadata, record ) {
				return record.get( 'book_first_chapter_link' );
			}
		}];
	},

	makeGridFeatures: function() {
		return this.gridFeatures;
	}
});
