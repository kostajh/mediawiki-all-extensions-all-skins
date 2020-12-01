Ext.define('BS.Bookshelf.dialog.WikiPageNode', {
	extend: 'MWExt.Dialog',
	requires: [ 'BS.form.field.TitleCombo' ],
	width: 450,
	title: mw.message('bs-bookshelfui-dlg-choosewikipage-title').plain(),

	//Custom settings
	defaultNamespaceId: 0,
	makeItems: function() {
		this.cbTitle = new BS.form.field.TitleCombo({
			id: this.makeId( 'input-pagetitle' ),
			fieldLabel: mw.message('bs-bookshelfui-dlg-choosewikipage-cbxArticleLabel').plain(),
			labelAlign: 'right'
		});
		this.cbTitle.addListener( 'collapse', this.onCbTitleCollapse, this );

		this.tfDisplayName = new Ext.form.TextField({
			fieldLabel: mw.message('bs-bookshelfui-dlg-choosewikipage-tfDisplayName').plain(),
			labelAlign: 'right'
		});

		return [
			this.cbTitle,
			this.tfDisplayName
		];
	},

	getData: function(oSender, oEvent) {
		var data = {
			bookshelf: {
				type: 'wikipage'
			}
		};
		var record = this.cbTitle.getValue();
		var display = this.tfDisplayName.getValue();

		if( record ) {
			//Legacy stuff
			data.id = record.get( 'page_id' );
			data.text = record.get( 'page_title' );
			data.prefixedText = record.get( 'prefixedText' );

			//New NodeModel stuff
			data.bookshelf.page_id = record.get( 'page_id' );
			data.bookshelf.page_namespace = record.get( 'page_namespace' );
			data.bookshelf.page_title = record.get( 'page_title' );

			//Mapping to TreePanel config
			data.articleTitle = data.prefixedText;
			data.articleId = data.id;
		}

		if( display === '' ) {
			display = data.articleTitle;
		}

		data.text = display;
		data.articleDisplayTitle = display;

		return data;
	},

	onCbTitleCollapse: function( field, eOpts ) {
		var records = field.getPicker().getSelectionModel().getSelection();
		if ( records.length < 1 ) {
			return;
		}
		var record = records[0];
		if( record.get('type') === 'wikipage' ) {
			var alias = record.get( 'page_title' ); //No namespace prefix
			alias = alias.split('/').reverse()[0]; //basename()
			this.tfDisplayName.setValue( alias );
		}
	},

	onBtnOKClick: function() {
		var record = this.cbTitle.getValue();
		if( !record ) {
			record = this.cbTitle.getStore().getAt(0);
			this.cbTitle.select( record );
		}

		if( !record || record.get( 'page_title' ).trim() === '' ) {
			bs.util.alert(
				'bs-bui-wikipagenodedialog-alert-empty',
				{
					textMsg: 'bs-bookshelfui-empty-selection'
				}
			);
			return;
		}

		this.callParent( arguments );
		this.cbTitle.setValue( '' );
		this.tfDisplayName.setValue( '' );
	}
});
