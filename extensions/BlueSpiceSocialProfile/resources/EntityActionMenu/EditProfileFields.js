/*
* @author     Stefan Kühn
* @package    BluespiceSocial
* @subpackage BlueSpiceSocial
* @copyright  Copyright (C) 2020 Hallo Welt! GmbH, All rights reserved.
* @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
*/

bs.social = bs.social || {};
bs.social.EntityActionMenu = bs.social.EntityActionMenu || {};

bs.social.EntityActionMenu.EditProfileFields = function ( entityActionMenu ) {
	OO.EventEmitter.call( this );
	var me = this;
	me.entityActionMenu = entityActionMenu;
	me.$element = null;
	me.priority = 50;
	if( me.entityActionMenu.entity.getData().outputtype !== 'Page' ) {
		return;
	}
	me.$element = $( '<li><a class="dropdown-item bs-social-entity-action-editprofilefields">'
		+ '<span>' + mw.message( "bs-social-entityaction-editprofilefields" ).plain() + '</span>'
		+ '</a></li>'
	);
	me.$element.on( 'click', function( e ) { me.click( e ); } );
};

OO.initClass( bs.social.EntityActionMenu.EditProfileFields );
OO.mixinClass( bs.social.EntityActionMenu.EditProfileFields, OO.EventEmitter );

bs.social.EntityActionMenu.EditProfileFields.prototype.click = function ( e ) {
	if( this.entityActionMenu.entity.editmode ) {
		e.preventDefault();
		return false;
	}
	this.entityActionMenu.entity.makeEditMode();
	e.preventDefault();
	return false;
};
