/**
 *
 * @author     Patric Wirth
 * @package    BluespiceSocial
 * @subpackage BlueSpiceSocialRating
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

$( document ).bind( 'BSSocialEntityInit', function( event, Entity, $el, type, data ) {
	bs.rating.init();
});

$( document ).bind( 'BSSocialEntityActionMenuInit', function( event, EntityActionMenu, $el ) {
	EntityActionMenu.classes.rating = bs.social.EntityActionMenuRating.Rating;
});
