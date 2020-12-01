Ext.define( 'BS.Calumma.dialog.NewPage', {
	extend: 'MWExt.Dialog',
	requires: [ 'BS.form.field.TitleCombo' ],
	title: mw.message( 'bs-action-new-page-title' ).plain(),
	closeAction: 'destroy',

	namespace: '',
	currentInputValue: '',

	makeItems: function() {
		var me = this;
		var namespace = '';

		if ( this.namespace !== '' ) {
			namespace = this.namespace;
		}

		this.cbPageName = Ext.create( 'BS.form.field.TitleCombo', {
			id: this.getId() + '-pagename',
			fieldLabel: mw.message('bs-action-new-page-text').plain(),
			enableKeyEvents: true,
			value: namespace + ':',
			// Internet explorer has trouble asserting value is on the allowed list
			forceSelection: Ext.isIE ? false : true,
			listeners: {
				focusleave: function ( comboBox ) {
					me.currentInputValue = document.getElementById( me.cbPageName.id + '-inputEl' ).value;
					me.cbPageName.select( me.currentInputValue );
				}
			}
		} );
		this.cbPageName.on( 'keypress', this.onPageNameKeypress, this );
		return [
			this.cbPageName
		];
	},

	onPageNameKeypress: function ( combo, e, eOpts ) {
		this.currentInputValue = document.getElementById( this.cbPageName.id + '-inputEl' ).value;
		if ( e.charCode === 13 ) {
			this.cbPageName.select( this.currentInputValue );
			this.onBtnOKClick();
		}
		if ( e.charCode === 58 ) {
			this.namespace = this.currentInputValue;
		}
	},

	getData: function () {
		return {
			name: this.currentInputValue,
			namespace: this.namespace
		}
	},

	show: function() {
		this.callParent(arguments);
		this.cbPageName.focus();
	}
} );
