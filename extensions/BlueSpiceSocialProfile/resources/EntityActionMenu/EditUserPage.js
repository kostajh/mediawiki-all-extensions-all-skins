/*
* @author     Stefan Kühn
* @package    BluespiceSocial
* @subpackage BlueSpiceSocial
* @copyright  Copyright (C) 2020 Hallo Welt! GmbH, All rights reserved.
* @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
*/

bs.social = bs.social || {};
bs.social.EntityActionMenu = bs.social.EntityActionMenu || {};

bs.social.EntityActionMenu.EditUserPage = function ( entityActionMenu ) {
	OO.EventEmitter.call( this );
	var me = this;
	me.entityActionMenu = entityActionMenu;
	me.$element = null;
	me.priority = 50;
	if( me.entityActionMenu.entity.getData().outputtype !== 'Page' ) {
		return;
	}
	me.$element = $( '<li><a class="dropdown-item bs-social-entity-action-edituserpage">'
		+ '<span>' + mw.message( "bs-social-entityaction-edit" ).plain() + '</span>'
		+ '</a></li>'
	);
	me.$element.on( 'click', function( e ) { me.click( e ); } );
};

OO.initClass( bs.social.EntityActionMenu.EditUserPage );
OO.mixinClass( bs.social.EntityActionMenu.EditUserPage, OO.EventEmitter );

bs.social.EntityActionMenu.EditUserPage.prototype.click = function ( e ) {
	window.location.href = mw.util.getUrl(
		this.entityActionMenu.entity.data.get( 'relatedtitle' ),
		{ action:'edit' }
	);
	e.preventDefault();
	return false;
};
