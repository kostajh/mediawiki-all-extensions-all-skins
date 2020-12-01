/**
 *
 * @author     Patric Wirth
 * @package    BluespiceSocial
 * @subpackage BlueSpiceSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

bs.social.EntityListMenuFilterOwnerID = function( key, mVal, EntityListMenu ) {
	bs.social.EntityListMenuFilter.call( this, key, mVal, EntityListMenu );
};

OO.initClass( bs.social.EntityListMenuFilterOwnerID );
OO.inheritClass(
	bs.social.EntityListMenuFilterOwnerID,
	bs.social.EntityListMenuFilter
);

bs.social.EntityListMenuFilterOwnerID.prototype.makeAllowedValues = function( mVal ) {
	if( !mVal || !mVal.value ) {
		mVal = 0;
	} else {
		mVal = mVal.value;
	}
	var items = [], userID = mw.config.get( 'wgUserId', 0 );

	if( userID > 0 && mVal === userID ) {
		items.push( {
			id: userID,
			text: this.getVarLabel( 'mine' ),
			selected: true
		});
		items.push( {
			id: 0,
			text: this.getVarLabel( 'all' )
		});
	} else if( mVal > 0 ) {
		items.push( {
			id: mVal,
			text: this.getVarLabel( mVal ),
			selected: true,
		});
		items.push( {
			id: 0,
			text: this.getVarLabel( 'all' )
		});
	} else {
		items.push( {
			id: userID,
			text: this.getVarLabel( 'mine' )
		});
		items.push( {
			id: 0,
			text: this.getVarLabel( 'all' ),
			selected: true
		});
	}

	return items;
};

bs.social.EntityListMenuFilterOwnerID.prototype.makeField = function( mVal ) {
	var field = $(
		'<label>'
		+ mw.message( 'bs-social-var-ownerid' ).plain()
		+ '<select style="width:100%"></select>'
		+ '</label>'
	);
	this.$element = field;
	return field;
};

bs.social.EntityListMenuFilterOwnerID.prototype.init = function( mVal ) {
	if( this.initDone ) {
		return;
	}

	var values = this.makeAllowedValues( mVal );
	var me = this;
	this.$element.find( 'select' ).select2({
		multiple: false,
		data: values,
		placeholder: mw.message( 'bs-social-filter-owner-label' ).plain(),
		allowClear: false,
		disabled: values.length < 2
	});

	this.$element.find( 'select' ).on( 'select2:select', function( e ) {
		me.change( e.params.data.id );
	});
	this.$element.find( 'select' ).on( 'select2:unselect', function( e ) {
		me.change( 0 );
	});
	this.initDone = true;
};

bs.social.EntityListMenuFilterOwnerID.prototype.getVarLabel = function( val ) {
	if( typeof val !== "string" ) {
		return val;
	}
	var msg = val;
	if( val === 'all' ) {
		msg = 'bs-social-filter-owner-val-all';
	} else if( val === 'mine' ) {
		msg = 'bs-social-filter-owner-val-mine';
	}
	return mw.message( msg ).plain();
};

bs.social.EntityListMenuFilterOwnerID.prototype.getData = function( data ) {
	var val = this.$element.find( 'select' ).val() || 0;
	if( !val || val < 1 ) {
		return data;
	}
	data.filter = data.filter || [];
	data.filter.push({
		property: 'ownerid',
		value: val,
		type: 'numeric'
	});
	return data;
};

bs.social.EntityListMenuFilterOwnerID.prototype.activate = function() {
	this.$element.find( 'select' ).prop( "disabled", false );
	return bs.social.EntityListMenuFilterOwnerID.super.prototype.activate.apply( this );
};

bs.social.EntityListMenuFilterOwnerID.prototype.deactivate = function() {
	this.$element.find( 'select' ).prop( "disabled", true );
	return bs.social.EntityListMenuFilterOwnerID.super.prototype.deactivate.apply( this );
};

bs.social.EntityListMenuFilters.ownerid = bs.social.EntityListMenuFilterOwnerID;