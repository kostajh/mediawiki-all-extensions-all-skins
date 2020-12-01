/**
 *
 * @author     Patric Wirth
 * @package    BluespiceSocial
 * @subpackage BlueSpiceSocialResolve
 * @copyright  Copyright (C) 2018 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

$( document ).bind( 'BSSocialEntityActionMenuInit', function( event, EntityActionMenu ) {
	EntityActionMenu.classes.resolved = bs.social.EntityActionMenuResolved.Resolved;
});