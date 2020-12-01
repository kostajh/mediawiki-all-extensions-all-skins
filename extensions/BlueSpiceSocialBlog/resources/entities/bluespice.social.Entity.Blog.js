/**
 * @author     Patric Wirth
 * @package    BlueSocial
 * @subpackage BlueSocialBlog
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 */

bs.social.EntityBlog = function( $el, type, data ) {
	this.$moreButton = $el.find(
		'.bs-social-entity-content-more-button'
	).first();
	bs.social.EntityText.call( this, $el, type, data );
	var me = this;
};
OO.initClass( bs.social.EntityBlog );
OO.inheritClass( bs.social.EntityBlog, bs.social.EntityText );

bs.social.EntityBlog.prototype.init = function() {
	bs.social.EntityBlog.super.prototype.init.apply( this );
	var me = this;

	if( !mw.config.get( 'bsBSSocialUseBlogTeaser', true ) || me.editmode || !me.exists() ) {
		this.hideMore();
		return;
	} else {
		this.showMore();
	}

	if( me.$moreButton && me.$moreButton.length > 0 ) {
		var msgMore = 'bs-socialblog-entityblog-content-more-btn-more';
		var msgLess = 'bs-socialblog-entityblog-content-more-btn-less';
		if( me.$moreButton.hasClass( 'extended' ) ) {
			me.$moreButton.text( mw.message( msgLess ).plain() );
		} else {
			me.$moreButton.text( mw.message( msgMore ).plain() );
		}
		me.$moreButton.on( 'click', function( e ) {
			if( me.$moreButton.hasClass( 'extended' ) ) {
				me.$moreButton.removeClass( 'extended' );
				me.$moreButton.text(  mw.message( msgMore ).plain() );
				me.contract();
				e.stopPropagation();
				return false;
			} else {
				me.$moreButton.addClass( 'extended' );
				me.$moreButton.text( mw.message( msgLess ).plain() );
				me.expand();
				e.stopPropagation();
				return false;
			}
		});
	}
};
bs.social.EntityBlog.prototype.expand = function() {
	var $content = this.getEl().find( '.bs-social-entity-content' ).first();

	$content.children( 'div' ).first().fadeOut( 'slow' );
	var $newContent = '<div>' + this.data.get( 'parsedtext', '' ) + '</div>';
	$content.html( $($newContent).prop( 'outerHTML' ) ).fadeIn( 'slow' );
};
bs.social.EntityBlog.prototype.contract = function() {
	var $content = this.getEl().find( '.bs-social-entity-content' ).first();

	$content.children( 'div' ).first().fadeOut( 'slow' );
	var teaser = this.data.get( 'teasertext', '' ).replace(
		new RegExp('\r?\n','g'),
		'<br />'
	);
	var $newContent = '<div>' + teaser + '</div>';
	//better user experience.. when the blog entry is larger than the window
	//scoll to the top of it instead of it just disapearing ;)
	if( $(window).scrollTop() > this.getEl().offset().top ) {
		$('html, body').animate({
			scrollTop: this.getEl().offset().top
		}, 500);
	}
	$content.html( $($newContent).prop( 'outerHTML' ) ).fadeIn( 'slow' );
};
bs.social.EntityBlog.prototype.hideMore = function() {
	if( this.$moreButton && this.$moreButton.length > 0 ) {
		this.$moreButton.parents( 'div' ).first().hide();
	}
};
bs.social.EntityBlog.prototype.showMore = function() {
	if( this.$moreButton && this.$moreButton.length > 0 ) {
		this.$moreButton.parents( 'div' ).first().show();
	}
};
bs.social.EntityBlog.prototype.reset = function( data ) {
	return bs.social.EntityBlog.super.prototype.reset.apply( this, [data] );
};
bs.social.EntityBlog.prototype.makeEditor = function() {
	return new bs.social.EntityEditorBlog(
		this.getEditorConfig(),
		this
	);
};

bs.social.EntityBlog.static.name = "\\BlueSpice\\Social\\Blog\\Entity\\Blog";
bs.social.factory.register( bs.social.EntityBlog );