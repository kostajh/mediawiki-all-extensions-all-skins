/**
 *
 * @author     Patric Wirth
 * @package    BluespiceSocial
 * @subpackage BlueSpiceSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

bs.social.EntityListMore = function( $el, entityList ) {
	bs.social.El.call( this, $el );
	var me = this;
	me.limit = entityList.getData().limit;
	me.start = entityList.getData().start;
	me.BUTTON_SECTION = 'bs-social-entitylist-more-section-button';
	me.entityList = entityList;
	me.data = {};
	me.makeUiID();

	entityList.on(
		'loadcomplete',
		me.onEntityListLoadComplete,
		[],
		this
	);

	if( entityList.menu ) {
		entityList.menu.on(
			'filterchange',
			me.onEntityListMenuFilterChange,
			[],
			this
		);
		entityList.menu.on(
			'optionchange',
			me.onEntityListMenuOptionChange,
			[],
			this
		);
	}

	me.$button = me.getEl().find( '.' + me.BUTTON_SECTION ).first();
	me.$button.on( 'click', function( e ) {
		if( !me.entityList.getData().usemorescroll ) {
			return true;
		}
		me.change( parseInt( me.entityList.getData().limit ) );
		return false;
	});
	$(document).trigger( 'BSSocialEntityListMoreInit', [ me, $el ] );

};
OO.initClass( bs.social.EntityListMore );
OO.inheritClass( bs.social.EntityListMore, bs.social.El );

bs.social.EntityListMore.prototype.change = function( val ) {
	this.start += val;
	this.entityList.getEntities( 'add', this.getData( {} ) )
};

bs.social.EntityListMore.prototype.getData = function( data ) {
	data.limit = this.limit;
	data.start = this.start;
	return data;
};

bs.social.EntityListMore.prototype.onEntityListMenuFilterChange = function( e, filter ) {
	//reset limit, besause params changed
	this.getEl().show();
	this.getEl().removeClass( 'forcehidden' );
	this.limit = this.entityList.getData().limit;
	this.start = this.entityList.getData().start;
};
bs.social.EntityListMore.prototype.onEntityListMenuOptionChange = function( e, option ) {
	if( option.key === 'limit' ) {
		return;
	}
	//reset limit, besause params changed
	this.getEl().show();
	this.getEl().removeClass( 'forcehidden' );
	this.limit = this.entityList.getData().limit;
	this.start = this.entityList.getData().start;
};
bs.social.EntityListMore.prototype.onEntityListLoadComplete = function( e, response ) {
	if( !response || !response.success || this.limitReached ) {
		return;
	}
	if( !this.entityList.getData().usemorescroll ) {
		return;
	}
	if( response.payload.entities.length === this.limit ) {
		return;
	}
	this.getEl().hide();
	this.getEl().addClass( 'forcehidden' );
};