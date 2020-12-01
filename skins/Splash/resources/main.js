/* JavaScript for the splash skin */

// Don't use Libertinus sans/biolinum for content on low-res windows (massive over-bolding)
$( function () {
	if ( navigator.appVersion.indexOf( 'Win' ) !== -1 && window.devicePixelRatio < 1.5 ) {
		$( 'body' ).addClass( 'windows-fonts' );
	}
} );

// Mobile popups
$( function () {
	var toggleTime = 200,
		mobileMediaQuery = window.matchMedia( 'screen and (max-width: 699px)' ),
		toggled = false,
		toggles = {
			// TODO: Lose the special toggle handles and just use the headers like on desktop
			'#global-menu-toggle': '#p-global-links',
			'#main-menu-toggle': '#mw-sidebar',
			'#toolbox-toggle': '#p-tb',
			'#personal-menu-toggle': '#p-personal',
			// TODO: Automatically just do all #page-tools-dropdowns items
			'#p-page-tools-label': '#p-page-tools .mw-portlet-body',
			'#p-lang-label': '#p-lang .mw-portlet-body',
			'#p-variants-label': '#p-variants .mw-portlet-body',
			'#p-coll-print_export-label': 'p-coll-print_export .mw-portlet-body'
		};

	// Special main page handling; no special toggles, whoo!
	if ( $( '.splash-mainpage' ).length ) {
		toggles = {
			'#p-personal-label': '#p-personal .mw-portlet-body',
			'#p-lang-label': '#p-lang .mw-portlet-body',
			'#mw-sidebar h2': '#sidebar-inner',
			'#mw-tools h2': '#tools-inner'
		};
	}

	function setToggled() {
		if ( !toggled ) {
			// swap hide method from screen-reader-friendly .hidden to display:none,
			// as presumably people are actually poking a screen for this...
			$.each( toggles, function ( toggle, target ) {
				$( target ).hide();
				$( target ).addClass( 'toggled' );
			} );

			toggled = true;
		}
	}

	$.each( toggles, function ( toggle, target ) {
		$( toggle ).on( 'click', function () {
			if ( mobileMediaQuery.matches && $( toggle ).closest( '.emptyPortlet' ).length == 0 ) {
				setToggled();
				$( target ).fadeToggle( toggleTime );
				$( '#menus-cover' ).fadeToggle( toggleTime );
			}
		} );
	} );

	// Close menus on click outside
	$( document ).on( 'click touchstart', function ( e ) {
		if ( $( e.target ).closest( '#menus-cover' ).length > 0 ) {
			$( Object.values( toggles ).join( ', ' ) ).fadeOut( toggleTime );
			$( '#menus-cover' ).fadeOut( toggleTime );
		}
	} );
} );

// Desktop click toggles
$( function () {
	// all possible toggle items
	var allDropdowns = '#p-personal, #p-lang, #mw-sidebar, #mw-tools, '
		+ '#mw-sidebar .mw-portlet, #p-tb, #page-tools-dropdowns .mw-portlet';

	$( allDropdowns ).click( function( e ) {
		var dropdowns= '', toggles = '';

		// Okay, what actually are our current dropdowns?
		if ( window.matchMedia( 'screen and (min-width: 700px)' ).matches ) {
			if ( $( '.splash-mainpage' ).length ) {
				dropdowns = '#p-personal, #p-lang, #mw-sidebar, #mw-tools';
				toggles = '#p-personal-label, #p-lang-label, h2';
			} else if ( window.matchMedia( 'screen and (min-width: 1001px)' ).matches ) {
				// all sidebar items separate dropdowns
				dropdowns = '#p-personal, #mw-sidebar .mw-portlet, #p-tb, #page-tools-dropdowns .mw-portlet';
				toggles = '#p-personal-label, #mw-sidebar .mw-portlet > h3, #p-tb-label, #page-tools-dropdowns .mw-portlet > h3';
			} else {
				// sidebar as single dropdown
				dropdowns = '#p-personal, #mw-sidebar, #p-tb, #page-tools-dropdowns .mw-portlet';
				toggles = '#p-personal-label, h2, #p-tb-label, #page-tools-dropdowns .mw-portlet > h3';
			}
		}

		// We got here from all possible dropdowns; make sure this is a current dropdown
		if ( $( dropdowns ).is( this ) ) { // or: Is this in dropdowns?
			// Check if it's already open so we don't open it again
			if ( $( this ).hasClass( 'dropdown-active' ) ) {
				if ( $( e.target ).closest( $( toggles ) ).length > 0 ) {
					// treat reclick on the header as a toggle
					closeOpen();
				}
				// Clicked inside an open menu; don't do anything
			} else {
				closeOpen();
				e.stopPropagation(); // stop hiding it!
				$( this ).addClass( 'dropdown-active' );
			}
		}
	} );

	$( document ).click( function ( e ) {
		if ( $( e.target ).closest( '.dropdown-active' ).length > 0 ) {
			// Clicked inside an open menu; don't close anything
		} else {
			closeOpen();
		}
	} );

	// Just close everything, nobody cares.
	function closeOpen() {
		$( allDropdowns ).removeClass( 'dropdown-active' );
	}
} );
