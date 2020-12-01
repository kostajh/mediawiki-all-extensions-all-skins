/**
 *
 * @author     Patric Wirth
 * @package    BluespiceSocial
 * @subpackage BlueSpiceSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

bs.social.EntityListMenuFilterType = function( key, mVal, EntityListMenu ) {
	bs.social.EntityListMenuFilter.call( this, key, mVal, EntityListMenu );
};

OO.initClass( bs.social.EntityListMenuFilterType );
OO.inheritClass(
	bs.social.EntityListMenuFilterType,
	bs.social.EntityListMenuFilter
);

bs.social.EntityListMenuFilterType.prototype.makeAllowedValues = function( mVal ) {
	var items = [];
	var types = this.EntityListMenu.entityList.getData().availabletypes;
	for( var i = 0; i < types.length; i++ ) {
		var selected = false;
		if( mVal && mVal.value && mVal.value.length > 0 ) {
			for( var y = 0; y < mVal.value.length; y++ ) {
				if( mVal.value[y] !== types[i] ) {
					continue;
				}
				selected = true;
				break;
			}
		}
		items.push( {
			id: types[i],
			text: this.getVarLabel( types[i] ),
			selected: selected
		});
	}

	return items;
};

bs.social.EntityListMenuFilterType.prototype.change = function( mVal ) {
	if( !mVal ) {
		mVal = [];
	}
	bs.social.EntityListMenuFilterType.super.prototype.change.apply( this, [
		mVal
	]);
};

bs.social.EntityListMenuFilterType.prototype.makeField = function( mVal ) {
	var field = $(
		'<label>'
		+ this.getVarLabel( 'type' )
		+ '<select style="width:100%"></select>'
		+ '</label>'
	);
	this.$element = field;
	this.makeQuickFilterButtons();
	return field;
};

bs.social.EntityListMenuFilterType.prototype.makeQuickFilterButtons = function() {
	var me = this;

	var msg = mw.message(
		'bs-social-entitylistmenufilter-quickfilter-removeall'
	).plain();
	this.$removeAllButton = $(
		'<span title="' + msg + '" class="bs-socialentitylist-menufilter-removeall">'
		+ '&nbsp;'
		+ '</span>'
	);
	this.$removeAllButton.on( 'click',function() {
		var $select2 = me.$element.find( 'select' );
		$select2.val( null );
		$select2.trigger('change');
	});

	var msg = mw.message(
		'bs-social-entitylistmenufilter-quickfilter-addall'
	).plain();
	this.$addAllButton = $(
		'<span title="' + msg + '" class="bs-socialentitylist-menufilter-addall">'
		+ '&nbsp;'
		+ '</span>'
	);
	this.$addAllButton.on( 'click',function() {
		var $select2 = me.$element.find( 'select' );
		$select2.val( null );
		$select2.children( 'option' ).prop( "selected", "selected" );
		$select2.trigger( 'change' );
		me.change( me.$element.find('select').select2( "val" ) );
	});
	this.$removeAllButton.insertBefore( this.$element.find('select') );
	this.$addAllButton.insertBefore( this.$element.find('select') );
};

bs.social.EntityListMenuFilterType.prototype.init = function( mVal ) {
	if( this.initDone ) {
		return;
	}

	var me = this;
	var $select2 = this.$element.find( 'select' ).select2({
		multiple: true,
		placeholder: this.getVarLabel( 'type' ),
		allowClear: false
	});
	var values = this.makeAllowedValues( mVal );
	for( var i = 0; i < values.length; i++ ) {
		$select2.append(
			new Option(
				values[i].text,
				values[i].id,
				true,
				values[i].selected
			)
		);
	}
	$select2.trigger('change');

	this.$element.find( 'select' ).on( 'select2:select', function( e ) {
		me.change( me.$element.find('select').select2( "val" ) );
	});
	this.$element.find( 'select' ).on( 'select2:unselect', function( e ) {
		me.change( me.$element.find('select').select2( "val" ) );
	});
	this.initDone = true;
};

bs.social.EntityListMenuFilterType.prototype.getVarLabel = function( type ) {
	if( type === 'type' ) {
		return bs.social.EntityListMenuFilterType.super.prototype.getVarLabel.apply(
			this,
			[ type ]
		);
	}
	return mw.message( bs.social.config[type].TypeMessageKey ).plain();
};

bs.social.EntityListMenuFilterType.prototype.activate = function() {
	this.$element.find( 'select' ).prop( "disabled", false );
	return bs.social.EntityListMenuFilterType.super.prototype.activate.apply( this );
};

bs.social.EntityListMenuFilterType.prototype.deactivate = function() {
	this.$element.find( 'select' ).prop( "disabled", true );
	return bs.social.EntityListMenuFilterType.super.prototype.deactivate.apply( this );
};

bs.social.EntityListMenuFilterType.prototype.getData = function( data ) {
	data.filter = data.filter || [];
	var val = this.$element.find( 'select' ).select2( "val" );
	if( !Array.isArray( val ) ) {
		val = val.split();
	}
	for( var i = 0; i < data.filter.length; i++ ) {
		if( data.filter[i].property !== 'type' ) {
			continue;
		}
		if( !data.filter[i].value ) {
			data.filter[i].value = [];
		}
		data.filter[i].value = val;
		data.comparison = 'ct';
		return data;
	}
	data.filter.push( {
		property: 'type',
		value: val,
		comparison: 'ct',
		type: 'list'
	});
	return data;
};

bs.social.EntityListMenuFilters.type = bs.social.EntityListMenuFilterType;