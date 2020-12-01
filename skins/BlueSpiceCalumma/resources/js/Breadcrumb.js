( function( d, $, mw ) {
	var api = new mw.Api();

	$( d ).on( 'click', '.bs-page-breadcrumbs .dropdown-toggle', function( e ) {
		var $buttonGroup = $(this).parent( '.btn-group' );
		if( $buttonGroup.data( 'loaded' ) === true ) {
			return;
		}

		var $mainButton = $buttonGroup.find( '.btn' ).first();
		var path = $mainButton.data( 'bs-path' );
		if( !path ) {
			return;
		}

		var $dropDownMenu = $buttonGroup.find( '.dropdown-menu > ul' ).first();
		api.abort();
		api.get( {
				"format": "json",
				"action": "bs-wikisubpage-treestore",
				"node": path,
				"limit": "-1"
		})
		.done( function( response ){
			for ( var i = 0; i < response.children.length; i++ ) {
				$dropDownMenu.append( '<li>' + response.children[i].page_link + '</li>' );
			};

			$buttonGroup.data( 'loaded', true );

			var $beadCrumbsCnt = $('#content .bs-page-breadcrumbs' ).first();
			var $dropDown = $buttonGroup.find( '.dropdown-menu' ).first();

			var $dropDownListItems = $dropDown.find( 'a' );

			var padding = $dropDown.width() - $( $dropDownListItems[0] ).width();

			var textWidth = 0;
			for ( var i = 0; i < $dropDownListItems.length; i++ ){
				var element = $dropDownListItems[i];

				var whiteSpace = element.style.whiteSpace;
				element.style.whiteSpace = "nowrap";

				if ( $( element ).width() > textWidth ) {
					textWidth = $( element ).width();
				}
				element.style.whiteSpace = whiteSpace;
			}
			$dropDown.css( 'width', ( padding + textWidth + padding ) + 'px' );

			if ( $dropDown.width() < $beadCrumbsCnt.width() ) {
				$dropDown.width( $beadCrumbsCnt.width() - 4 )
			}

			var beadCrumbsCntOffset = $beadCrumbsCnt.offset();
			var dropDownOffset = $dropDown.offset();
			if ( beadCrumbsCntOffset.left > dropDownOffset.left ) {
				$dropDown.offset( { left: beadCrumbsCntOffset.left } );
			}

			beadCrumbsCntOffset.right = beadCrumbsCntOffset.left + $( '#content' ).width();
			dropDownOffset.right = dropDownOffset.left + $dropDown.width();
			if ( beadCrumbsCntOffset.right < dropDownOffset.right ) {
				$dropDown.offset( { left: beadCrumbsCntOffset.right - $dropDown.width() - 2 } );
			}
		});
	});
})( document, jQuery, mediaWiki );
