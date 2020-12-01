( function () {

	var NewsTicker = {

		init: function () {
			window.setInterval( NewsTicker.next, 3000 );

			// If WikiEditor is available, use it on every textarea of the special page
			mw.loader.using( 'user.options' ).then( function () {
				if ( mw.user.options.get( 'usebetatoolbar' ) === 1 ) {
					$.when( mw.loader.using( 'ext.wikiEditor' ), $.ready ).then( function () {
						$( '.mw-special-NewsTicker textarea' ).each( function () {
							$( this ).wikiEditor( 'addModule', $.wikiEditor.modules.toolbar.config.getDefaultConfig() );
						} );
					} );
				}
			} );
		},

		next: function () {
			$( '.news-ticker' ).each( function () {
				var news = $( '.news', this ),
					current = $( '.current', this ),
					index = current.index(),
					length = news.length - 1,
					next = index === length ? 0 : index + 1;
				current.removeClass( 'current' ).slideToggle();
				news.eq( next ).addClass( 'current' ).slideToggle();
			} );
		}
	};

	$( NewsTicker.init );

}() );
