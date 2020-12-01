/**
 *
 * @author     Patric Wirth
 * @package    BluespiceSocial
 * @subpackage BSSocialActionMW
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

bs.social.EntityArticleSave = function( $el, type, data ) {
	bs.social.EntityAction.call( this, $el, type, data );
	var me = this;
};
OO.initClass( bs.social.EntityArticleSave );
OO.inheritClass( bs.social.EntityArticleSave, bs.social.EntityAction );

bs.social.EntityArticleSave.static.name = "\\BlueSpice\\Social\\ArticleActions\\Entity\\ActionArticleSave";
bs.social.factory.register( bs.social.EntityArticleSave );