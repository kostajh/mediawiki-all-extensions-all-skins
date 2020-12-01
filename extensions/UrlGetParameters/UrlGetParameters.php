<?php
/**
 * UrlGetParameters - An extension that allows to use and/or display the "GET" parameters
 * of the URL on a wiki page
 *
 * @link https://www.mediawiki.org/wiki/Extension:UrlGetParameters Documentation
 *
 * @file UrlGetParameters.php
 * @defgroup UrlGetParameters
 * @ingroup Extensions
 * @package MediaWiki
 * @author S.O.E. Ansems
 * @author Ankit Garg
 * @author Yaron Koren
 * @copyright (C) 2008 S.O.E. Ansems
 * @license https://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

// Ensure that the script cannot be executed outside of MediaWiki.
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This is an extension to MediaWiki and cannot be run standalone.' );
}

// Display extension properties on MediaWiki.
$wgExtensionCredits['parserhook'][] = array(
	'path' => __FILE__,
	'name' => 'UrlGetParameters',
	'version' => '1.5.0',
	'descriptionmsg' => 'urlgetparameters-desc',
	'author' => array(
		'S.O.E. Ansems',
		'...'
	),
	'url' => 'https://www.mediawiki.org/wiki/Extension:UrlGetParameters',
	'license-name' => 'GPL-2.0-or-later'
);

// Register extension messages and other localisation.
$wgMessagesDirs['UrlGetParameters'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['UrlGetParametersMagic'] = __DIR__ . '/UrlGetParameters.i18n.magic.php';

// Register extension hooks.
$wgHooks['ParserFirstCallInit'][] = 'urlGetParameters_Setup';

// Provide configuration parameter.
$wgUrlGetParametersSeparator = ',';

// Do the extension's action.

/**
 * @param $parser Parser
 * @return void
 */
function urlGetParameters_Setup( $parser ) {
	$parser->setFunctionHook( 'urlget', 'urlGetParameters_Render' );
	return true;
}

/**
 * @param $parser Parser
 * @return string
 */
function urlGetParameters_Render( $parser ) {
	global $wgUrlGetParametersSeparator;

	// {{#urlget:paramname|defaultvalue}}

	// Get the parameters that were passed to this function
	$params = func_get_args();
	array_shift( $params );

	// Cache needs to be disabled for URL parameters to be retrieved correctly
	$parser->getOutput()->updateCacheExpiry( 0 );

	// Check whether this param is an array, i.e. of the form "a[b]"
	$pos_left_bracket = strpos( $params[0], '[' );
	$pos_right_bracket = strpos( $params[0], ']' );

	if ( !$pos_left_bracket || !$pos_right_bracket ) {

		if ( isset($_GET[$params[0]] ) ) {
			// Allow array
			if ( is_array( $_GET[$params[0]] ) ) {
				$listval = array();
				foreach ($_GET[$params[0]] as $selectedOption) {
				   	array_push( $listval, rawurlencode( $selectedOption ) );
				}
				return implode($wgUrlGetParametersSeparator, $listval);
			} else {
				return rawurlencode( $_GET[$params[0]] );
			}
		}
	} else {
		// It's an array
		$key = substr( $params[0], 0, $pos_left_bracket );
		$value = substr($params[0], $pos_left_bracket + 1, ( $pos_right_bracket - $pos_left_bracket - 1 ) );

		if ( isset( $_GET[$key] ) && isset( $_GET[$key][$value] ) ) {
			return rawurlencode( $_GET[$key][$value] );
		}
	}
	if ( count( $params ) > 1 ) {
		return rawurlencode( $params[1] );
	}

	return '';
}
