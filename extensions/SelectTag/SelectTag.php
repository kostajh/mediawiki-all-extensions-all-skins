<?php
/*
Copyright (c) 2012 Khaled El Mansoury
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
--
Links:
Full documentation available at: http://www.expressprogs.com/products/mwext/selecttag/
MediaWiki page: https://www.mediawiki.org/wiki/Extension:SelectTag
*/

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}

$wgExtensionCredits['parserhook'][] = [
	'path' => __FILE__,
	'name' => 'SelectTag',
	'author' => '[http://www.expressprogs.com Khaled El Mansoury]',
	'url' => 'https://www.mediawiki.org/wiki/Extension:SelectTag',
	'descriptionmsg' => 'selecttag-desc',
	'version' => '1.1.0'
];

$wgMessagesDirs['SelectTag'] = __DIR__ . '/i18n';

$wgHooks['ParserFirstCallInit'][] = 'wfSelectTagParserInit';

function wfSelectTagParserInit( Parser $parser ) {
	$parser->setHook( 'select', 'wfSelectTagRender' );
	return true;
}

function wfSelectTagRender( $input, array $params, Parser $parser, PPFrame $frame ) {
	global $wgSelectTag;
	$parser->getOutput()->updateCacheExpiry( 0 );
	$dbr = wfGetDB( DB_REPLICA );
	if ( isset( $params['_source'] ) ) {
			$sourcearray = $wgSelectTag[$params['_source']];
	} else {
			return '<div style="color: red;">' . wfMessage( 'selecttag-sourceattr-unspecified' )->inContentLanguage()->escaped() . '</div>';
	}

	$dbtable    = $sourcearray["_dbname"];
	$cond       = '';
	$cond_array = [];

	if ( isset( $params['_show'] ) ) {
			$show = $sourcearray["_show"][$params['_show']];
	} else {
			$show = $sourcearray["_show"][$sourcearray["_showDefault"]];
	}
	array_push( $cond_array, $show );

	foreach ( $sourcearray as $key => $value ) {
			if ( !is_array( $value ) && substr( $value, 0, 0 ) != "_" ) {
					if ( isset( $params[$key] ) ) {
							$params[$key] = $parser->recursiveTagParse( $params[$key], $frame );
							if ( strpos( $params[$key], "-" ) > -1 ) {
									$values_array = explode( "-", $params[$key] );
									$cond_value   = $value . " BETWEEN " . $values_array[0] . " AND " . $values_array[1];
									unset( $values_array );
							} else {
									$params[$key] = $dbr->addQuotes( $params[$key] );
									$cond_value = $value . "=" . $params[$key] . "";
							}

							if ( $cond == '' ) {
									$cond = $cond_value;
							} else {
									$cond .= " AND " . $cond_value;
							}

							array_push( $cond_array, $value );
					}
			}
	}

	$res          = $dbr->select( $dbtable, $cond_array, $cond, __METHOD__, [] );
	$return_value = '';
	while ( $row = $dbr->fetchRow( $res ) ) {
			if ( $return_value == '' ) {
					$return_value = $row[$show];
			} else {
					$return_value .= "<br />" . $row[$show];
			}
	}
	return $return_value;
}
