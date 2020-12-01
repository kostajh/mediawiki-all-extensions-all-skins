/**
 *
 * @author     Patric Wirth
 * @package    BlueSpiceSocial
 * @subpackage BlueSpiceSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

bs.social.EntityListMenuFilterArchived = function( key, mVal, EntityListMenu ) {
	bs.social.EntityListMenuFilter.call( this, key, mVal, EntityListMenu );
};

OO.initClass( bs.social.EntityListMenuFilterArchived );
OO.inheritClass(
	bs.social.EntityListMenuFilterArchived,
	bs.social.EntityListMenuFilter
);

bs.social.EntityListMenuFilterArchived.prototype.makeAllowedValues = function( mVal ) {
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
		id: -1,
		text: this.getVarLabel( 'notarchived' ),
		selected: mVal === false
	});
	items.push( {
		id: 1,
		text: this.getVarLabel( 'archived' ),
		selected: mVal === true
	});
	return items;
};

bs.social.EntityListMenuFilterArchived.prototype.makeField = function( mVal ) {
	var field = $(
		'<label>'
		+ mw.message( 'bs-social-var-archived' ).plain()
		+ '<select style="width:100%"></select>'
		+ '</label>'
	);
	this.$element = field;
	return field;
};

bs.social.EntityListMenuFilterArchived.prototype.init = function( mVal ) {
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
		return true;
	});
	this.$element.find( 'select' ).on( 'select2:unselect', function( e ) {
		me.change( 0 );
	});
	this.initDone = true;
};

bs.social.EntityListMenuFilterArchived.prototype.getVarLabel = function( val ) {
	var msg = val;
	if( val === 'archived' ) {
		msg = 'bs-social-filter-archived-val-archived';
	} else if( val === 'notarchived' ) {
		msg = 'bs-social-filter-archived-val-notarchived';
	}
	return mw.message( msg ).plain();
};

bs.social.EntityListMenuFilterArchived.prototype.getData = function( data ) {
	this.selectedFilters = parseInt( this.selectedFilters );
	if( this.selectedFilters === 0 ) {
		return data;
	}

	data.filter = data.filter || [];
	data.filter.push({
		property: 'archived',
		value: this.selectedFilters > 0,
		type: 'boolean',
		comparison: 'eq'
	});
	return data;
};

bs.social.EntityListMenuFilterArchived.prototype.activate = function() {
	this.$element.find( 'select' ).prop( "disabled", false );
	return bs.social.EntityListMenuFilterArchived.super.prototype.activate.apply( this );
};

bs.social.EntityListMenuFilterArchived.prototype.deactivate = function() {
	this.$element.find( 'select' ).prop( "disabled", true );
	return bs.social.EntityListMenuFilterArchived.super.prototype.deactivate.apply( this );
};

bs.social.EntityListMenuFilters.archived = bs.social.EntityListMenuFilterArchived;