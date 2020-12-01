/**
 *
 * @author     Patric Wirth
 * @package    BluespiceSocial
 * @subpackage BlueSpiceSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

bs.social.EntityListMenuFilter = function( key, mVal, EntityListMenu ) {
	OO.EventEmitter.call( this );
	var me = this;
	me.key = key;
	me.$element = null;
	me.EntityListMenu = EntityListMenu;
	me.active = true;
	me.field = me.makeField( mVal );
	me.initDone = false;
};

OO.initClass( bs.social.EntityListMenuFilter );
OO.mixinClass( bs.social.EntityListMenuFilter, OO.EventEmitter );

bs.social.EntityListMenuFilter.prototype.init = function( mVal ) {};

bs.social.EntityListMenuFilter.prototype.getEntityListMenu = function() {
	return this.EntityListMenu;
};

bs.social.EntityListMenuFilter.prototype.isActive = function() {
	return this.active;
};

bs.social.EntityListMenuFilter.prototype.activate = function() {
	this.active = true;
	return this;
};

bs.social.EntityListMenuFilter.prototype.deactivate = function() {
	this.active = false;
	return this;
};

bs.social.EntityListMenuFilter.prototype.getData = function( data ) {
	return data;
};

bs.social.EntityListMenuFilter.prototype.change = function( mVal ) {
	var oldMVal = this.getData( {} );
	this.selectedFilters = mVal;
	var res = {
		result: true
	};
	this.emit( 'change', this, mVal, oldMVal, res );
	if( !res || !res.result ) {
		return false;
	}
	return this;
};
bs.social.EntityListMenuFilter.prototype.makeField = function() {
	return null;
};

bs.social.EntityListMenuFilter.prototype.getVarLabel = function( varType ) {
	var varKeys = mw.config.get( 'bsgSocialVarMessageKeys', {} );
	return varKeys[varType] ? mw.message( varKeys[varType] ).plain() : varType;
};