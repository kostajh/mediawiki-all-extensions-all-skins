/**
 *
 * @author     Patric Wirth
 * @package    BluespiceSocial
 * @subpackage BlueSpiceSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

bs.social.EntityListMenuOptionDirection = function( key, mVal, EntityListMenu ) {
	this.initDone = false;
	bs.social.EntityListMenuOption.call( this, key, mVal, EntityListMenu );
};

OO.initClass( bs.social.EntityListMenuOptionDirection );
OO.inheritClass( bs.social.EntityListMenuOptionDirection, bs.social.EntityListMenuOption );

bs.social.EntityListMenuOptionDirection.prototype.makeAllowedOptions = function( mVal ) {
	if( Array.isArray( mVal ) ) {
		mVal = mVal[0];
	}
	var items = [];
	var values = ['DESC', 'ASC'];

	for( var i = 0; i < values.length; i++ ) {
		items.push( {
			id: values[i],
			text: this.getVarLabel( values[i] ),
			selected: values[i] === mVal.direction ? true : false
		});
	}
	return items;
};

bs.social.EntityListMenuOptionDirection.prototype.makeField = function( mVal ) {
	var field = $(
		'<label>'
		+ mw.message( 'bs-social-option-direction-label' ).plain()
		+ '<select style="width:100%"></select>'
		+ '</label>'
	);
	this.$element = field;
	return field;
};

bs.social.EntityListMenuOptionDirection.prototype.init = function( mVal ) {
	if( this.initDone ) {
		return;
	}

	var me = this;
	this.$element.find( 'select' ).select2({
		multiple: false,
		data: this.makeAllowedOptions( mVal ),
		placeholder: mw.message( 'bs-social-option-direction-label' ).plain(),
		allowClear: false
	});

	this.$element.find( 'select' ).on( 'select2:select', function( e ) {
		me.change( e.params.data.id );
	});
	this.$element.find( 'select' ).on( 'select2:unselect', function( e ) {
		me.change( 0 );
	});
	this.initDone = true;
};

bs.social.EntityListMenuOptionDirection.prototype.getVarLabel = function( val ) {
	var msg = val;
	if( val === 'ASC' ) {
		msg = 'bs-social-option-direction-val-asc';
	} else if( val === 'DESC' ) {
		msg = 'bs-social-option-direction-val-desc';
	}
	return mw.message( msg ).plain();
};

bs.social.EntityListMenuOptionDirection.prototype.getData = function( data ) {
	var val = this.$element.find( 'select' ).val();
	if( !val || val === '' ) {
		return data;
	}
	if( !data.sort ) {
		data.sort = [];
	}
	if( !data.sort[0] ) {
		data.sort[0] = {};
	}
	data.sort[0].direction = val;
	return data;
};

bs.social.EntityListMenuOptionDirection.prototype.activate = function() {
	this.$element.find( 'select' ).prop( "disabled", false );
	return bs.social.EntityListMenuOptionDirection.super.prototype.activate.apply( this );
};

bs.social.EntityListMenuOptionDirection.prototype.deactivate = function() {
	this.$element.find( 'select' ).prop( "disabled", true );
	return bs.social.EntityListMenuOptionDirection.super.prototype.deactivate.apply( this );
};

bs.social.EntityListMenuOptions.direction = bs.social.EntityListMenuOptionDirection;