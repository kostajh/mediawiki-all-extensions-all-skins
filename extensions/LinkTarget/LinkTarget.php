<?php
/**
 * Adds a target attribute to specified links
 *
 * For more info see https://mediawiki.org/wiki/Extension:LinkTarget
 *
 * @file
 * @ingroup Extensions
 * @author Ike Hecht, 2015
 * @license GNU General Public Licence 2.0 or later
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	echo <<<EOT
To install this extension, put the following line in LocalSettings.php:
require_once "$IP/extensions/LinkTarget/LinkTarget.php";
EOT;
	exit( 1 );
}

$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'LinkTarget',
	'author' => array(
		'Ike Hecht',
	),
	'version' => '0.1.0',
	'url' => 'https://www.mediawiki.org/wiki/Extension:LinkTarget',
	'descriptionmsg' => 'linktarget-desc',
	'license-name' => 'GPL-2.0-or-later'
);

$wgMessagesDirs['LinkTarget'] = __DIR__ . '/i18n';

/**
 * Convert the output to DOM and modify all links to have the appropriate targets.
 *
 * @global array $wgLinkTargetParentClasses
 * @global string $wgLinkTargetDefault
 * @param OutputPage $out Unused
 * @param ParserOutput $parseroutput
 * @return boolean B/c
 */
$wgHooks['OutputPageParserOutput'][] = function ( OutputPage &$out, ParserOutput $parseroutput ) {
	global $wgLinkTargetParentClasses, $wgLinkTargetDefault;

	if ( empty( $wgLinkTargetParentClasses ) ) {
		// Save all that processing
		return true;
	} elseif ( !is_array( $wgLinkTargetParentClasses ) ) {
		$wgLinkTargetParentClasses = array( $wgLinkTargetParentClasses );
	}

	$text = $parseroutput->getText();
	if ( empty( $text ) ) {
		return true;
	}

	/** @todo Support framename? */
	$validTargets = array( '_blank', '_self', '_parent', '_top' );

	$htmlFormatter = new HtmlFormatter\HtmlFormatter( $text );
	$dom = $htmlFormatter->getDoc();
	$xpath = new DOMXpath( $dom );
	foreach ( $wgLinkTargetParentClasses as $target => $parentClasses ) {
		if ( !is_array( $parentClasses ) ) {
			$parentClasses = array( $parentClasses );
		}
		foreach ( $parentClasses as $parentClass ) {
			if ( !in_array( $target, $validTargets, true ) ) {
				$target = $wgLinkTargetDefault;
			}
			$nodes = $xpath->query(
				"//*[contains(concat(' ', normalize-space(@class), ' '), ' {$parentClass} ')]//a" );
			foreach ( $nodes as $node ) {
				$node->setAttribute( 'target', $target );
			}
		}
	}
	$parseroutput->setText( $htmlFormatter->getText() );

	return true;
};

// Config
# Expects an array with the format:
#      array( target => array( classes ), target => array( classes ), ... ).
# If target is not specified or is invalid, the target will be set to $wgLinkTargetDefault.
# Note that it is also valid to specify one class as a string, instead of an array of classes.
$wgLinkTargetParentClasses = array();
# The default target for parent classes that do not have a valid target specified.
$wgLinkTargetDefault = '_blank';
