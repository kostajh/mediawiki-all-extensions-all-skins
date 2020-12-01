/**
 * Js for Rating extension
 *
 * @author     Patric Wirth
 * @package    BluespiceSocial
 * @subpackage BSSocialMicroBlog
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

bs.social.EntityMicroBlog = function( $el, type, data ) {
	bs.social.EntityText.call( this, $el, type, data );
	var me = this;
};
OO.initClass( bs.social.EntityMicroBlog );
OO.inheritClass( bs.social.EntityMicroBlog, bs.social.EntityText );

bs.social.EntityMicroBlog.prototype.reset = function( data ) {
	return bs.social.EntityMicroBlog.super.prototype.reset.apply( this, [data] );
};

bs.social.EntityMicroBlog.static.name = "\\BlueSpice\\Social\\MicroBlog\\Entity\\MicroBlog";
bs.social.factory.register( bs.social.EntityMicroBlog );