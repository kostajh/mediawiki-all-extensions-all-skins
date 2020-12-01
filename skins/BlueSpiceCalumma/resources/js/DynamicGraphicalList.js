( function( $, d, bs, undefined ) {

	//AKA "element-bound off-canvas"
	function DynamicGraphicalList( element, cfg ) {
		this.isRendered = false;

		this.title = cfg.title || '';
		this.intro = cfg.intro || '';
		this.body = cfg.body || $.noop; //ATM this is always a callback, but in future this might also be a DOMElement or string or DOM-Id or jQuery object
		this.direction = cfg.direction || direction.EAST;

		this.$viewport = $( cfg.viewport || document );
		this.$element = $(element);
		this.$outer = $('<div>')
			.addClass( 'dynamic-graphical-list-outer dynamic-graphical-list-outer-' + this.direction )
			.addClass( 'dynamic-graphical-list-hidden' )
			.appendTo( 'body' );
		this.$inner = $('<div>')
			.addClass( 'dynamic-graphical-list-inner' )
			.appendTo( this.$outer );
		this.$header = $('<div>')
			.addClass( 'dynamic-graphical-list-header' )
			.appendTo( this.$inner );
		this.$titlebox = $('<div>')
			.appendTo( this.$header );
		this.$title = $('<h1>')
			.addClass( 'dynamic-graphical-list-title' )
			.appendTo( this.$titlebox );
		this.$intro = $('<div>')
			.addClass( 'dynamic-graphical-list-intro' )
			.appendTo( this.$titlebox );
		this.$actions = $('<div>')
			.addClass( 'dynamic-graphical-list-actions' )
			.appendTo( this.$header );
		this.$body = $('<div>')
			.addClass( 'dynamic-graphical-list-body' )
			.appendTo( this.$inner );

		this.loadIndicator = new bs.LoadIndicator( {
			$element: this.$body,
			// We want to show loading right away, on every render
			// must be positive integer
			inTimeout: 1
		} );

		this.keyHandler = $.proxy( this.closeOnEsc, this );
		$( document ).on( 'keydown', this.keyHandler );
	};

	DynamicGraphicalList.prototype.toggle = function() {
		if( this.$outer.hasClass( 'dynamic-graphical-list-hidden') ) {
			this.show();
		}
		else {
			this.hide();
		}
	};

	DynamicGraphicalList.prototype.show = function() {
		this.closeOtherFylouts();
		if ( this.isRendered === false ) {
			this.loadIndicator.pushPending();
		}

		this.$outer.removeClass( 'dynamic-graphical-list-hidden' );
		this.$element.addClass( 'dynamic-graphical-list-visible' );
		this.$element.parents('.dynamic-graphical-list-link-wrapper').first().addClass( 'dynamic-graphical-list-visible' );

		if( this.isRendered ) {
			return;
		}

		var me = this;

		me.$title.html( this.title );
		me.$intro.html( this.intro );

		var actions = [];
		actions.push({
			icon: 'close',
			text: mw.message( 'bs-graphicallist-action-close' ).text(),
			action: 'close'
		});
		var actionsHTML = '';
		for( var i = 0; i < actions.length; i++ ) {
			var action = actions[i];
			actionsHTML += mw.html.element(
				'a',
				{
					'class': 'icon-' + action.icon,
					'data-target': 'graphical-list-action-' + action.action,
					'title': action.text
				}
			);
		}
		me.$actions.html( actionsHTML );

		//TODO: Dispatch DOMElement|jQuery|string(dom-id)|string|callback
		var getBodyPromise = this.body( this, this.$body );
		getBodyPromise.done( function( content ) {
			me.loadIndicator.popPending();
			me.$body.append( content );
			me.isRendered = true;
		});
	};

	DynamicGraphicalList.prototype.hide = function() {
		this.$outer.addClass( 'dynamic-graphical-list-hidden' );
		this.$element.removeClass( 'dynamic-graphical-list-visible' );
		this.$element.parents('.dynamic-graphical-list-link-wrapper').first().removeClass( 'dynamic-graphical-list-visible' );
	};

	DynamicGraphicalList.prototype.closeOtherFylouts = function() {
		$(document).find('.dynamic-graphical-list-visible')
			.removeClass( 'dynamic-graphical-list-visible' );
		$(document).find('.dynamic-graphical-list-outer')
			.addClass( 'dynamic-graphical-list-hidden' );
	};

	// $.proxy event handler
	DynamicGraphicalList.prototype.closeOnEsc = function ( e ) {
		if ( e.keyCode === 27 ) {
			this.hide();
		}
	};

	var DATA_ATTR = 'dynamicGraphicalList';
	var direction = {
		NORTH: 'north',
		EAST: 'east',
		SOUTH: 'south',
		WEST: 'west'
	};

	$.fn.dynamicGraphicalList = function( cfg ) {
		var dynGraphicalList = this.data( DATA_ATTR );
		if ( !dynGraphicalList ) {
			dynGraphicalList = new DynamicGraphicalList( this, cfg );
			this.data( DATA_ATTR, dynGraphicalList );
		}
		return dynGraphicalList;
	};

	$.dynamicGraphicalListDirection = direction;

})( jQuery, document, blueSpice );

$( document ).on( 'click', "*[data-target]", function(e){
	var target = $( this ).data( "target" );
	if( target === 'graphical-list-action-close'){
		$('.dynamic-graphical-list-outer').addClass( 'dynamic-graphical-list-hidden' );
		$('.dynamic-graphical-list-visible').removeClass('dynamic-graphical-list-visible');
	}
});
