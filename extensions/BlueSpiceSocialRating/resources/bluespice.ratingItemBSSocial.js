/**
 * @author     Patric Wirth
 * @package    BlueSpiceSocial
 * @subpackage BlueSpiceSocialRating
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

bs.rating.ItemBSSocial = function( $el, type, data ) {
	bs.rating.Item.call( this, $el, type, data );
	var me = this;
	me.makeVoteButton();
	me.makeNumVotes();

	me.getEl().append( me.$voteButton );
	me.getEl().append( me.$numVotes );

	if( me.userVoted() ) {
		me.$voteButton.addClass( 'uservoted' );
		me.$numVotes.addClass( 'uservoted' );
	}
	$( me.getEl() ).on(
		"click",
		".bs-rating-bssocial-button, .bs-rating-bssocial-numvotes",
		function() {
			if( !me.data.get( 'usercanmodify', false ) ) {
				return false;
			}
			me.addLoadingMask();
			me.vote( me.userVoted() ? false : 1 ).done(function( result ) {
				if( result.success === true ) {
					me.reset( result.payload.data );
					me.removeLoadingMask();
				}
			});
		return false;
	});
};
OO.inheritClass( bs.rating.ItemBSSocial, bs.rating.Item );
bs.rating.register( 'bssocial', 'RatingItemBSSocial', bs.rating.ItemBSSocial );

bs.rating.ItemBSSocial.prototype.reset = function( data ) {
	bs.rating.ItemBSSocial.super.prototype.reset.apply( this, [data] );
	var me = this;
	this.getEl().find('.bs-rating-bssocial-numvotes').replaceWith(
		me.makeNumVotes()
	);
	if( me.userVoted() ) {
		me.$voteButton.addClass( 'uservoted' );
		me.$numVotes.addClass( 'uservoted' );
	} else {
		me.$voteButton.removeClass( 'uservoted' );
		me.$numVotes.removeClass( 'uservoted' );
	}
};

bs.rating.ItemBSSocial.prototype.makeVoteButton = function( data ) {
	this.$voteButton = $(
		''
	);
	return this.$voteButton;
};

bs.rating.ItemBSSocial.prototype.makeNumVotes = function( data ) {
	this.$numVotes = $(
		'<a class="bs-rating-bssocial-numvotes"><span>'
		+ mw.message(
			'bs-socialrating-aftercontent-ratingtext',
			this.getVoteCount()
		).parse()
		+ '</a></span>'
	);
	return this.$numVotes;
};
