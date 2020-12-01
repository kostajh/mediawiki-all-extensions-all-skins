Ext.define( 'BS.BlueSpiceCategoryManager.Model', {
	extend: 'BS.model.Category',
	fields: [
		{
			name: 'text', type: 'string'
		},
		{
			name: 'link', type: 'string', convert: function ( value, record ) {
				return mw.Title.makeTitle(
					bs.ns.NS_CATEGORY,
					record.get( 'text' )
				).getUrl();
			}
		},
		{
			name: 'categoryName', type: 'string', convert: function ( value, record ) {
				return record.get( 'text' )
			}
		},
		{
			name: 'leaf', type: 'boolean', convert: function ( value, record ) {
				return false;
			}
		},
		{
			name: 'loaded', type: 'boolean', convert: function ( value, record ) {
				return record.get( 'leaf' );
			}
		}
	]
} );