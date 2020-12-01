/**
 *
 * @author     Patric Wirth
 * @package    BluespiceSocial
 * @subpackage BSSocialComments
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

bs.social.EntityComment = function( $el, type, data ) {
	bs.social.EntityText.call( this, $el, type, data );
	var me = this;
};
OO.initClass( bs.social.EntityComment );
OO.inheritClass( bs.social.EntityComment, bs.social.EntityText );

bs.social.EntityComment.static.name = "\\BlueSpice\\Social\\Comments\\Entity\\Comment";
bs.social.factory.register( bs.social.EntityComment );

bs.social.EntityComment.prototype.save = function( newdata ) {
	var $dfd = bs.social.EntityComment.super.prototype.save.apply( this, arguments );
	var me = this;
	$dfd.then( function() {
		if( !me.getData().parentid || me.getData().parentid < 1 ) {
			return;
		}

		var $entities = $( 'div.bs-social-entity[data-id="' + me.getData().parentid + '"] ');
		if ( $entities.length < 1 ) {
			return;
		}
		var entity = bs.social.newFromEl( $entities.first() );

		bs.api.tasks.execSilent(
			me.getTaskApi(),
			'getEntity',
			entity.getData()
		).done( function( response ) {
			if( !response.success ) {
				if( response.message && response.message !== '' ) {
					OO.ui.alert( response.message );
				}
				return;
			}
			var count = JSON.parse( response.payload.entity ).commentcount

			$entities.each( function() {
				var entity = bs.social.newFromEl( $( this ) );
				var $afterContent = entity.getContainer( entity.AFTER_CONTENT_CONTAINER );
				if ( $afterContent.length < 1 ) {
					return;
				}
				var $comments = $afterContent.find(
					'.bs-social-entityaftercontent-comment'
				).first()
				if ( $comments.length < 1 ) {
					return;
				}
				if ( $comments.find( '.bs-social-count-short' ).length > 0 ) {
					$comments.find( '.bs-social-count-short' ).html( count );
				}
				if ( $comments.find( '.bs-social-count-default' ).length > 0 ) {
					var textCount = mw.message(
						'bs-socialcomments-commenttext',
						count
					).parse();
					$comments.find( '.bs-social-count-default' ).html( textCount );
				}
			} );
		})
	} );
	return $dfd;
}
