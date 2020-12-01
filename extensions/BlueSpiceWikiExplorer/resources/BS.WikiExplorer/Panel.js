Ext.define('BS.WikiExplorer.Panel', {
	extend: 'Ext.grid.Panel',
	require: [
		'Ext.grid.*',
		'Ext.data.*',
		'Ext.tip.QuickTipManager',
		'BS.WikiExplorer.data.ListEntry',
		'Ext.grid.filters.Filters'
	],
	initComponent: function() {
		this.callParent(arguments);
	}
});