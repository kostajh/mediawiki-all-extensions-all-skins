/**
 *
 * @author     Patric Wirth
 * @package    BluespiceSocial
 * @subpackage BlueSpiceSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

bs.social.EntityListMenuOptionLimit = function( key, mVal, EntityListMenu ) {
	this.limitReached = false;
	bs.social.EntityListMenuOption.call( this, key, mVal, EntityListMenu );
	var me = this;
	me.limit = EntityListMenu.entityList.getData().limit;
	me.start = EntityListMenu.entityList.getData().start;

	EntityListMenu.entityList.on(
		'loadcomplete',
		me.onEntityListLoadComplete,
		[],
		this
	);
	EntityListMenu.on(
		'filterchange',
		me.onEntityListMenuFilterChange,
		[],
		this
	);
	EntityListMenu.on(
		'optionchange',
		me.onEntityListMenuOptionChange,
		[],
		this
	);
};

OO.initClass( bs.social.EntityListMenuOptionLimit );
OO.inheritClass( bs.social.EntityListMenuOptionLimit, bs.social.EntityListMenuOption );

bs.social.EntityListMenuOptionLimit.prototype.init = function( mVal ) {
	bs.social.EntityListMenuOptionLimit.super.prototype.init.apply( this );
	if( !this.getEntityListMenu().entityList.getData().useendlessscroll ) {
		return;
	}
	this.makeEndlessScroll();
};

bs.social.EntityListMenuOptionLimit.prototype.change = function( mVal ) {
	
	this.start += mVal;
	bs.social.EntityListMenuOptionLimit.super.prototype.change.apply( this, [
		mVal
	]);
};

bs.social.EntityListMenuOptionLimit.prototype.makeEndlessScroll = function() {
	var win = $(window);
	var me = this;
	win.scroll( function() {
		if( !me.isActive() || me.limitReached ) {
			return;
		}

		var elBottom = me.getEntityListMenu().entityList.getEl().outerHeight()
			+ Math.floor(
				me.getEntityListMenu().entityList.getEl().offset().top
			);

		if ( elBottom < win.scrollTop() + win.height() + 50 ) {
			me.change( //may be a user filter in the future
				parseInt( me.getEntityListMenu().entityList.getData().limit )
			);
		}
	});
};

bs.social.EntityListMenuOptionLimit.prototype.getData = function( data ) {
	data.limit = this.limit;
	data.start = this.start;
	return data;
};

bs.social.EntityListMenuOptionLimit.prototype.onEntityListLoadComplete = function( e, response ) {
	if( !response || !response.success || this.limitReached ) {
		return;
	}
	if( response.payload.entities.length === this.limit ) {
		return;
	}
	this.limitReached = true;
};

bs.social.EntityListMenuOptionLimit.prototype.onEntityListMenuFilterChange = function( e, filter ) {
	//reset limit, besause params changed
	this.limitReached = false;
	this.limit = this.getEntityListMenu().entityList.getData().limit;
	this.start = this.getEntityListMenu().entityList.getData().start;
};
bs.social.EntityListMenuOptionLimit.prototype.onEntityListMenuOptionChange = function( e, option ) {
	if( option.key === 'limit' ) {
		return;
	}
	//reset limit, besause params changed
	this.limitReached = false;
	this.limit = this.getEntityListMenu().entityList.getData().limit;
	this.start = this.getEntityListMenu().entityList.getData().start;
};

bs.social.EntityListMenuOptions.limit = bs.social.EntityListMenuOptionLimit;