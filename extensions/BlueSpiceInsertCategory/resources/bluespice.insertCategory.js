$(document).on('click', '#ca-insert_category', function(e) {
	e.preventDefault();
	var me = $(this).find('a');
	mw.loader.using( 'ext.bluespice.extjs' ).done(function(){
		Ext.require('BS.InsertCategory.Dialog', function(){
			BS.InsertCategory.Dialog.clearListeners();
			BS.InsertCategory.Dialog.on( 'ok', function ( sender, data ) {
				if ( BS.InsertCategory.Dialog.isDirty ) {
					BsInsertCategoryViewHelper.setCategories( data );
					return false;
				}
			} );
			BS.InsertCategory.Dialog.setData(
				BsInsertCategoryViewHelper.getCategories()
			);
			BS.InsertCategory.Dialog.show( me );
		});
	});
	return false;
});

$(document).on('click', 'a.bs-category-add-category', function(e) {
	e.preventDefault();
	var me = $(this).find('a');
	me.categoryEditor = null;
	mw.loader.using( 'ext.bluespice.extjs' ).done(function(){

		Ext.require( 'BS.InsertCategory.panel.CategoryInlineEditor', function(){
			//Prevent a second toggle when category field is already visible.
			//Element id is set in CategoryInlineEditor
			if ( $( '.bs-insertcategory-category-inline-editor-icon' ).is( ':visible' ) ) {
				return;
			}

			$( '.bs-category-add-category' ).hide();
			$( '.bs-category-container-categories' ).hide();

			if ( me.categoryEditor == null ) {
				me.categoryEditor = new BS.InsertCategory.panel.CategoryInlineEditor({
					id: 'bs-insertcategory-inlineeditor',
					pageId: mw.config.get( 'wgArticleId' ),
					allCategories: mw.config.get( 'wgCategories' ),
					renderTo: 'bs-category-container-add-category'
				} );
			} else {
				me.categoryEditor.show();
				$( '.bs-insertcategory-category-inline-editor-icon' ).show();
			}

			me.categoryEditor.btnCancel.on( 'click', function(){
				me.categoryEditor.hide();
				$( '.bs-category-add-category' ).show();
				$( '.bs-category-container-categories' ).show();
			}, me.categoryEditor );

			$( document ).on( 'click', '.bs-insertcategory-category-inline-editor-icon', function( e ) {
				me.categoryEditor.hide();
				$( '.bs-category-add-category' ).show();
				$( '.bs-category-container-categories' ).show();
			});

			me.categoryEditor.switchToEditor();
		}, me );
	}, me );
	return false;
});