Ext.define( 'BS.ArticleInfo.panel.LinkList', {
	extend: 'Ext.Panel',
	cls: 'bs-articleinfo-flyout-linklist-cnt',
	title: '',
	storeField: '',
	emptyText: '',

	linkList: {},
	listType: 'ul',
	initComponent: function () {
		this.store = new Ext.data.Store( {
			fields: [ this.storeField ],
			data: this.linkList,
			autoLoad: true
		} );

		this.template = new Ext.XTemplate(
			"<ul class='bs-articleinfo-flyout-linklist'>",
			"<tpl for='.'>",
				"<li>{" + this.storeField + "}</li>",
			"</tpl></ul>"
		);

		if( this.listType === 'pills' ) {
			this.template = new Ext.XTemplate(
				"<div class='bs-articleinfo-flyout-linklist'>",
				"<tpl for='.'>",
					"<span class='pill'>{" + this.storeField + "}</span>",
				"</tpl></div>"
			);
		}

		this.items = [
			new Ext.DataView( {
				store: this.store,
				tpl: this.template,
				itemSelector: '.storeitem', //No selection needed here
				emptyText: this.emptyText
			} )
		];

		this.callParent( arguments );
	}
});
