( function( mw, $, bs, d, undefined ){
	bs.util.registerNamespace( 'bs.pageassignments' );

	bs.pageassignments.GraphicalList = function() {};
	bs.pageassignments.GraphicalList.prototype.getTitle = function() {
		return '';
	};

	bs.pageassignments.GraphicalList.prototype.getActions = function() {
		return [];
	};

	bs.pageassignments.GraphicalList.prototype.getBody = function() {
		var me = this;
		var dfd = $.Deferred();

		var htmlForSitetools = mw.config.get( 'bsgPageAssignmentsSitetools' );

		dfd.resolve( function() {
			var html = '<div class="grapicallist-pageassignments-body">';
			for( var type in htmlForSitetools ) {
				html += '<div><span class="bs-icon-' + type + ' section">'
					+ me.getTypeMessage( type )
					+ '</span>';
				for( var i = 0; i < htmlForSitetools[type].length; i++ ) {
					html += htmlForSitetools[type][i]['html'];
				}
				html += '</div>';
			}
			html += '</div>';

			return html;
		} );


		return dfd;
	};

	bs.pageassignments.GraphicalList.prototype.getTypeMessage = function( type ) {
		var msg = mw.message( 'bs-pageassignments-assignee-type-' + type );
		return msg.exists() ? msg : type;
	};

	bs.pageassignments.GraphicalListFactory = function() {
		return new bs.pageassignments.GraphicalList();
	};


	$( d ).on( 'click', "*[data-target]", function(e){
		var target = $( this ).data( "target" );
		if( target === 'graphical-list-action-preview' ){
			$( '.grapicallist-pageassignments-body .list' ).css( 'display', 'none' );
			$( '.grapicallist-pageassignments-body .preview' ).css( 'display', 'block' );
		}
		if( target === 'graphical-list-action-list' ){
			$( '.grapicallist-pageassignments-body .list' ).css( 'display', 'block' );
			$( '.grapicallist-pageassignments-body .preview' ).css( 'display', 'none' );
		}
	});

})( mediaWiki, jQuery, blueSpice, document );