<?php
/**
 * MultimediaPlayer - Plays a list of multimedia files
 *
 * To activate this extension, add the following into your LocalSettings.php file:
 * require_once( "$IP/extensions/MultimediaPlayer/MultimediaPlayer.php" );
 *
 * @ingroup Extensions
 * @author Ike Hecht
 * @version 0.3.3
 * @link https://www.mediawiki.org/wiki/Extension:MultimediaPlayer Documentation
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	echo ( "This is an extension to the MediaWiki package and cannot be run standalone.\n" );
	die( -1 );
}

// Extension credits that will show up on Special:Version
$wgExtensionCredits['parserhook'][] = array(
	'path' => __FILE__,
	'name' => 'MultimediaPlayer',
	'version' => '0.3.3',
	'author' => 'Ike Hecht for [http://www.wikiworks.com/ WikiWorks]',
	'url' => 'https://www.mediawiki.org/wiki/Extension:MultimediaPlayer',
	'descriptionmsg' => 'multimediaplayer-desc',
);

$wgResourceModules['ext.MultimediaPlayer'] = array(
	'styles' => 'css/multimediaplayer.css',
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'MultimediaPlayer',
);

$wgExtensionMessagesFiles['MultimediaPlayer'] = __DIR__ . '/MultimediaPlayer.i18n.magic.php';
$wgMessagesDirs['MultimediaPlayerMagic'] = __DIR__ . '/i18n';

$wgAutoloadClasses['MultimediaPlayer'] = __DIR__ . '/MultimediaPlayer.class.php';
$wgAutoloadClasses['MultimediaPlayerItem'] = __DIR__ . '/MultimediaPlayerItem.class.php';
$wgAutoloadClasses['MultimediaPlayerContainer'] = __DIR__ . '/MultimediaPlayerContainer.class.php';
$wgAutoloadClasses['MultimediaPlayerSources'] = __DIR__ . '/MultimediaPlayerSources.class.php';
$wgAutoloadClasses['MultimediaPlayerHooks'] = __DIR__ . '/MultimediaPlayer.hooks.php';

$wgHooks['ParserFirstCallInit'][] = 'MultimediaPlayerHooks::onParserFirstCallInit';
$wgHooks['BeforePageDisplay'][] = 'MultimediaPlayerHooks::onBeforePageDisplay';

/**
 * Set in LocalSettings.php
 * Expects an array where:
 *	key is the name of a source, to be included as the first argument to the
 *		#multimediaitem parser function
 *	value is the code to render that source, with $1 as a placeholder for the item's id,
 *		which is also the second argument to the #multimediaitem parser function
 */
$wgMultimediaPlayerSources = array();

/**
 * List of keys for the default sources that will be loaded.
 * The code for these keys is in the MultimediaPlayerSources class.
 * This is mainly here so that admins can remove some or all of them.
 */
$wgMultimediaPlayerKnownSources = MultimediaPlayerSources::getDefaultSources();
