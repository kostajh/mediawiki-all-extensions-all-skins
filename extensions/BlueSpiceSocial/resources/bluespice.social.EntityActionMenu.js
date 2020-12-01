/**
 *
 * @author     Stefan Kühn
 * @package    BluespiceSocial
 * @subpackage BlueSpiceSocial
 * @copyright  Copyright (C) 2020 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

bs.social.EntityActionMenu = function( $el, entity ) {
	bs.social.El.call( this, $el );
	var me = this;
	me.entity = entity;
	me.actions = me.entity.data.get( 'actions', {} );
	me.buttons = {};
	me.showEntities = false;

	me.classes = {
		edit: bs.social.EntityActionMenu.Edit,
		delete: bs.social.EntityActionMenu.Delete
	};

	me.$actionsContainer = me.entity.getContainer( me.entity.ACTIONS_CONTAINER );
	me.$dropDownContent = me.$actionsContainer.find( '.bs-social-entity-actions-menu-content' );
	me.$dropDownButton = me.$actionsContainer.find( '.bs-social-entity-actions-menu' );
	me.$highPrioElement = me.$actionsContainer.find( '.bs-social-entity-actions-menu-prio' );
	me.$actionsContainer.hide();
	me.$dropDownButton.hide();
	$(document).trigger( 'BSSocialEntityActionMenuInit', [ me, $el ] );

	var priorityActions = []
	for( var i in me.actions ) {
		if( !me.classes[i] ) {
			continue;
		}
		me.buttons[i] = new me.classes[i]( this, me.actions[i] );
		priorityActions.push( me.buttons[i] );
	}

	priorityActions.sort( function( a, b ) {
		return a.priority < b.priority;
	});

	var first = true;
	for( var i = 0; i < priorityActions.length; i++ ) {
		if ( !priorityActions[i].$element || priorityActions[i].$element === '' ) {
			continue;
		}
		if ( first ) {
			me.$highPrioElement.append( priorityActions[i].$element );
			me.$actionsContainer.show();
			first = false;
			continue;
		}
		me.$dropDownContent.append( priorityActions[i].$element );
		me.$dropDownButton.show();
	}

	return;
};


OO.initClass( bs.social.EntityActionMenu );
OO.inheritClass( bs.social.EntityActionMenu, bs.social.El );