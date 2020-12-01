/**
 *
 * @author     Josef Konrad <konrad@hallowelt.com>
 * @package    BluespiceSocial
 * @subpackage BlueSpiceSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

bs.social.EntityListMenu.Button.EntitySpawner = function( EntityListMenu ) {
	bs.social.EntityListMenu.Button.call( this, EntityListMenu );
	OO.EventEmitter.call( this );
	var me = this;
	this.EntityListMenu.entityList.on( 'loadcomplete', function() {
		me.makeActionMenu();
	});
};

OO.initClass( bs.social.EntityListMenu.Button.EntitySpawner );
OO.inheritClass( bs.social.EntityListMenu.Button.EntitySpawner, bs.social.EntityListMenu.Button );

bs.social.EntityListMenu.Button.EntitySpawner.prototype.init = function() {
	bs.social.EntityListMenu.Button.EntitySpawner.super.prototype.init.apply( this );
};

bs.social.EntityListMenu.Button.EntitySpawner.prototype.makeButton = function() {
	bs.social.EntityListMenu.Button.EntitySpawner.super.prototype.makeButton.apply( this );

	var tpl = this.getTemplate();

	this.$button = $(tpl.render( this.getTemplateVars() ));

	this.$button.on( 'click', this.onClick );

	this.makeActionMenu();
};

bs.social.EntityListMenu.Button.EntitySpawner.prototype.onClick = function() {
	$(this).find(
		'.bs-social-entityspawner-actions-content'
	).first().show();
};

bs.social.EntityListMenu.Button.EntitySpawner.prototype.getTemplateName = function() {
	return 'BlueSpiceSocial.EntitySpawner.mustache';
};

bs.social.EntityListMenu.Button.EntitySpawner.prototype.getTemplateVars = function() {
	var params = bs.social.EntityListMenu.Button.EntitySpawner.super.prototype.getTemplateVars.apply( this );
	params['classes'] = 'bs-social-entityspawner';
	params['tooltip'] = mw.message( 'bs-social-entityspawner-button-tooltip' ).plain()

	return params;
};

bs.social.EntityListMenu.Button.EntitySpawner.prototype.makeActionMenu = function() {
	var $content = this.$button.find(
		'.bs-social-entityspawner-actions-content'
	).first();

	$content.hide().empty();
	if( this.EntityListMenu.entityList.getData().showentityspawner === false ) {
		this.$button.hide();
		return;
	}
	var spawnerPermissions = [];

	spawnerPermissions = mw.config.get(
		'bsgSocialUserSpawnerEntities',
		spawnerPermissions
	);

	for(var type in bs.social.config ) {
		if( !bs.social.config[type].IsSpawnable ) {
			continue;
		}
		if( spawnerPermissions.indexOf( type ) === -1 ) {
			continue;
		}
		var typeAllowed = false;
		for( var i = 0; i < this.EntityListMenu.getData().filter.length; i++ ) {
			var filter = this.EntityListMenu.getData().filter[i];
			if( !filter.property || filter.property !== 'type' ) {
				continue;
			}
			if( !filter.value || filter.value.length < 1 ) {
				break;
			}
			for( var y = 0; y < filter.value.length; y ++ ) {
				if( filter.value[y] !== type ) {
					continue;
				}
				typeAllowed = true;
				break;
			}
		}
		if( !typeAllowed ) {
			continue;
		}

		var $element = $(
			'<a href="#" data-type="' + type + '" class="bs-social-entityspawner-' + type + '">'
			+ mw.message( bs.social.config[type].TypeMessageKey ).plain()
			+ '</a>'
		);
		var me = this;
		$element.on( 'click', function( e ) {
			me.onSpawnerActionClick( e );
			return false;
		} );

		$content.append( $element );
	};

	$content.on( "mouseleave", function() {
		$(this).hide();
	});

	var active = $content.children().length > 0;
	if( !active ) {
		this.$button.hide();
		return;
	}
	this.$button.show();
	return null;
};

bs.social.EntityListMenu.Button.EntitySpawner.prototype.onSpawnerActionClick = function( e ) {
	var type = $( e.target ).attr( 'data-type' );
	if( !type || type === '' ) {
		return false;
	}
	var me = this;
	var text = '';
	var preload = this.EntityListMenu.entityList.getData().preloadtitles[type];
	var render = function( preloadtext ) {
		var tpl = new bs.social.EntityOutput({
			'type': type,
			'ownerid': mw.config.get( 'wgUserId', 0 ),
			'wasSpawned': true,
			'preload': preload ? preload : '',
			'text' : preloadtext || text
		});

		var $EL = tpl.render( 'Default' );

		var li = '<li class="bs-social-entityspawner-new">' + $( $EL[0] ).prop( 'outerHTML' ) + '</li>';
		$( 'ul.bs-social-entitylist' )
			.first().prepend(
			li
		);

		bs.social.init( $(li).find('.bs-social-entity') );

		//sometimes teh button gets lost... especially in IE
		if( me.$button ) {
			me.$button.find( '.bs-social-entityspawner-actions-content' ).first().hide();
		}
	};
	this.EntityListMenu.entityList.showLoadMask();
	if( !preload || preload === '' ) {
		render();
		e.preventDefault();
		this.EntityListMenu.entityList.hideLoadMask()
		return false;
	}
	var api = new mw.Api();
	api.get( {
		action: 'query',
		titles: preload,
		prop: 'revisions',
		rvprop: 'content',
		indexpageids : ''
	} ).done( function( resp, jqXHR ){
		me.EntityListMenu.entityList.hideLoadMask();
		var preloadtext = '';
		var pageId = resp.query.pageids[0];
		if( pageId ) {
			var pageInfo = resp.query.pages[pageId];
			if( pageInfo && pageInfo.revisions && pageInfo.revisions[0] && pageInfo.revisions[0]['*'] ) {
				preloadtext = pageInfo.revisions[0]['*'];
			}
		}
		render( preloadtext );
	});

	e.preventDefault();
	return false;
};