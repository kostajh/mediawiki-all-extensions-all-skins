/**
 *
 * @author     Patric Wirth
 * @package    BluespiceSocial
 * @subpackage BlueSpiceSocialProfile
 * @copyright  Copyright (C) 2020 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

$( document ).bind( 'BSSocialEntityActionMenuInit', function( event, EntityActionMenu, $el ) {
	EntityActionMenu.classes.changeimage = bs.social.EntityActionMenu.ChangeImage;
	EntityActionMenu.classes.editprofilefields = bs.social.EntityActionMenu.EditProfileFields;
	EntityActionMenu.classes.edituserpage = bs.social.EntityActionMenu.EditUserPage;
});
