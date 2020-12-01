/**
 *
 * @author     Patric Wirth
 * @package    BluespiceSocial
 * @subpackage BSSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

bs.social.EntityListMenuFilterTags = function( key, mVal, EntityListMenu ) {
	bs.social.EntityListMenuFilter.call( this, key, mVal, EntityListMenu );
	var me = this;
	me.initDone = false;
};

OO.initClass( bs.social.EntityListMenuFilterTags );
OO.inheritClass( bs.social.EntityListMenuFilterTags, bs.social.EntityListMenuFilter );

bs.social.EntityListMenuFilterTags.prototype.change = function( mVal ) {
	if( !mVal ) {
		mVal = [];
	}
	bs.social.EntityListMenuFilterTags.super.prototype.change.apply( this, [
		mVal
	]);
};

bs.social.EntityListMenuFilterTags.prototype.makeField = function( mVal ) {
	var field = $(
		'<label>'
		+ this.getVarLabel( 'tags' )
		+ '<select style="width:100%"></select>'
		+ '</label>'
	);
	this.$element = field;
	this.makeQuickFilterButtons();
	return field;
};

bs.social.EntityListMenuFilterTags.prototype.makeQuickFilterButtons = function() {
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

	this.$removeAllButton.insertBefore( this.$element.find('select') );
};

bs.social.EntityListMenuFilterTags.prototype.init = function( mVal ) {
	if( this.initDone ) {
		return;
	}
	var me = this;

	this.$select2 = this.$element.find( 'select' ).select2({
		multiple: true,
		placeholder: this.getVarLabel( 'tags' ),
		minimumInputLength: 1,
		ajax: {
			url: mw.util.wikiScript( 'api' ),
			dataType: 'json',
			tape: 'POST',
			data: function (params) {
				return {
					action: 'bs-socialtitlequery-store',
					query: params.term
				};
			},
			processResults: function( data ) {
				var results = [];
				$.each(data.results, function( index, result ) {
					if( result.type && result.type === 'namespace' ) {
						return;
					}
					results.push( {
						id: result.displayText,
						text: result.displayText
					});
				});
				return {
					results: results
				};
			}
		}
	});

	if( mVal && mVal.value && mVal.value.length > 0 ) {
		for( var i = 0; i < mVal.value.length; i++ ) {
			this.$select2.append(
				new Option( mVal.value[i], mVal.value[i], true, true )
			);
		}
	}
	this.$select2.trigger( 'change' );

	this.$element.find( 'select' ).on( 'select2:select', function( e ) {
		me.change( me.$element.find( 'select' ).select2( "val" ) );
	});
	this.$element.find( 'select' ).on( 'select2:unselect', function( e ) {
		me.change( me.$element.find( 'select' ).select2( "val" ) );
	});
	this.initDone = true;
};

bs.social.EntityListMenuFilterTags.prototype.activate = function() {
	this.$element.find( 'select' ).prop( "disabled", false );
	return bs.social.EntityListMenuFilterTags.super.prototype.activate.apply( this );
};

bs.social.EntityListMenuFilterTags.prototype.deactivate = function() {
	this.$element.find( 'select' ).prop( "disabled", "disabled" );
	return bs.social.EntityListMenuFilterTags.super.prototype.deactivate.apply( this );
};

bs.social.EntityListMenuFilterTags.prototype.getData = function( data ) {
	var val = this.$element.find( 'select' ).select2( "val" );
	if( !Array.isArray( val ) ) {
		val = val.split();
	}
	if( val.length < 1 ) {
		return data;
	}
	data.filter = data.filter || [];
	for( var i = 0; i < data.filter.length; i++ ) {
		if( data.filter[i].property !== 'tags' ) {
			continue;
		}
		if( !data.filter[i].value ) {
			data.filter[i].value = [];
		}
		data.filter[i].value = val;
		return data;
	}
	data.filter.push( {
		property: 'tags',
		value: val,
		comparison: 'ct',
		type: 'list'
	});
	return data;
};

bs.social.EntityListMenuFilters = bs.social.EntityListMenuFilters || {};
bs.social.EntityListMenuFilters.tags = bs.social.EntityListMenuFilterTags;