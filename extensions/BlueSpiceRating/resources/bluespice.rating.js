/**
 * Js for Rating extension
 *
 * @author     Patric Wirth
 * @package    BlueSpiceRating
 * @subpackage Rating
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

OO = window.OO; //To make OOJS available within the RL modules
window.bs = window.bs || {};
window.bs.rating = window.bs.rating || {};
bs.rating.factory = new OO.Factory();
bs.rating.types = {};
bs.rating.itemStore = {};
bs.rating.register = function( sType, sStaticName, oClass ) {
	oClass.static.name = sStaticName;
	bs.rating.factory.register( oClass );
	bs.rating.types[sType] = sStaticName;
};
bs.rating.uuid = 0;
bs.rating.generateUniqueId = function() {
	return "bsr-ui-" + ( ++bs.rating.uuid );
};
bs.rating.config = mw.config.get('BSRatingConfig', {});
bs.rating.getUiID = function( $el ) {
	if( $el.attr('id') && $el.attr('id').length > 0 ) {
		return $el.attr('id');
	}
	return false;
};
bs.rating.getFromStore = function( uiID ) {
	if( !uiID || !bs.rating.itemStore[uiID] ) {
		return null;
	}
	return bs.rating.itemStore[uiID];
};

bs.rating.newFromEl = function( $el ) {
	if( !$el || $el.length < 1 ) {
		return null;
	}
	var uiID = bs.rating.getUiID( $el );
	var item = bs.rating.getFromStore( uiID );
	if( item ) {
		return item;
	}
	return bs.rating.createFromEl( $el );
};
bs.rating.createFromEl = function( $el ) {
	if( !$el || $el.length < 1 ) {
		return null;
	}
	var type = $el.attr( 'data-type' );
	if( !type || type === '' || typeof type === 'undefined' ) {
		type = 'base';
	}

	if( typeof bs.rating.types[type] === "undefined" ) {
		throw "Unregistered type: " + type;
	}

	if( typeof $el.attr('data-item') === "undefined" ) {
		throw "The fild 'data-item' is missing.";
	}

	var data = JSON.parse( $el.attr('data-item') );

	var ratingitem = bs.rating.factory.create(
		bs.rating.types[type],
		$el,
		type,
		data
	);
	bs.rating.itemStore[ratingitem.makeUiID()] = ratingitem;
};
bs.rating.init = function(){
	$(".bs-rating").each( function() {
		if( bs.rating.getUiID( $(this) ) ) {
			//already exists
			return null;
		}
		bs.rating.createFromEl( $(this) );
	});
};

mw.loader.using( mw.config.get('BSRatingModules', []) ).done( function() {
	bs.rating.init();
});