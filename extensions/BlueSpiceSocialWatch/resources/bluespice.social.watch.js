/**
 *
 * @author     Patric Wirth
 * @package    BluespiceSocial
 * @subpackage BlueSpiceSocialWatch
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */
$( document ).bind( 'BSSocialEntityActionMenuInit', function( event, EntityActionMenu ) {
	EntityActionMenu.classes.watch = bs.social.EntityActionMenuWatch.Watch;
});
