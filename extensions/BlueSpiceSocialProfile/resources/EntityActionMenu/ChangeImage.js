/*
* @author     Patric Wirth
* @package    BluespiceSocial
* @subpackage BlueSpiceSocialProfile
* @copyright  Copyright (C) 2020 Hallo Welt! GmbH, All rights reserved.
* @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
*/

bs.social = bs.social || {};
bs.social.EntityActionMenu = bs.social.EntityActionMenu || {};

bs.social.EntityActionMenu.ChangeImage = function ( entityActionMenu ) {
	OO.EventEmitter.call( this );
	var me = this;
	me.entityActionMenu = entityActionMenu;
	me.$element = null;
	me.priority = 10;
	if( me.entityActionMenu.entity.getData().outputtype !== 'Page' ) {
		return;
	}
	me.$element = $( '<li><a class="dropdown-item bs-social-entity-action-changeimage">'
		+ '<span>' + mw.message( "bs-socialprofile-entityaction-changeimage" ).plain() + '</span>'
		+ '</a></li>'
	);
	me.$element.on( 'click', function( e ) { me.click( e ); } );
};

OO.initClass( bs.social.EntityActionMenu.ChangeImage );
OO.mixinClass( bs.social.EntityActionMenu.ChangeImage, OO.EventEmitter );

bs.social.EntityActionMenu.ChangeImage.prototype.click = function ( e ) {
	mw.loader.using( ['ext.bluespice.extjs'] ).done( function() {
		Ext.onReady( function() {
			Ext.require( 'BS.Avatars.SettingsWindow', function() {
				BS.Avatars.SettingsWindow.show();
			} );
		} );
	} );
	e.preventDefault();
	return false;
};
