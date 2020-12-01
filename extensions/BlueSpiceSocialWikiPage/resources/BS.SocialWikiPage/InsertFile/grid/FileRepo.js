Ext.define( 'BS.SocialWikiPage.InsertFile.grid.FileRepo', {
	extend: 'BS.grid.FileRepo',
	pageSize : 5,
	initComponent: function() {
		this.callParent( arguments );
	},

	makePagingToolbar: function( items ) {
		items.push( new Ext.PagingToolbar({
				dock: 'bottom',
				store: this.store,
				displayInfo: true
			})
		);
	},

	makeColumns: function() {
		this.callParent( arguments );

		return {
			items: [
				this.colFileThumb,
				this.colFilename,
				this.colFileTimestamp,
				this.colFileMediaType,
				this.colFileDescription
			],
			defaults: {
				flex: 1
			}
		};
	},

	getData: function() {
		if( this.getSelection().length < 1 ) {
			return {};
		}
		return this.getSelection()[0].getData();
	}
});