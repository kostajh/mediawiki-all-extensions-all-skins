<?php
/**
 * The UserPageEditProtection extension to MediaWiki allows to restrict the edit
 * access to user pages.
 *
 * @link https://www.mediawiki.org/wiki/Extension:UserPageEditProtection Homepage
 * @link https://phabricator.wikimedia.org/diffusion/EUPE/browse/master/README.md Documentation
 * @link https://www.mediawiki.org/wiki/Extension_talk:UserPageEditProtection Support
 * @link https://phabricator.wikimedia.org/maniphest/task/edit/form/1/ Issue tracker
 * @link https://phabricator.wikimedia.org/diffusion/EUPE/repository/master/ Source Code
 * @link https://github.com/wikimedia/mediawiki-extensions-UserPageEditProtection/releases Downloads
 *
 * @file
 * @ingroup Extensions
 * @package MediaWiki
 *
 * @author Lisa Ridley (lhridley/hoggwild5)
 * @author Eric Gingell (egingell)
 * @author Karsten Hoffmeyer (kghbln)
 *
 * @copyright Copyright (C) 2007, Lisa Ridley
 *
 * @license GPL-2.0-or-later
 */

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'UserPageEditProtection' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['UserPageEditProtection'] = __DIR__ . '/i18n';
	// phpcs:ignore Generic.Files.LineLength.TooLong
	$wgExtensionMessagesFiles['UserPageEditProtectionAlias'] = __DIR__ . '/UserPageEditProtection.alias.php';
	wfWarn(
		'Deprecated PHP entry point used for the UserPageEditProtection extension. ' .
		'Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the UserPageEditProtection extension requires MediaWiki 1.29+' );
}
