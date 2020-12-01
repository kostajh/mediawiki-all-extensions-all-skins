/**
 *
 * @author     Josef Konrad <konrad@hallowelt.com>
 * @package    BluespiceSocial
 * @subpackage BlueSpiceSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

bs.social = bs.social || {};
bs.social.EntityListMenu = bs.social.EntityListMenu || {};

bs.social.EntityListMenu.Button = function( EntityListMenu ) {
	OO.EventEmitter.call( this );
	var me = this;
	me.EntityListMenu = EntityListMenu;
	me.$button = null;
};

OO.initClass( bs.social.EntityListMenu.Button );
OO.mixinClass( bs.social.EntityListMenu.Button, OO.EventEmitter );

bs.social.EntityListMenu.Button.prototype.init = function() {
	this.EntityListMenu.on( 'registerbutton', this.registerButton, [], this );
};

bs.social.EntityListMenu.Button.prototype.registerButton = function() {
	this.makeButton();
	this.EntityListMenu.buttons.push( this.$button );
};

bs.social.EntityListMenu.Button.prototype.makeButton = function() {
	var tpl = this.getTemplate();
	var me = this;
	this.$button = $(tpl.render( this.getTemplateVars() ));

	this.$button.on(
		'click',
		me.onClick.bind( this )
	);
};

bs.social.EntityListMenu.Button.prototype.getTemplate = function() {
	return mw.template.get(
		'ext.bluespice.social.timeline.templates',
		this.getTemplateName()
	);
};

bs.social.EntityListMenu.Button.prototype.getTemplateVars = function() {
	return {
		classes: '',
		tooltip: ''
	};
};

bs.social.EntityListMenu.Button.prototype.getTemplateName = function() {
	return 'BlueSpiceSocial.EntityListMenuButton.mustache';
};

bs.social.EntityListMenu.Button.prototype.onClick = function() {
	console.log('Append this');
};