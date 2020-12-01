( function () {

	// Detects Mobile Devices
	function isMobile() {
		return (
			navigator.userAgent.match( /Android/i ) ||
			navigator.userAgent.match( /BlackBerry/i ) ||
			navigator.userAgent.match( /iPhone|iPad|iPod/i ) ||
			navigator.userAgent.match( /Opera Mini/i ) ||
			navigator.userAgent.match( /IEMobile/i )
		);
	}

	/**
	 * @param {NodeList} $cites
	 * @param {NodeList} $references
	 * @param {Node} $container (optional)
	 * @constructor
	 */
	function CiteDrawer( $cites, $references, $container ) {
		// Only do the initialization when we're allowed to run the widget
		if ( this.checkEnabled() ) {
			this.$cites = $cites;
			this.$references = $references;
			this.$drawer = null;
			this.$drawerContent = null;
			this.$container = $container || $( document.body );
			this.addMarkup();
			this.bindEvents();
		}
	}

	/**
	 * Checks if we're allowed to run widget on current device
	 *
	 * @return {boolean}
	 */
	CiteDrawer.prototype.checkEnabled = function () {
		var wgCiteDrawerEnableDesktop = mw.config.get( 'wgCiteDrawerEnableDesktop' ) || false,
			wgCiteDrawerEnableMobile = mw.config.get( 'wgCiteDrawerEnableMobile' ) || false;

		if ( isMobile() && wgCiteDrawerEnableMobile ) {
			return true;
		}
		if ( wgCiteDrawerEnableDesktop ) {
			return true;
		}
		return false;
	};

	/**
	 * Builds widget markup and adds it into DOM
	 */
	CiteDrawer.prototype.addMarkup = function () {
		var self = this,
			$drawerWrapper = $( '<div>' ),
			$drawer = $( '<div>' ),
			$header = $( '<div>' ),
			$close = $( '<div>' ),
			$content = $( '<div>' ),
			theme = mw.config.get( 'wgCiteDrawerTheme' ) || 'dark';

		// The following classes are used here:
		// * cite-drawer-wrapper--theme-dark
		// * cite-drawer-wrapper--theme-light
		$drawerWrapper
			.addClass( 'cite-drawer-wrapper cite-drawer-wrapper--theme-' + theme );

		$header
			.addClass( 'drawer-header' )
			.text( 'citation' );

		$close.addClass( 'drawer-close' );
		$content.addClass( 'drawer-content' );

		$drawer
			.addClass( 'cite-drawer' )
			.append( $header )
			.append( $close )
			.append( $content );

		$drawerWrapper.append( $drawer );

		this.$drawer = $drawerWrapper;
		this.$drawerContent = $content;

		this.$container.append( this.$drawer );

		$close.on( 'click', this.closeDrawer.bind( this ) );

		$drawerWrapper.on( 'click', function ( event ) {
			if ( event.target ) {
				self.closeDrawer( event );
			}
		} );

	};

	/**
	 * Closes drawer
	 */
	CiteDrawer.prototype.closeDrawer = function () {
		this.$drawer.removeClass( 'drawer-active' );
	};

	/**
	 * Binds to click events on elements
	 */
	CiteDrawer.prototype.bindEvents = function () {
		this.$cites.on( 'click', this.onCiteClick.bind( this ) );
	};

	/**
	 * Escapes an id string
	 *
	 * @param {string} id
	 * @return {string}
	 */
	CiteDrawer.prototype.escapeID = function ( id ) {
		return id.replace( /(:|\.|\[|\]|,|=|@)/g, '\\$1' );
	};

	/**
	 * Handles ref click
	 *
	 * @param {Event} event
	 */
	CiteDrawer.prototype.onCiteClick = function ( event ) {
		var target = $( event.target ).attr( 'href' ),
			$referenceItem = this.$references.find( this.escapeID( target ) ),
			referenceHtml = $referenceItem.find( '.reference-text' ).html();

		event.preventDefault();

		this.$drawerContent.html( $( event.target ).text() + ' ' + referenceHtml );
		this.$drawer.addClass( 'drawer-active' );
	};

	/**
	 * Exposes class to the outer world
	 *
	 * @type {CiteDrawer}
	 */
	mw.Citedrawer = CiteDrawer;

}() );
