/*
* @author     Stefan Kühn
* @package    BluespiceSocial
* @subpackage BlueSpiceSocial
* @copyright  Copyright (C) 2020 Hallo Welt! GmbH, All rights reserved.
* @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
*/

bs.social = bs.social || {};
bs.social.EntityActionMenu = bs.social.EntityActionMenu || {};

bs.social.EntityActionMenu.Delete = function ( entityActionMenu ) {
	OO.EventEmitter.call( this );
	var me = this;
	me.entityActionMenu = entityActionMenu;
	me.$element = null;
	var key = me.entityActionMenu.entity.data.get( 'archived' )
		? 'bs-social-entityaction-undelete'
		: 'bs-social-entityaction-delete';
	me.$element = $( '<li><a class="dropdown-item bs-social-entity-action-delete">'
		+ '<span>' + mw.message( key ).plain() + '</span>'
		+ '</a></li>'
	);

	me.$element.on( 'click', function( e ) { me.click( e ); } );
	me.priority = 10;
};

OO.initClass( bs.social.EntityActionMenu.Delete );
OO.mixinClass( bs.social.EntityActionMenu.Delete, OO.EventEmitter );

bs.social.EntityActionMenu.Delete.prototype.click = function ( e ) {
	var me = this;
	var msg = this.entityActionMenu.entity.getConfig().DeleteConfirmMessageKey;
	if( this.entityActionMenu.entity.data.get( 'archived' ) ) {
		msg = this.entityActionMenu.entity.getConfig().UnDeleteConfirmMessageKey;
	}
	OO.ui.confirm( mw.message( msg ).plain() ).done( function ( confirmed ) {
		if ( confirmed ) {
			me.entityActionMenu.entity.delete();
		}
	});

	e.preventDefault();
	return false;
};
