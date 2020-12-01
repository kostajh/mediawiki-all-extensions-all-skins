/**
 *
 * @author     Patric Wirth
 * @package    BluespiceSocial
 * @subpackage BlueSpiceSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

bs.social.EntityAction = function( $el, type, data ) {
	bs.social.EntityText.call( this, $el, type, data );
	var me = this;
};
OO.initClass( bs.social.EntityAction );
OO.inheritClass( bs.social.EntityAction, bs.social.EntityText );
