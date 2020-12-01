<?php
/**
 * Negref
 *
 * @file
 * @ingroup Extensions
 * @author Daniel Friesen (http://danf.ca/mw/)
 * @license https://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * @link https://www.mediawiki.org/wiki/Extension:Negref Documentation
 */

if ( !defined( 'MEDIAWIKI' ) ) die( "This is an extension to the MediaWiki package and cannot be run standalone." );

$wgExtensionCredits['parserhook'][] = array(
	'path' => __FILE__,
	'name' => "Negref",
	'author' => "[http://danf.ca/mw/ Daniel Friesen]",
	'description-msg' => 'negref-desc',
	'url' => 'https://www.mediawiki.org/wiki/Extension:Negref',
	'license-name' => 'GPL-2.0-or-later',
);

$dir = dirname( __FILE__ ) . '/';
$wgMessagesDirs['NegRef'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['NegRefMagic'] = $dir . 'Negref.i18n.magic.php';
$wgHooks['ParserFirstCallInit'][] = 'efNegrefRegisterParser';

function efNegrefRegisterParser( &$parser ) {
	$parser->setFunctionHook( 'negref', 'efNegrefHook' );
	return true;
}

function efNegrefHook( $parser, $input, $replaceData='', $replaceRef='', $pattern='' ) {
	$data = $input;
	$ref = '';

	$keys = array_keys( $parser->mStripState->general->getArray() );
	foreach ( $keys as $key ) {
		if ( preg_match( '/^' . preg_quote( $parser->uniqPrefix(), '/' ) . '-(ref)-.*$/', $key ) ) {
			if ( substr_count( $input, $key ) > 0 ) {
				$data = str_replace( $key, '', $data );
				$ref .= $key;
			}
		}
	}

	return str_replace( $replaceRef, $ref, str_replace( $replaceData, $data, $pattern ) );
}
