( function( mw, bs, $ ) {
	Ext.define( 'BS.Bookshelf.dialog.NewPrompt', {
		extend: 'MWExt.Dialog',
		requires:['BS.form.SimpleSelectBox'],
		title: mw.message('bs-bookshelfui-new-book-title').plain(),
		closeAction: 'destroy',
		storageLocationRegistry: bs.bookshelf.storageLocationRegistry,

		makeItems: function() {
			var aTypes = [];
			for ( var typeKey in this.storageLocationRegistry.registry ) {
				if ( !this.storageLocationRegistry.registry.hasOwnProperty( typeKey ) ) {
					continue;
				}
				aTypes.push({
					name: this.storageLocationRegistry.lookup( typeKey ).getLabel(),
					value: typeKey
				});
			}

			this.tfBookTitle = new Ext.form.field.Text({
				id: this.makeId( 'input-booktitle' ),
				fieldLabel: mw.message('bs-bookshelfui-new-book-text').plain()
			});
			this.cbBookType = new BS.form.SimpleSelectBox({
				fieldLabel: mw.message('bs-bookshelfui-book-type').plain(),
				bsData: aTypes,
				value: aTypes[0].value
			});

			return [
				this.tfBookTitle,
				this.cbBookType
			];
		},

		onBtnOKClick: function() {
			var bookTitle = this.tfBookTitle.getValue(),
				bookType = this.cbBookType.getValue();

			window.location.href =  this.storageLocationRegistry.lookup( bookType ).getEditUrlFromTitle( bookTitle );
		}
	});
} )( mediaWiki, blueSpice, jQuery );
