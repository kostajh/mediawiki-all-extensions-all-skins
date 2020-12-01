( function( d, $, mw ) {
	$( d ).on( 'click', '.sidebar-toggle', function( e ){
		e.preventDefault();

		var target =  $( this ).attr( 'data-toggle' );

		var side = '';
		if( target == 'navigation-main-collapse' ){
			side = 'left';
		} else if( target == 'sitetools-main-collapse' ){
			side = 'right';
		}

		var sideAction = '';
		if( $( 'body' ).hasClass( target ) ){
			sideAction = 'close';
		} else {
			sideAction = 'open';
		}

		// Give grep a chance:
		// - bs-calumma-navigation-toggle-tooltip-left-close
		// - bs-calumma-navigation-toggle-tooltip-left-open
		// - bs-calumma-navigation-toggle-tooltip-right-close
		// - bs-calumma-navigation-toggle-tooltip-right-open
		var messageKey = 'bs-calumma-navigation-toggle-tooltip-' + side + '-' + sideAction;
		var messageOnButton = mw.message( messageKey  ).plain();
		$( this ).attr( 'title', messageOnButton );
		$( this ).attr( 'aria-label', messageOnButton );

		if( $( 'body' ).hasClass( target ) ){
			$( 'body' ).removeClass( target );
			bs.calumma.cookie.set( 'Calumma_'+target, 'false' );
		}
		else{
			$( 'body' ).addClass( target );
			if ( $( this ).closest( 'aside' ).hasClass( 'active' ) ) {
				$( this ).closest( 'aside' ).removeClass( 'active' );
			}
			bs.calumma.cookie.set( 'Calumma_'+target, 'true' );
		}
	});

	$( d ).on( 'click', '.bs-tabs .active a[data-toggle="tab"]', function( e ){
		if( ( $( d ).width() <= 1000 ) ) {
			var $anchor = $( this );
			var targetId = $anchor.attr( 'href' ).substring( 1 ); //cut off leading '#'
			var $tabsContainer = $anchor.closest( '.bs-tabs' );
			var tabsContainerId = $tabsContainer.attr( 'id' );

			var asideTrigger = $anchor.closest( 'aside' ).attr( 'data-toggle-by' );

			var activeTab = bs.calumma.cookie.get( 'CalummaTab_'+tabsContainerId );
			if ( ( activeTab === targetId ) || !activeTab ){
				$( 'body' ).addClass( asideTrigger );
				bs.calumma.cookie.set( 'Calumma_' + asideTrigger, 'true' );

				/*close graphicallist*/
				var $graphicalListTrigger = $anchor.closest( 'aside' ).find( '.dynamic-graphical-list-visible a.title' );
				$graphicalListTrigger.trigger( 'click' );
			}
		}
	});

	$( d ).ready( function(e){
		if( $( d ).width() <= 721 ) {
			$( 'body' ).addClass( 'navigation-main-collapse' );
			$( 'aside.navigation-main' ).css( 'display', 'block' );

			bs.calumma.cookie.set( 'Calumma_navigation-main-collapse', 'true' );
			bs.calumma.cookie.set( 'Calumma_desktop-view', 'false' );
		}
		if( ( $( d ).width() > 721 ) && ( $( d ).width() <= 1000 ) ) {
			$( 'body' ).addClass( 'navigation-main-collapse' );
			$( 'body' ).addClass( 'sitetools-main-collapse' );

			bs.calumma.cookie.set( 'Calumma_navigation-main-collapse', 'true' );
			bs.calumma.cookie.set( 'Calumma_sitetools-main-collapse', 'true' );
			bs.calumma.cookie.set( 'Calumma_desktop-view', 'false' );
		}
		if( $( d ).width() > 1000 ) {
			bs.calumma.cookie.set( 'Calumma_desktop-view', 'true' );
		}
	});

	$( d ).ready( function(e){
		if( $( d ).width() > 721 ) {
			if ( $( 'aside.navigation-main.active' ).length > 0 ) {
				$( 'body.navigation-main-collapse' ).removeClass( 'navigation-main-collapse' );
			}

			if ( $( 'aside.sitetools-main.active' ).length > 0 ) {
				$( 'body.sitetools-main-collapse' ).removeClass( 'sitetools-main-collapse' );
			}
		}

		var leftButtonMsgKey = 'bs-calumma-navigation-toggle-tooltip-left-close';
		var rightButtonMsgKey = 'bs-calumma-navigation-toggle-tooltip-right-close';

		if( $( 'body' ).hasClass( 'navigation-main-collapse' ) ){
			leftButtonMsgKey = 'bs-calumma-navigation-toggle-tooltip-left-open';
		}
		if( $( 'body' ).hasClass( 'sitetools-main-collapse' ) ){
			rightButtonMsgKey = 'bs-calumma-navigation-toggle-tooltip-right-open';
		}

		var $mainNavToggle = $(".sidebar-toggle .navigation-main-collapse").parent( '.sidebar-toggle' );
		$mainNavToggle.attr('title', mw.message( leftButtonMsgKey ).plain());
		$mainNavToggle.attr('aria-label', mw.message( leftButtonMsgKey ).plain());

		var $siteToolsToggle = $(".sidebar-toggle .sitetools-main-collapse").parent( '.sidebar-toggle' );
		$siteToolsToggle.attr('title', mw.message( rightButtonMsgKey ).plain());
		$siteToolsToggle.attr('aria-label', mw.message( rightButtonMsgKey ).plain());


	});
})( document, jQuery, mediaWiki );

