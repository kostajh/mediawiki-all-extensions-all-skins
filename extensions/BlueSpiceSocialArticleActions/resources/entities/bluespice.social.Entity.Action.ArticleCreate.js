/**
 *
 * @author     Patric Wirth
 * @package    BluespiceSocial
 * @subpackage BSSocialActionMW
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

bs.social.EntityArticleCreate = function( $el, type, data ) {
	bs.social.EntityAction.call( this, $el, type, data );
	var me = this;
};
OO.initClass( bs.social.EntityArticleCreate );
OO.inheritClass( bs.social.EntityArticleCreate, bs.social.EntityAction );

bs.social.EntityArticleCreate.static.name = "\\BlueSpice\\Social\\ArticleActions\\Entity\\ActionArticleCreate";
bs.social.factory.register( bs.social.EntityArticleCreate );
