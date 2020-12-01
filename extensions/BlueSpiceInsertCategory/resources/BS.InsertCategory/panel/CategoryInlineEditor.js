Ext.define( 'BS.InsertCategory.panel.CategoryInlineEditor', {
	extend: 'Ext.panel.Panel',
	requires: [ 'BS.form.field.CategoryTag' ],
	cls: 'bs-insertcategory-category-inline-editor',
	header: false,
	layout: 'fit',

	pageId: -1,
	allCategories: [],

	initComponent: function() {
		me = this;
		this.btnSave = new Ext.button.Button( {
			text: mw.message( 'bs-extjs-save' ).plain(),
			disabled: true,
			cls: 'editor-button'
		} );
		this.btnSave.on( 'click', this.btnSaveClick, this );

		this.btnCancel = new Ext.button.Button( {
			text: mw.message( 'bs-extjs-cancel' ).plain(),
			cls: 'editor-button'
		} );
		this.btnCancel.on( 'click', this.btnCancelClick, this );

		this.cbCategories = new BS.form.field.CategoryTag({
			id: this.getId() + '-categories',
			showTreeTrigger: true,
			labelSeparator: '',
			labelStyle: 'width:25px',
			anchor: '100%',
			fieldLabel: '<a class="bs-category-add-category bs-insertcategory-category-inline-editor-icon" href="#"><i></i></a>'
		});
		var triggers = this.cbCategories.getTriggers();
		triggers.cancel = new Ext.form.trigger.Trigger({
			cls : 'bs-form-cancel-trigger bs-form-trigger',
			handler: function() {
				me.btnCancel.fireEvent( 'click' );
			}
		});
		triggers.save = new Ext.form.trigger.Trigger({
			cls : 'bs-form-save-trigger bs-form-trigger',
			handler: function() {
				me.btnSave.fireEvent( 'click' );
			}
		});
		triggers.implicitCategories = new Ext.form.trigger.Trigger({
			cls : 'bs-form-implicitcategories-trigger bs-form-trigger',
			hidden: true,
			weight: -1,
			handler: function() {
				bs.util.alert( 'bs-category-inline-editor-implicit',
						{
							text: mw.msg( 'bs-insertcategory-category-editor-implicit-categories-help' ) + '<br/> ' + me.renderCategoryLinklist( me.implicitCategories ),
							titleMsg: 'bs-insertcategory-category-editor-implicit-categories-title',
						}, {
					scope: this
				}
				);
			}
		});
		this.triggerImplicitCategories = triggers.implicitCategories;
		this.cbCategories.setTriggers( triggers );
		this.cbCategories.on( 'change', this.cbCategoriesChange, this );

		this.pnlExplcitCategoriesEditor = new Ext.form.Panel( {
			cls: 'category-editor-form',
			items: [
				this.cbCategories
			],
			hidden: true
		} );

		this.pnlExplcitCategories = new Ext.panel.Panel( {
			items: [
				this.pnlExplcitCategoriesEditor
			]
		} );

		this.items = [
			this.pnlExplcitCategories
		];

		this.loadCategories();

		this.callParent( arguments );
	},

	loadCategories: function() {
		var me = this;
		bs.api.tasks.exec( 'wikipage', 'getExplicitCategories', {
			page_id: this.pageId
		} )
		.done( function( result ) {
			me.cbCategories.suspendEvent( 'change' );
			me.cbCategories.setValue( result.payload );
			me.cbCategories.resumeEvent( 'change' );

			me.showImplicitCategories( result.payload );
		});
	},

	showImplicitCategories: function( explicitCategories ) {
		var implicitCategories = [];
		for( var i = 0; i < this.allCategories.length; i++ ) {
			var currentCategory = this.allCategories[i];
			if( explicitCategories.indexOf( currentCategory ) === -1 ) {
				implicitCategories.push( currentCategory );
			}
		}

		if( implicitCategories.length === 0 ) {
			return;
		}

		me.implicitCategories = implicitCategories;
		this.triggerImplicitCategories.show();
	},

	renderCategoryLinklist: function( categories ) {
		var links = [];
		for( var i = 0; i < categories.length; i++ ) {
			var categoryName = categories[i];
			var title = mw.Title.makeTitle( bs.ns.NS_CATEGORY, categoryName );
			var link = mw.html.element(
				'a',
				{
					href: title.getUrl(),
					title: categoryName,
					target: '_blank',
					class: 'pill'
				},
				categoryName
			);

			links.push( link );
		}

		var html = links.join( ', ' );

		return html;
	},

	cbCategoriesChange: function() {
		this.btnSave.enable();
		this.btnSave.setHidden( false );
	},

	btnSaveClick:function() {
		var me = this;
		var categories = this.cbCategories.getValue();
		this.setLoading( true );

		bs.api.tasks.exec( 'wikipage', 'setCategories', {
			page_id: this.pageId,
			categories: categories
		} )
		.done( function( result ) {
			me.setLoading( false );
			location.reload();
		})
		.fail( function() {
			me.setLoading( false );
		});
	},

	btnCancelClick: function() {
		this.hide();
		$( '#ca-insert_category-titlesection' ).show();
		$( '.bs-category-container-categories' ).show();
	},

	setLoading: function( state ) {
		if( this.parentFlyout && this.parentFlyout.setLoading ) {
			this.parentFlyout.setLoading( state );
		}
		else {
			this.callParent( arguments );
		}
	},

	switchToEditor: function() {
		this.pnlExplcitCategoriesEditor.show();
	}
});