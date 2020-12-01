Ext.define( 'BS.Bookshelf.panel.BookManager', {
	extend: 'BS.CRUDGridPanel',
	requires: [
		'BS.Bookshelf.dialog.CopyPrompt', 'BS.Bookshelf.dialog.NewPrompt',
		'BS.Bookshelf.data.BookStore'
	],

	initComponent: function() {
		this.makeCopyButton();
		this.colMainConf.columns = this.makeColumns();

		this.groupingFeature = new Ext.grid.feature.Grouping({
			enableGroupingMenu:false,
			enableNoGroups: false,
			groupHeaderTpl: [
				'{name:this.formatName(values.rows.length)}', {
					formatName: function( name, count ) {
						var groupName = name,
							location = bs.bookshelf.storageLocationRegistry.lookup( name ),
							groupInfo = mw.message("bs-bookshelfui-grouping-template-books", count).parse();

						if ( location ) {
							groupName = location.getLabel();
						}

						return groupName + ' ' + groupInfo;
					}
				}
			]
		});

		this.gpMainConf.features = [
			this.groupingFeature
		];

		var storeFields = [ 'page_id', 'page_title', 'page_namespace',
			'book_first_chapter_prefixedtext', 'book_prefixedtext',
			'book_type', 'book_displaytext', 'book_meta' ];

		$( document ).trigger('BSBookshelfUIManagerPanelInit', [ this, this.colMainConf, storeFields ]);

		// this.colMainConf.columns.defaults.sortable=false would be better but
		// due to poor design of BS.CRUDGridPanel this is not possible
		for( var i = 0; i < this.colMainConf.columns.length; i++ ) {
			if( this.colMainConf.columns[i].sortable !== true ) {
				this.colMainConf.columns[i].sortable = false;
			}
		}

		this.strMain = new BS.Bookshelf.data.BookStore( {
			fields: storeFields,
			groupField: 'book_type',
			sortInfo: {
				field: 'page_title',
				direction: 'ASC'
			}
		} );

		this.callParent(arguments);
	},

	onBtnAddClick: function( oButton, oEvent ) {
		var dlgNew = new BS.Bookshelf.dialog.NewPrompt( {
			id: this.makeId( 'dialog-new' )
		} );
		dlgNew.show();
		this.callParent(arguments);
	},

	onActionCopyClick:function(grid, rowIndex, colIndex) {
		this.grdMain.getSelectionModel().select(
			this.grdMain.getStore().getAt( rowIndex )
		);
		this.onBtnCopyClick( this.btnCopy, {} );
	},

	dlgCopy: null,
	onBtnCopyClick: function(  oButton, oEvent  ) {
		if( !this.dlgCopy ) {
			this.dlgCopy = new BS.Bookshelf.dialog.CopyPrompt();
			this.dlgCopy.on( 'ok', function() {
				this.grdMain.getStore().reload();
			}, this );
		}
		var record = this.getSingleSelection();

		this.dlgCopy.setData( record.getData() );
		this.dlgCopy.show();
	},

	onGrdMainSelectionChange: function( sender, records, opts ) {
		this.callParent( arguments );
		this.btnCopy.disable();
		if( records && records.length > 0 && records.length < 2 ) {
			this.btnCopy.enable();
		}
	},

	onBtnEditClick: function(  oButton, oEvent  ) {
		var record = this.getSingleSelection(),
			type = record.get( 'book_type' ),
			storageLocation = bs.bookshelf.storageLocationRegistry.lookup( type );

		window.location.href = storageLocation.getEditUrlFromTitle( record.get('book_prefixedtext'), {
			returnto: new mw.Title( 'BookshelfBookManager', bs.ns.NS_SPECIAL ).getPrefixedDb()
		} );
		this.callParent(arguments);
	},

	onBtnRemoveClick: function( oButton, oEvent ) {
		bs.util.confirm(
			'bs-bookshelfui-confirm-delete',
			{
				titleMsg: 'bs-bookshelfui-delete-book-title',
				textMsg: 'bs-bookshelfui-delete-book-text'
			},
			{
				ok: this.doDeleteBook,
				scope: this
			}
		);
		this.callParent(arguments);
	},

	progressMsg: null,
	doDeleteBook: function() {
		var record = this.getSingleSelection(),
			me = this,
			bookType = record.get( 'book_type' ),
			storageLocation = bs.bookshelf.storageLocationRegistry.lookup( bookType );

		if ( !storageLocation ) {
			// Probably should notify user, but this is a really edge case
			return;
		}

		this.progressMsg = Ext.Msg.wait(
			mw.message('bs-bookshelfui-manager-deletingprogress-text').plain(),
			mw.message('bs-bookshelfui-manager-deletingprogress-title').plain()
		);

		storageLocation.delete( record )
			.done( function( success, data ) {
				if ( success ) {
					mw.notify(
						mw.msg( 'bs-bookshelfui-manager-deletionsuccess-text' ),
						{ title: mw.msg( 'bs-bookshelfui-manager-deletionsuccess-title' ) }
					);
				}
				else {
					bs.util.alert(
						'bs-bui-editor-delete-error',
						{
							titleMsg: 'bs-bookshelfui-manager-deletionfailure-title',
							text: mw.message(
								'bs-bookshelfui-manager-deletionfailure-text',
								data.message || ''
							).parse()
						}
					);
				}
				me.progressMsg.hide();
				me.strMain.reload();
			} )
			.fail( function( error ) {
				me.progressMsg.hide();
				bs.util.alert(
					'bs-bui-editor-delete-error',
					{
						text: error
					}
				);
			} );
	},

	makeCopyButton: function () {
		this.btnCopy = Ext.create( 'Ext.Button', {
			id: this.getId()+'-btn-copy',
			icon: mw.config.get( 'wgScriptPath') + '/extensions/BlueSpiceBookshelf/resources/images/bs-btn_bookclone.png',
			iconCls: 'btn'+this.tbarHeight,
			tooltip: mw.message('bs-bookshelfui-extjs-tooltip-copy').plain(),
			height: 50,
			width: 52,
			disabled: true
		});
		this.btnCopy.on( 'click', this.onBtnCopyClick, this );

		this.colMainConf.actions.push({
			iconCls: 'bs-extjs-actioncolumn-icon icon-copy contructive',
			glyph: true,
			tooltip: mw.message('bs-bookshelfui-extjs-tooltip-copy').plain(),
			handler: this.onActionCopyClick,
			scope: this
		});
	},

	makeTbarItems: function() {
		this.callParent( arguments );
		return [
			this.btnAdd,
			this.btnCopy,
			this.btnEdit,
			this.btnRemove
		];
	},

	makeColumns: function() {
		return [
			{
				dataIndex: 'book_displaytext',
				header: mw.message( 'bs-bookshelfui-manager-title' ).plain(),
				sortable: true,
				groupable: false,
				filter: {
					type: 'string'
				},
				renderer: function( value, metaData, record, rowIndex, colIndex, store ) {
					var storageLocation = bs.bookshelf.storageLocationRegistry.lookup( record.get( 'book_type' ) ),
						url = storageLocation ? storageLocation.getEditUrlFromTitle( record.get( 'page_title' ), {
							returnto: new mw.Title( 'BookshelfBookManager', bs.ns.NS_SPECIAL ).getPrefixedDb()
						} ) : '#';

					return mw.html.element(
						'a',
						{
							'href': url,
							'data-bs-title': record.get( 'book_prefixedtext' )
						},
						value
					);
				}
			}
		];
	}
});
