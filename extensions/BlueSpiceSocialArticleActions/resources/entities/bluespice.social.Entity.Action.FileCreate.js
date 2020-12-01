/**
 *
 * @author     Patric Wirth
 * @package    BluespiceSocial
 * @subpackage BSSocialActionMW
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

bs.social.EntityFileCreate = function( $el, type, data ) {
	bs.social.EntityAction.call( this, $el, type, data );
	var me = this;
};
OO.initClass( bs.social.EntityFileCreate );
OO.inheritClass( bs.social.EntityFileCreate, bs.social.EntityAction );

bs.social.EntityFileCreate.static.name = "\\BlueSpice\\Social\\ArticleActions\\Entity\\ActionFileCreate";
bs.social.factory.register( bs.social.EntityFileCreate );
