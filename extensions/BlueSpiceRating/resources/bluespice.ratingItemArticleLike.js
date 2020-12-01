/**
 * @author     Patric Wirth
 * @package    BluespiceRating
 * @subpackage Rating
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

bs.rating.ItemArticleLike = function( $el, type, data ) {
	bs.rating.Item.call( this, $el, type, data );
	var me = this;
	me.makeVoteButton();
	me.makeNumVotes();
	me.makeUserVoted();

	me.getEl().append( me.$voteButton );
	me.getEl().append( me.$numVotes );
	me.getEl().append( me.$userVoted );

	if( me.userVoted() ) {
		me.$voteButton.addClass( 'uservoted' );
		me.$numVotes.addClass( 'uservoted' );
		me.$userVoted.show();
	}
	$( me.getEl() ).on(
		"click",
		".bs-rating-articlelike-button, .bs-rating-articlelike-numvotes",
		function() {
		if( !me.data.get( 'usercanmodify', false ) ) {
			return false;
		}
		me.addLoadingMask();
		me.vote( me.userVoted() ? false : 1 ).done( function( result ) {
			if( result.success === true ) {
				me.reset( result.payload.data );
				me.removeLoadingMask();
			}
		});
		return false;
	});
};
OO.inheritClass( bs.rating.ItemArticleLike, bs.rating.Item );
bs.rating.register(
	'articlelike',
	"\\BlueSpice\\Rating\\RatingItem\\ArticleLike",
	bs.rating.ItemArticleLike
);

bs.rating.ItemArticleLike.prototype.getData = function() {
	var data = bs.rating.ItemArticleLike.super.prototype.getData.apply( this );
	data.articleid = mw.config.get( 'wgArticleId', 0 );
	return data;
};

bs.rating.ItemArticleLike.prototype.reset = function( data ) {
	bs.rating.ItemArticleLike.super.prototype.reset.apply( this, [data] );
	var me = this;
	this.getEl().find('.bs-rating-articlelike-numvotes').replaceWith(
		me.makeNumVotes()
	);
	if( me.userVoted() ) {
		me.$voteButton.addClass( 'uservoted' );
		me.$numVotes.addClass( 'uservoted' );
		me.$userVoted.show();
	} else {
		me.$voteButton.removeClass( 'uservoted' );
		me.$numVotes.removeClass( 'uservoted' );
		me.$userVoted.hide();
	}
};

bs.rating.ItemArticleLike.prototype.makeVoteButton = function( data ) {
	this.$voteButton = $(
		'<span class="bs-rating-articlelike-button"></span>'
	);
	return this.$voteButton;
};

bs.rating.ItemArticleLike.prototype.makeNumVotes = function( data ) {
	this.$numVotes = $(
		'<span class="bs-rating-articlelike-numvotes">'
		+ mw.message(
			this.userVoted()
				? 'bs-rating-articlelike-uratingtextservoted'
				: 'bs-rating-articlelike-ratingtext',
			this.getVoteCount()
		).parse()
		+ '</span>'
	);
	return this.$numVotes;
};

bs.rating.ItemArticleLike.prototype.makeUserVoted = function( data ) {
	this.$userVoted = $(
		'<span class="bs-rating-articlelike-uservoted"></span>'
	).hide();
};
