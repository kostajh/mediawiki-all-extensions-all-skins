/**
 *
 * @author     Patric Wirth
 * @package    BluespiceSocial
 * @subpackage BlueSpiceSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

bs.social.EntityListMenuOptionSort = function( key, mVal, EntityListMenu ) {
	bs.social.EntityListMenuOption.call( this, key, mVal, EntityListMenu );
};

OO.initClass( bs.social.EntityListMenuOptionSort );
OO.inheritClass( bs.social.EntityListMenuOptionSort, bs.social.EntityListMenuOption );

bs.social.EntityListMenuOptionSort.prototype.makeAllowedOptions = function( mVal ) {
	if( Array.isArray( mVal ) ) {
		mVal = mVal[0];
	}
	var items = [];
	var values = this.EntityListMenu.entityList.getData().availablesorterfields;
	for( var i = 0; i < values.length; i++ ) {
		items.push( {
			id: values[i],
			text: this.getVarLabel( values[i] ),
			selected: values[i] === mVal.property ? true : false
		});
	}
	return items;
};

bs.social.EntityListMenuOptionSort.prototype.makeField = function( mVal ) {
	var field = $(
		'<label>'
		+ mw.message( 'bs-social-option-orderby-label' ).plain()
		+ '<select style="width:100%"></select>'
		+ '</label>'
	);
	this.$element = field;
	return field;
};

bs.social.EntityListMenuOptionSort.prototype.getData = function( data ) {
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
	data.sort[0].property = val;
	return data;
};

bs.social.EntityListMenuOptionSort.prototype.init = function( mVal ) {
	if( this.initDone ) {
		return;
	}

	var me = this;
	this.$element.find( 'select' ).select2({
		multiple: false,
		data: this.makeAllowedOptions( mVal ),
		placeholder: mw.message( 'bs-social-option-orderby-label' ).plain(),
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

bs.social.EntityListMenuOptionSort.prototype.activate = function() {
	this.$element.find( 'select' ).prop( "disabled", false );
	return bs.social.EntityListMenuOptionSort.super.prototype.activate.apply( this );
};

bs.social.EntityListMenuOptionSort.prototype.deactivate = function() {
	this.$element.find( 'select' ).prop( "disabled", true );
	return bs.social.EntityListMenuOptionSort.super.prototype.deactivate.apply( this );
};

bs.social.EntityListMenuOptions.sort = bs.social.EntityListMenuOptionSort;