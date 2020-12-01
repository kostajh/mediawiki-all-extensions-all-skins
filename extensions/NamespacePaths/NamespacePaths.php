<?php
/**
 * Namespace Paths
 *
 * @file
 * @ingroup Extensions
 * @author Daniel Friesen (http://danf.ca/mw/)
 * @copyright Copyright © 2010 – Daniel Friesen
 * @license https://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * @link https://www.mediawiki.org/wiki/Extension:NamespacePaths Documentation
 */

if ( !defined( 'MEDIAWIKI' ) ) die( "This is an extension to the MediaWiki package and cannot be run standalone." );

$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'NamespacePaths',
	'version' => '1.1.0',
	'author' => array( '[http://danf.ca/mw/ Daniel Friesen]', '[http://redwerks.org/mediawiki/ Redwerks]' ),
	'url' => 'https://www.mediawiki.org/wiki/Extension:NamespacePaths',
	'descriptionmsg' => 'namespacepaths-desc',
);

$wgMessagesDirs['NamespacePaths'] = __DIR__ . '/i18n';

$wgHooks['WebRequestPathInfoRouter'][] = 'efNamepacePathRouter';
$wgHooks['GetLocalURL::Article'][] = 'efNamepacePathsGetURL';

function efNamepacePathRouter( $router ) {
	global $wgNamespacePaths;
	$router->add( $wgNamespacePaths,
		array( 'data:page_title' => '$1', 'data:ns' => '$key' ),
		array( 'callback' => 'efNamespacePathCallback' )
	);
	return true;
}

function efNamespacePathCallback( &$matches, $data ) {
	$nstext = MWNamespace::getCanonicalName( intval( $data['ns'] ) );
	$matches['title'] = $nstext . ':' . $data['page_title'];
}

function efNamepacePathsGetURL( $title, &$url ) {
	global $wgNamespacePaths;
	// Ensure that the context of this url is one we'd do article path replacements in
	$ns = $title->getNamespace();
	if ( array_key_exists( $ns, $wgNamespacePaths ) ) {
		$url = str_replace( '$1', wfUrlencode( $title->getDBkey() ), $wgNamespacePaths[$ns] );
	}
	return true;
}

/** Settings **/
$wgNamespacePaths = array();

