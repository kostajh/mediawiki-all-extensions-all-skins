/**
 *
 * @author     Patric Wirth
 * @package    BluespiceSocial
 * @subpackage BlueSpiceSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

bs.social.El = function( $el ) {
	OO.EventEmitter.call( this );
	var me = this;
	me.$el = $el;
};
OO.initClass( bs.social.El );
OO.mixinClass( bs.social.El, OO.EventEmitter );

bs.social.El.prototype.makeUiID = function() {
	var uiID = bs.social.getUiID( this.$el );
	if( uiID ) {
		return uiID;
	}
	if( !this.$el.attr('id') || this.$el.attr('id').length < 1 ) {
		this.$el.attr('id', bs.social.generateUniqueId() );
	}
	return bs.social.getUiID( this.$el );
};

bs.social.El.prototype.getEl = function() {
	return this.$el;
};

bs.social.El.prototype.replaceEL = function( El ) {
	this.insertAfterEL( El ).removeEL();
	this.$el = $( El );
	return this;
};

bs.social.El.prototype.insertAfterEL = function( El ) {
	//do not use jquery right away!
	$(El).hide().insertAfter( this.getEl() ).fadeIn();
	bs.social.init();
	return this;
};

bs.social.El.prototype.insertLastChild = function( El ) {
	this.getEl().append( El );
	return this;
};

bs.social.El.prototype.insertBeforeEL = function( El ) {
	//do not use jquery right away!
	var li = document.createElement('li');
	li.innerHTML = El;
	this.getEl().hide();
	$(li).insertBefore( this.getEl() );
	$(li).find(".bs-social-entity").each( function() {
		//init all and have fun!
		bs.social.newFromEl( $(this) );
	});
	this.getEl().fadeIn();
	return this;
};

bs.social.El.prototype.insertFirstChild = function( El ) {
	this.getEl().prepend( El );
	return this;
};

bs.social.El.prototype.removeEL = function() {
	this.getEl().remove();
	delete bs.social.entityStore[this.uiID];
	return this;
};
bs.social.El.prototype.hide = function() {
	this.getEl().hide();
	return this;
};
bs.social.El.prototype.show = function() {
	this.getEl().show();
	return this;
};
bs.social.El.prototype.showLoadMask = function() {
	this.$loadMask = this.$loadMask || $(
		'<div class="bs-social-loadmask"><div class="loader-indicator loading"><div class="loader-indicator-inner"></div></div>'
	).appendTo( 'body' );
	var $element = this.getEl();
	this.$loadMask.css({
		width: $( $element[0] ).outerWidth() + 'px',
		position: 'absolute',
		top: $element.offset().top,
		left: $element.offset().left,
		right: $element.offset().right,
		bottom: $element.offset().bottom
	});
	this.$loadMask.height( $element.outerHeight() );
	this.$loadMask.fadeIn();
	return this;
};
bs.social.El.prototype.hideLoadMask = function() {
	if( this.$loadMask ) {
		this.$loadMask.fadeOut();
	}
	return this;
};
