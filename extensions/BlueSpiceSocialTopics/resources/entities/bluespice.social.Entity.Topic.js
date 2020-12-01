/**
 * Js for Rating extension
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    BluespiceSocial
 * @subpackage BlueSpiceSocialTopics
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

bs.social.EntityTopic = function( $el, type, data ) {
	bs.social.EntityText.call( this, $el, type, data );
	var me = this;
};
OO.inheritClass( bs.social.EntityTopic, bs.social.EntityText );

bs.social.EntityTopic.prototype.reset = function( data ) {
	return bs.social.EntityTopic.super.prototype.reset.apply( this, [data] );
};

bs.social.EntityTopic.prototype.makeEditor = function() {
	return new bs.social.EntityEditorTopic(
		this.getEditorConfig(),
		this
	);
};

bs.social.EntityTopic.static.name = "\\BlueSpice\\Social\\Topics\\Entity\\Topic";
bs.social.factory.register( bs.social.EntityTopic );