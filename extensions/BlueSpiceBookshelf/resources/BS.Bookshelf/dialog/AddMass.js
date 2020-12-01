Ext.define('BS.Bookshelf.dialog.AddMass', {
	extend: 'MWExt.Dialog',
	requires: [ 'BS.form.field.TitleCombo', 'BS.form.SimpleSelectBox', 'BS.form.CategoryCombo' ],
	width: 450,
	title: mw.message( 'bs-bookshelfui-dlg-addmass-title' ).plain(),

	abortClosing: false,

	makeItems: function() {
		this.cbType = new BS.form.SimpleSelectBox({
			itemId: 'type-selection',
			bsData: [
				{ 'type': 'category', 'text': mw.message( 'bs-bookshelfui-type-category' ).plain() },
				{ 'type': 'subpages', 'text': mw.message( 'bs-bookshelfui-type-subpages' ).plain() },
				{ 'type': 'pagecollection', 'text': mw.message( 'bs-bookshelfui-type-pagecollection' ).plain() }
			],
			fieldLabel: mw.message( 'bs-bookshelfui-dlg-type-label' ).plain(),
			displayField: "text",
			valueField: "type",
			labelAlign: "right"
		});
		this.cbType.addListener('select', this.onCbType, this);

		this.makeTitleCombo();
		this.makeCategoryCombo();
		this.makePageCollectionCombo();

		this.sources = {
			subpages: this.cbTitle,
			category: this.cbCategory,
			pagecollection: this.cbPageCollection
		};

		//Allow other extensions to add sources to mass add dialog
		mw.hook( 'ext.bookshelfui.addmass.create' ).fire( this );

		var items = [
			this.cbType
		];

		for( var sourceKey in this.sources ) {
			items.push( this.sources[sourceKey] );
		}

		for(var i = 0; i < items.length; i++) {
			items[i].addListener( 'select', function ( oSender, record ) {
				this.btnOK.enable();
			}, this );
		}

		return items;
	},
	//Register foreign source over hook handler
	registerSource: function( type, messageKey, element ) {
		//Add type to source picker
		this.cbType.getStore().add( {
			'type': type,
			'text': mw.message( messageKey ).plain()
		} );
		//Add type to sources registry
		this.sources[ type ] = element;
	},

	onCbType: function( oSender, record ) {
		this.selectedType = record.data.type;
		this.hideAll();
		if( this.sources[ this.selectedType ] ) {
			this.sources[ this.selectedType ].setVisible( true );
		}
		this.btnOK.enable();
	},

	getData: function( oSender, oEvent ) {
		var data = {};
		data.selectedType = this.selectedType;
		if( this.selectedType === 'subpages' ) {
			data.root = this.cbTitle.getValue().get( 'prefixedText' );
		} else {
			if( this.sources[ this.selectedType ] ) {
				data.root = this.sources[ this.selectedType ].getValue();
			}
		}
		mw.hook( 'ext.bookshelfui.addmass.getdata' ).fire( this, data );

		return data;
	},

	onBtnOKClick: function() {
		if( !this.selectedType ) {
			bs.util.alert(
				'bs-bui-wikipagenodedialog-alert-empty',
				{
					textMsg: 'bs-bookshelfui-empty-selection'
				}
			);
			return;
		}

		if( this.selectedType === 'subpages' ) {
			var record = this.cbTitle.getValue();
			if( !record || record.get( 'type' ) !== 'wikipage'
					|| record.get( 'page_title' ).trim() === '' ) {
				bs.util.alert(
					'bs-bui-wikipagenodedialog-alert-empty',
					{
						textMsg: 'bs-bookshelfui-empty-selection'
					}
				);
				return;
			}
		} else {
			var value = this.sources[ this.selectedType ].getValue();
			if( !value ) {
				bs.util.alert(
					'bs-bui-wikipagenodedialog-alert-empty',
					{
						textMsg: 'bs-bookshelfui-empty-selection'
					}
				);
				return;
			}
		}

		mw.hook( 'ext.bookshelfui.addmass.close' ).fire( this );

		if( this.abortClose ) {
			return;
		}

		this.callParent( arguments );
	},

	makeTitleCombo: function() {
		this.cbTitle = new BS.form.field.TitleCombo({
			fieldLabel: mw.message('bs-bookshelfui-dlg-choosewikipage-cbxArticleLabel').plain(),
			labelAlign: 'right',
			hidden: true
		});
	},

	makeCategoryCombo: function() {
		this.cbCategory = new BS.form.CategoryCombo({
			fieldLabel: mw.message('bs-bookshelfui-dlg-choosecategory-label').plain(),
			labelAlign: 'right',
			hidden: true
		});
	},

	makePageCollectionCombo: function() {
		Ext.define( 'PageCollection', {
			extend: 'Ext.data.Model',
			fields: [
				{ name: 'pc_title', type: 'string' }
			]
		} );
		var store = new BS.store.BSApi({
			apiAction: 'bs-bookshelf-page-collection-store',
			proxy: {
				extraParams: {
					limit: 9999
				}
			},
			model: 'PageCollection',
			autoLoad: true
		});

		this.cbPageCollection = Ext.create( 'Ext.form.field.ComboBox', {
			displayField: 'pc_title',
			valueField: 'pc_title',
			allowBlank: false,
			forceSelection: true,
			store: store,
			hidden: true,
			fieldLabel: mw.message('bs-bookshelfui-dlg-choosepc-label').plain(),
			labelAlign: 'right'
		});
	},

	hideAll: function() {
		$.each( this.sources, function( key, el ){
			el.setVisible( false );
		} );
	}
});

