(function(mw, $, bs, d, undefined){
	var _currentData = false;
	/**
	* This is an ugly workaround for ExtJS having troubles to render a
	* component to a hidden DOM element. For whatever reason, if the width of
	* the target is 0, the ExtJS component adapts to it even if
	* already rendered. So we wait for the target DOM element to have
	* a width...
	*/
	function _showGlobalNav() {
		if( $('#bs-bui-navigation').width() === 0 ) {
			setTimeout( _showGlobalNav, 100 );
			return;
		}

		if( $( '#bs-bui-navigation-allbooks' ).length > 0 ) {
			Ext.create( 'BS.Bookshelf.grid.Books', {
				collapsible: true,
				titleCollapse: true,
				stateId: 'bs-booshelfui-globalnav-books',
				cls: 'bs-bookshelfui-globalnav-allbooks',
				renderTo: 'bs-bui-navigation-allbooks',
				currentData: _currentData,
				collapsed: _currentData !== false,
				height: ( _currentData !== false ) ? 200 : false
			});
		}

		if( $( '#bs-bui-navigation-currentbook' ).length > 0 ) {
			if ( _currentData ) {
				Ext.create( 'BS.Bookshelf.panel.BookNavigation', {
					cls: 'bs-bookshelfui-globalnav-book',
					renderTo: 'bs-bui-navigation-currentbook',
					currentData: _currentData
				});
			};
		};
	}

	Ext.onReady(function(){
		mw.loader.using( 'skins.bluespiceskin.scripts', function() {
			//Only render if there is a container in the skin markup
			if($("#bs-nav-section-bs-bookshelfui").length === 0){
				return;
			}

			$('.bs-bookshelf-toc').each(function(){
				//If there are more than one book TOCs on the page, we look
				//wich one of them references a book that contains the current
				//page. This is the case if the tag provides a number for the
				//page
				if( $(this).data('bs-number') === '' ) {
					//We want to show the chapters in the left navigation and not
					//in the pages content
					$(this).show();
					return;
				}

				_currentData = $(this).data();
			});

			_showGlobalNav();

			if( _currentData ) {
				var $bookLink = $( '#bs-tab-link-bs-bookshelfui' );
				$bookLink.addClass( 'bs-booktab-in-book' );
				$( '#bs-nav-sections' ).tabs(
					'option',
					'active',
					$bookLink.parent().index() //this is bad...
				);
			}
		});
	});
})(mediaWiki, jQuery, blueSpice, document);

var bsBookshelfNavDeps = window.bsBookshelfNavDeps || [];
bsBookshelfNavDeps.push('ext.bluespice.bookshelfUI.navigationTab');
