Ext.define( 'BS.BlueSpiceExtendedFilelist.grid.FileRepo', {
	extend: 'BS.grid.FileRepo',

	initComponent: function() {
		this.callParent( arguments );
	},

	makePagingToolbar: function( items ) {
		this.cbPageSizeTop = this.makePageSizeCombo();

		items.push(new Ext.PagingToolbar({
			dock: 'top',
			store: this.store,
			displayInfo: true,
			items: [
				this.cbPageSizeTop
			]
		}));
		return this.callParent( arguments );
	},

	/**
	 * TODO: promote to BS.grid.FileRepo
	 * @returns {Ext.form.ComboBox}
	 */
	makePageSizeCombo: function() {
		var combo = new Ext.form.ComboBox({
			fieldLabel: mw.message ( 'bs-filerepo-pagesize' ).plain(),
			labelAlign: 'right',
			autoSelect: true,
			forceSelection: true,
			triggerAction: 'all',
			mode: 'local',
			store: new Ext.data.SimpleStore({
				fields: ['text', 'value'],
				data: [
					['20', 20],
					['50', 50],
					['100', 100],
					['200', 200],
					['500', 500]
				]
			}),
			value: this.pageSize,
			labelWidth: 120,
			flex: 2,
			valueField: 'value',
			displayField: 'text'
		});

		combo.on ('select', this.onSelectPageSize, this);

		return combo;
	},

	onSelectPageSize: function( sender, event ) {
		var pageSize = sender.getValue();

		//Make sure both toolbars have the same page selection
		this.cbPageSize.suspendEvents( false );
		this.cbPageSize.setValue( pageSize, false );
		this.cbPageSize.resumeEvents();

		this.cbPageSizeTop.suspendEvents( false );
		this.cbPageSizeTop.setValue( pageSize, false );
		this.cbPageSizeTop.resumeEvents();

		this.callParent( arguments );
	},

	makeColumns: function() {
		this.callParent( arguments );
		//this.colPageCategories.setHidden( flase ) triggers a re-render which
		//sadly forgets the filters plugin. This is a bit hacky but works
		this.colPageCategories.hidden = false;
		return {
			items: [
				this.colFileThumb,
				this.colFilename,
				this.colFilesize,
				this.colFileUserText,
				this.colPageCategories,
				this.colFileExtension,
				this.colFileMimetype,
				this.colFileWidth,
				this.colFileHeight,
				this.colFileTimestamp,
				this.colFileMediaType,
				this.colFileDescription
			],
			defaults: {
				flex: 1
			}
		};
	}
});