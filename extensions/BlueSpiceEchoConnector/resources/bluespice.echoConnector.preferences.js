( function( mw, $ ) {

	var $matrix = $( '#mw-htmlform-category-notifications' );
	if( $matrix.length < 1 ) {
		return;
	}
	var $menu = $( '<div id="mw-htmlform-category-notifications-mask"></div>' );
	mw.loader.using( [ 'ext.bluespice.extjs' ] ).done( function() {
		$menu.insertAfter( $matrix );
		Ext.onReady( function() {
			var getMatrixSelection = function( action ) {
				var categories = [];
				var input = 'input[name="wpnotify-category-selection[]"][value^="page-' + action + '-"]';
				$matrix.find( input ).each( function() {
					if ( !$(this).is(':checked') ) {
						return;
					}
					categories.push( $(this).val().substring( 6 + action.length ) );
				} );
				return categories;
			};

			var selectMatrix = function( categories, action ) {
				var input = 'input[name="wpnotify-category-selection[]"][value^="page-' + action + '-"]';
				$matrix.find( input ).each( function() {
					var cat = $(this).val().substring( 6 + action.length );
					if ( $.inArray( cat, categories ) === -1 ) {
						$(this).prop( 'checked', false );
						return;
					}
					$(this).prop( 'checked', true );
				} );
			};
			// ignore actions. for now we just set both create and edit.
			// later there can a category select for each action create, edit...
			// action = action || 'page-create-';
			var actions = [ 'create', 'edit' ];
			var values = [];
			for ( var i = 0; i < actions.length; i++ ) {
				$.each( getMatrixSelection(actions[i]), function( i, el ) {
					if ( $.inArray( el, values ) !== -1 ) {
						return;
					}
					values.push( el );
				} );
			}
			var categorySelect = Ext.create( 'BS.form.CategoryBoxSelect', {
				value: values,
				minWidth: "100% !important;",
				showTree: function() {
					if( !this.wdTree ) {
						var categoryTree =  new BS.tree.Categories();
						categoryTree.on( 'itemclick', this.onTreeItemClick, this );

						this.wdTree = new Ext.Window({
							modal: true,
							title: mw.message( 'bs-extjs-categorytree-title' ).plain(),
							x: this.getX(),
							y: this.getY() + this.getHeight(),
							closeAction: 'hide',
							autoScroll: true,
							width: this.getWidth(),
							height: 300,
							items: [
								categoryTree
							]
						});

						this.wireUpWithContainerWindow();
					}

					this.wdTree.show();
				}
			} );
			categorySelect.on( 'change', function( element, categories ) {
				// ignore actions. for now we just set both create and edit.
				// later there can a category select for each action create, edit...
				// action = action || 'page-create-';
				var actions = [ 'create', 'edit' ];
				var values = [];
				for ( var i = 0; i < actions.length; i++ ) {
					selectMatrix( categories, actions[i] );
				}
			} );
			var panel = Ext.create( 'Ext.Panel', {
				renderTo: $menu.attr( 'id' ),
				items: [ categorySelect ]
			} );
			// this is a bit hacky, but whenever another tab is loaded and the extjs panel
			// is rendered when not visible, it shows up with dimentions 0x0.
			// so whenever the tab is switched to the echo section we redo the layout.
			// i could not manage to infuse the OOJs tabs nor set up an MutationObserver
			$( window ).on( 'hashchange', function( e ) {
				if ( window.location.hash !== '#mw-prefsection-echo' ) {
					return;
				}
				panel.updateLayout();
			} );
		} );
	} );

} )( mediaWiki, jQuery );