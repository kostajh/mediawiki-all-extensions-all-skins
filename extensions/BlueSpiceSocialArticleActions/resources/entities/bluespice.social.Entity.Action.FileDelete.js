/**
 *
 * @author     Patric Wirth
 * @package    BluespiceSocial
 * @subpackage BSSocialActionMW
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

bs.social.EntityFileDelete = function( $el, type, data ) {
	bs.social.EntityAction.call( this, $el, type, data );
	var me = this;
};
OO.initClass( bs.social.EntityFileDelete );
OO.inheritClass( bs.social.EntityFileDelete, bs.social.EntityAction );

bs.social.EntityFileDelete.static.name = "\\BlueSpice\\Social\\ArticleActions\\Entity\\ActionFileDelete";
bs.social.factory.register( bs.social.EntityFileDelete );
