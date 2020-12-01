/**
 *
 * @author     Patric Wirth
 * @package    BluespiceSocial
 * @subpackage BlueSpiceSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

bs.social.EntityList = function( $el ) {
	bs.social.El.call( this, $el );
	var me = this;
	me.menu = null;
	me.data = {};
	//total randomness... hopefully no followup errors now
	if( !$el ||! $el.attr || !$el.attr( 'data-entitylist' ) || $el.attr( 'data-entitylist' ).length < 0 ) {
		return;
	}
	try{
		me.data = JSON.parse( $el.attr( 'data-entitylist' ) );
	} catch(e) {
		console.error(e);
		return;
	}

	me.persistSettings = me.data.persistsettings || false;
	me.loading = false;
	me.makeUiID();

	if( me.data.showentitylistmenu && $el.prevAll( '.bs-social-entitylist-menu' ).length > 0 ) {
		me.menu = new bs.social.EntityListMenu(
			$el.prevAll( '.bs-social-entitylist-menu' ).first(),
			me
		);
	}
	if( me.data.showentitylistmore && $el.nextAll( '.bs-social-entitylist-more' ).length > 0 ) {
		me.more = new bs.social.EntityListMore(
			$el.nextAll( '.bs-social-entitylist-more' ).first(),
			me
		);
	}

	$(window).on( 'popstate', this.onPopState.bind( this ) );

	$(document).trigger( 'BSSocialEntityListInit', [ me, $el ] );
};
OO.initClass( bs.social.EntityList );
OO.inheritClass( bs.social.EntityList, bs.social.El );

bs.social.EntityList.prototype.getData = function() {
	//because this data sometimes gets randomly overwritten by some weird
	//reference
	var data = {};
	for( var key in this.data ) {
		data[key] = this.data[key];
	}
	return data;
};

bs.social.EntityList.prototype.getEntities = function( action, userData ) {
	action = action || 'add';
	userData = userData || {};
	var me = this;
	var dfd = $.Deferred();
	var taskData = me.makeTaskData( userData );

	me.emit( 'load', this, taskData );
	me.showLoadMask();
	bs.api.tasks.execSilent( 'social', 'getEntities', taskData )
	.done( function( response ) {
		if( !response ) {
			alert( ':(' );
		}
		me.emit( 'loadcomplete', this, response );
		if( action === 'add' ) {
			me.addChildren( response.payload.entities );
			dfd.resolve( me );
			return;
		}
		me.replaceChildren( response.payload.entities );
		dfd.resolve( me );
	})
	.then( function( response ){
		bs.social.init();
		me.emit( 'loadcomplete', this, response );
		me.hideLoadMask();
		me.persistSetting( 'filter', taskData );
		me.persistSetting( 'sort', taskData );
	});

	return dfd;
};
bs.social.EntityList.prototype.makeTaskData = function( userData ) {
	if( !userData ) {
		return this.getData();
	}
	var data = $.extend(
		this.getData(),
		userData
	);

	return data;
};

bs.social.EntityList.prototype.persistSetting = function( option, data ) {
	if( this.persistSettings === false ) {
		return;
	}
	if( !window.history ) {
		// Unless history is supported by the browser
		// we cannot save the state
		return;
	}
	data = data || this.data;

	if( !( option in data ) ) {
		return;
	}

	var optionValues = this.filterDataToPersist( option, data[option] || [] );
	optionValues = JSON.stringify( optionValues );

	var newUrl = this.setOrReplaceQSParam( option, optionValues );
	this.historyPush = this.historyPush || 0;
	this.historyPush++;
	window.history.pushState( { path: newUrl }, '', newUrl );
};

bs.social.EntityList.prototype.filterDataToPersist = function( option, data ) {
	var filteredData = [];
	if ( data.length < 1 ) {
		return filteredData;
	}
	for( var i = 0; i < data.length; i++ ) {
		if( !data[i].property ) {
			continue;
		}
		if( this.getUnpersistableFields( option ).indexOf( data[i].property ) > -1 ) {
			continue;
		}
		filteredData.push( data[i] );
	}
	return filteredData;
};

bs.social.EntityList.prototype.getUnpersistableFields = function( option ) {
	if ( option !== 'filter' ) {
		return [];
	}
	return [
		"timestampcreated",
		"timestamptouched"
	];
};

bs.social.EntityList.prototype.setOrReplaceQSParam = function( param, value ) {
	value = value || '';
	var location = window.location;
	var search = location.search;
	var operator = "?";
	if( search ) {
		search = search
			.replace( new RegExp('[?&]' + param + '=[^&#]*(#.*)?$' ), '$1' )
			.replace( new RegExp('([?&])' + param + '=[^&]*&'), '$1' );
		operator = search === '' ? '?' : '&';
		search = search + operator + param + '=' + value;
		return window.location.href.replace( window.location.search, search );
	}
	return window.location.href.replace(
		window.location.pathname,
		window.location.pathname + operator + param + '=' + value
	);
};

bs.social.EntityList.prototype.onPopState = function( e ) {
	if( this.historyPush === undefined ) {
		return;
	}
	// Changing URL on each filter change pushes to history,
	// meaning that the use would have to go back in browser as many
	// times as the filters has been changed.
	// This skip back accordingly
	window.history.go( - ( this.historyPush ) );
};

bs.social.EntityList.prototype.replaceChildren = function( entities ) {
	var me = this;
	me.getEl().children( 'li' ).remove();
	return me.addChildren( entities );
};

bs.social.EntityList.prototype.addChildren = function( entities ) {
	var ids = [];
	this.getEl().find( '> li > .bs-social-entity' ).each( function() {
		var id = $(this).attr( 'data-id' );
		if( !id || id === '' || id === 0 || id === '0' ) {
			return;
		}
		ids.push( parseInt( id ) );
	});

	for( var i = 0; i < entities.length; i++ ) {
		var entity = JSON.parse( entities[i].entity );
		if( !entity ) {
			continue;
		}
		if( $.inArray( entity.id, ids ) !== -1 ) {
			continue;
		}
		this.getEl().append( "<li>" + entities[i].view + "</li>" );
	}
};

bs.social.EntityList.prototype.getDirtyEntities = function() {
	var dirty = [];
	this.getEl().find( '.bs-social-entity.dirty' ).each( function() {
		dirty.push( bs.social.newFromEl( $( this ) ) );
	});
	return dirty;
};