/**
 *
 * @author     Patric Wirth
 * @package    BluespiceSocial
 * @subpackage BSSocialComments
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

$( document ).bind( 'BSSocialEntityInit', function( event, Entity, $el, type, data ) {
	if( !Entity.getConfig().CanHaveChildren ) {
		return;
	}
	var $anchor = Entity.getContainer( Entity.AFTER_CONTENT_CONTAINER ).find(
		'.bs-social-entityaftercontent-comment'
	);

	$anchor.on( 'click', function() {
		var $entityList = Entity.getContainer( Entity.CHILDREN_CONTAINER ).find(
			'.bs-social-entitylist'
		).first();
		if( $entityList.length < 1 ) {
			return;
		}
		$entityList.toggle();
		$entityList.siblings(
			'.bs-social-entitylist-more:not(.forcehidden), .bs-social-entitylist-menu, .bs-social-entitylist-headline'
		).toggle()
	});
});