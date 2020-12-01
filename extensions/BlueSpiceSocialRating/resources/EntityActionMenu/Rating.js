/*
 * @author     Stefan Kühn
 * @package    BluespiceSocial
 * @subpackage BlueSpiceSocial
 * @copyright  Copyright (C) 2020 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

bs.social = bs.social || {};
bs.social.EntityActionMenuRating = bs.social.EntityActionMenu || {};

bs.social.EntityActionMenuRating.Rating = function ( entityActionMenu, data ) {
    OO.EventEmitter.call( this );
    var me = this;
    me.data = data || {};
    me.entityActionMenu = entityActionMenu;
    me.$element = null;
    me.$element = $( '<li class="dropdown-item bs-rating bs-rating-bssocial" data-type="bssocial" data-item=' + me.data['data-item'] + '></li>' );

    me.priority = 70;
};

OO.initClass( bs.social.EntityActionMenuRating.Rating );
OO.mixinClass( bs.social.EntityActionMenuRating.Rating, OO.EventEmitter );