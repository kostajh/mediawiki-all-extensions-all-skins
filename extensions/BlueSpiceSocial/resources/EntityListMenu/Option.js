/**
 *
 * @author     Patric Wirth
 * @package    BluespiceSocial
 * @subpackage BlueSpiceSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

bs.social.EntityListMenuOption = function( key, mVal, EntityListMenu ) {
	OO.EventEmitter.call( this );
	var me = this;
	me.key = key;
	me.$element = null;
	me.EntityListMenu = EntityListMenu;
	me.active = true;
	me.field = me.makeField( mVal );
	me.initDone = false;
};

OO.initClass( bs.social.EntityListMenuOption );
OO.mixinClass( bs.social.EntityListMenuOption, OO.EventEmitter );

bs.social.EntityListMenuOption.prototype.init = function( mVal ) {};

bs.social.EntityListMenuOption.prototype.getEntityListMenu = function() {
	return this.EntityListMenu;
};

bs.social.EntityListMenuOption.prototype.isActive = function() {
	return this.active;
};

bs.social.EntityListMenuOption.prototype.getData = function( data ) {
	return data;
};

bs.social.EntityListMenuOption.prototype.activate = function() {
	this.active = true;
	return this;
};

bs.social.EntityListMenuOption.prototype.deactivate = function() {
	this.active = false;
	return this;
};

bs.social.EntityListMenuOption.prototype.change = function( mVal ) {
	var oldMVal = this.getData( {} );
	var res = {
		result: true
	};
	this.emit( 'change', this, mVal, oldMVal, res );
	if( !res || !res.result ) {
		return false;
	}
	return this;
};

bs.social.EntityListMenuOption.prototype.makeField = function( mVal ) {
	return null;
};

bs.social.EntityListMenuOption.prototype.getVarLabel = function( varType ) {
	var varKeys = mw.config.get( 'bsgSocialVarMessageKeys', {} );
	return varKeys[varType] ? mw.message( varKeys[varType] ).plain() : varType;
};