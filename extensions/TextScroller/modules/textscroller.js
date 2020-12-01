// Make sure Object.create is available in the browser (for our prototypal inheritance)
// Courtesy of Douglas Crockford
if ( typeof Object.create !== 'function' ) {
	Object.create = function ( o ) {
		function F() {}
		F.prototype = o;
		return new F();
	};
}

( function () {

	// Main plugin class
	var Typer = {

			// Plugin option defaults, overrideable by user
			options: {
				speed: 60, // interval between key presses
				text: null, // text to type into element
				append: false // set to true to add text to element.
			},

			// Recursive function that types one letter at a time
			doType: function () {
				var _this = this,
					letter = _this.letters.shift();
				if (
					letter == '\n' || letter == '\r' ||
				letter.charCodeAt( 0 ) == 10 ||
				letter.charCodeAt( 0 ) == 13
				) {
					letter = '<br>';
				}
				_this.el.innerHTML += letter;

				// Scroll to bottom of the div to simulate the div being scrolled
				var scrollHeight = _this.el.scrollHeight;
				// if ( scrollHeight > this.prevScrollHeight ) {
				$( _this.el ).parent().scrollTop( scrollHeight );
				// this.prevScrollHeight = scrollHeight;
				// }

				// If there are more letters to type, setTimeout
				// Otherwise, execute callback function
				if ( _this.letters.length > 0 ) {
					_this.timeout = window.setTimeout( function () {
						_this.doType();
					}, _this.options.speed );
				} else {
					if ( typeof _this.callback === 'function' ) {
						_this.callback( this.el );
					}
				}
			},

			isTyping: function () {
				return this.letters.length > 0;
			},

			stop: function () {
				window.clearTimeout( this.timeout );
				this.el.innerHTML = this.originalText;
				this.letters = '';
				this.$el.hide();
			},

			restart: function () {
				window.clearTimeout( this.timeout );
				this.el.innerHTML = this.originalText;
				this.init( this.el, this.options, this.callback );
			},

			// Plugin init function
			init: function ( el, options, callback ) {
			// Hide arrow if there isn't any text
				var arrow = $( el ).parent().parent().find( '.arrow_outer' );
				if ( arrow.text() == '' ) {
					arrow.hide();
				}

				// Strip HTML from text
				$( el ).html( $( el ).text() );

				this.options = $.extend( {}, this.options, options );
				this.callback = callback;
				this.el = el;
				this.$el = $( el );

				// Replace the break characters with new lines
				// el.innerHTML = el.innerHTML.replace( /@br@/g, '\n' );

				this.originalText = this.options.text || el.innerHTML;
				this.letters = ( this.options.text || el.innerHTML ).split( '' );
				this.prevScrollHeight = 0;
				$( el ).parent().scrollTop( 0 );

				if ( !this.options.append ) {
					this.el.innerHTML = '';
				}
				if ( this.$el.is( ':hidden' ) ) {
					this.$el.show();
				}

				this.doType();
			}
		},

		isMobileDomain = window.location.hostname.match( /\bm\./ ) != null,
		WAIT_INTERVAL = 500, // Timer interval
		timer,
		typers = {};

	// Reformat scroller text by replacing line break tokens with appropriate HTML or ASCII line breaks
	function addLineBreaks() {
		$( '.greytext' ).each( function ( i, div ) {
			$( this ).html( $( this ).html().replace( /@br@/g, '<br/>' ) );
		} );
		$( '.scrolltext' ).each( function ( i, div ) {
			var ua = navigator.userAgent.toLowerCase();
			if ( /msie (\d+\.\d+);/.test( ua ) && parseInt( ua.split( 'msie' )[ 1 ] <= 8 ) ) {
				$( this ).html( $( this ).html().replace( /@br@/g, '<br/>' ) );
			} else {
				$( this ).html( $( this ).html().replace( /@br@/g, '\n' ) );
			}
		} );
	}

	/**
	 * Make some CSS tweaks for specific browsers
	 */
	function adjustCSS() {
		var ua = navigator.userAgent.toLowerCase(),
			isOlderIE = /msie (\d+\.\d+);/.test( ua ),
			ieVersion = parseInt( ua.split( 'msie' )[ 1 ] );

		if ( isOlderIE && ieVersion <= 8 ) {
			$( '.scrolltext' ).show();
		} else {
			// Resize the scrolling div based on the size of the greytext
			if ( isLargeDisplay() ) {
				$( '.textscroller_inner' ).each( function ( i, div ) {
					$( this ).height( $( this ).find( '.greytext' ).height() + 50 );
				} );
			}
		}

		if ( isOlderIE && ieVersion < 8 ) {
			$( '.arrow_right' ).addClass( 'scroller_none' );
		}

		if ( isOlderIE && ieVersion == 7 ) {
			$( '.textscroller_outer' ).addClass( 'scroller_iewidth' );
		}

		if ( /(mozilla)(?:.*? rv:([\w.]+))?/.test( ua ) ) {
			$( '.scrolltext' ).css( 'margin-bottom', '35px' );
		}

		if ( isMobileDomain && !isLargeDisplay() ) {
			$( '.textscroller_outer' ).addClass( 'scroller_mobile' );
			$( '.textscroller_inner' ).addClass( 'scroller_inner_mobile' );
			$( '.arrow_outer' ).addClass( 'scroller_none' );
			$( '.greytext' ).addClass( 'scroller_none' );
		}
	}

	function toggleScrollers() {
		$( 'div[id^=scrl-]' ).each( function ( i, div ) {
			var divId = $( div ).attr( 'id' ),
				typer = typers[ divId ];
			if ( isScrolledIntoView( div ) ) {
				if ( typeof typer === 'undefined' ) {
					typers[ divId ] = type( div );
				} else if ( !typer.isTyping() ) {
					typers[ divId ].restart();
				}
			} else {
				if ( typeof typer !== 'undefined' ) {
					typers[ divId ].stop();
				}
			}
		} );
	}

	function type( el, options, callback ) {
		var el = $( el ).find( '.scrolltext' )[ 0 ],
			t = Object.create( Typer );
		t.init( el, options, callback );
		return t;
	}

	function isScrolledIntoView( elem ) {
		var docViewTop = $( window ).scrollTop(),
			docViewBottom = docViewTop + $( window ).height(),
			elemTop = $( elem ).offset().top,
			elemBottom = elemTop + $( elem ).height();

		return ( ( elemBottom >= docViewTop ) && ( elemTop <= docViewBottom ) ||
			( elemBottom <= docViewBottom ) && ( elemTop >= docViewTop ) );
	}

	function isLargeDisplay() {
		var largeDisplay = true,
			isLandscape = ( document.documentElement.clientHeight < document.documentElement.clientWidth );
		if ( ( document.documentElement.clientWidth < 600 ) || ( document.documentElement.clientHeight < 421 && isLandscape ) ) {
			largeDisplay = false;
		}
		return largeDisplay;
	}

	// Init the typers
	$( function () {
		adjustCSS();
		addLineBreaks();

		var ua = navigator.userAgent.toLowerCase();
		if ( !( /msie (\d+\.\d+);/.test( ua ) && parseInt( ua.split( 'msie' )[ 1 ] <= 8 ) ) ) {
			toggleScrollers();

			// On scroll complete toggle viewable videos to play state
			$( window ).on( 'scroll', function () {
				window.clearTimeout( timer );
				timer = setTimeout( toggleScrollers, WAIT_INTERVAL );
			} );
		}
	} );

}() );
