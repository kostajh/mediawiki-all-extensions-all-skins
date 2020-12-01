/**
 *
 * @author     Patric Wirth
 * @package    BlueSpiceSocial
 * @subpackage BlueSpiceSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

bs.social.EntityListMenuFilterResolved = function( key, mVal, EntityListMenu ) {
	bs.social.EntityListMenuFilter.call( this, key, mVal, EntityListMenu );
};

OO.initClass( bs.social.EntityListMenuFilterResolved );
OO.inheritClass(
	bs.social.EntityListMenuFilterResolved,
	bs.social.EntityListMenuFilter
);

bs.social.EntityListMenuFilterResolved.prototype.makeAllowedValues = function( mVal ) {
	if( !mVal || !mVal.value ) {
		mVal = null;
	} else {
		mVal = mVal.value ? true : false;
	}
	var items = [];

	items.push( {
		id: 0,
		selected: mVal === null
	});
	items.push( {
		id: 1,
		text: this.getVarLabel( 'resolved' ),
		selected: mVal === true
	});
	items.push( {
		id: -1,
		text: this.getVarLabel( 'notresolved' ),
		selected: mVal === false
	});
	return items;
};

bs.social.EntityListMenuFilterResolved.prototype.makeField = function( mVal ) {
	var field = $(
		'<label>'
		+ mw.message( 'bs-socialresolve-var-resolved' ).plain()
		+ '<select style="width:100%"></select>'
		+ '</label>'
	);
	this.$element = field;
	return field;
};

bs.social.EntityListMenuFilterResolved.prototype.init = function( mVal ) {
	if( this.initDone ) {
		return;
	}

	var values = this.makeAllowedValues( mVal );
	var me = this;
	this.$element.find( 'select' ).select2({
		multiple: false,
		data: values,
		allowClear: true,
		placeholder: ''
	});

	this.$element.find( 'select' ).on( 'select2:select', function( e ) {
		me.change( parseInt( e.params.data.id ) );
	});
	this.$element.find( 'select' ).on( 'select2:unselect', function( e ) {
		me.change( 0 );
	});
	this.initDone = true;
};

bs.social.EntityListMenuFilterResolved.prototype.getVarLabel = function( val ) {
	var msg = val;
	if( val === 'resolved' ) {
		msg = 'bs-socialresolve-status-resolved';
	} else if( val === 'notresolved' ) {
		msg = 'bs-socialresolve-status-notresolved';
	}
	return mw.message( msg ).plain();
};

bs.social.EntityListMenuFilterResolved.prototype.getData = function( data ) {
	var val = this.selectedFilters || 0;
	if( val === 0 ) {
		return data;
	}
	data.filter = data.filter || [];
	data.filter.push({
		property: 'resolved',
		value: val > 0,
		type: 'boolean',
		comparison: 'eq'
	});
	return data;
};

bs.social.EntityListMenuFilterResolved.prototype.activate = function() {
	this.$element.find( 'select' ).prop( "disabled", false );
	return bs.social.EntityListMenuFilterResolved.super.prototype.activate.apply( this );
};

bs.social.EntityListMenuFilterResolved.prototype.deactivate = function() {
	this.$element.find( 'select' ).prop( "disabled", true );
	return bs.social.EntityListMenuFilterResolved.super.prototype.deactivate.apply( this );
};

bs.social.EntityListMenuFilters.resolved = bs.social.EntityListMenuFilterResolved;