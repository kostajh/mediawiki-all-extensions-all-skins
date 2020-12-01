<?php
/**
 * This file is part of the MediaWiki extension ContributionsList
 *
 * Copyright (C) 2014, Ike Hecht
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
class ContributionsListHooks {

	/**
	 * Set up the #contributionslist parser function
	 *
	 * @param Parser $parser
	 */
	public static function setupParserFunction( Parser $parser ) {
		$parser->setFunctionHook( 'contributionslist', __CLASS__ . '::contributionslistParserFunction',
			Parser::SFH_OBJECT_ARGS );
	}

	/**
	 *
	 * The parser function is called with the form:
	 * {{#contributionslist:
	 *    user=username | category=categoryname | type=createonly/notcreate/all |
	 *    format=plain/ol/ul | datefrom=fromdate | dateto=todate }}
	 *
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @param array $args
	 * @return string
	 */
	public static function contributionslistParserFunction(
		Parser $parser, PPFrame $frame, array $args
	) {
		$params = self::extractOptions( $args, $frame );

		$user = isset( $params['user'] ) ? $params['user'] : '';
		$category = isset( $params['category'] ) ? $params['category'] : '';
		$type = isset( $params['type'] ) ? $params['type'] : '';
		$format = isset( $params['format'] ) ? $params['format'] : '';
		$dateFrom = isset( $params['datefrom'] ) ? $params['datefrom'] : '';
		$dateTo = isset( $params['dateto'] ) ? $params['dateto'] : '';

		$contributionsList = new ContributionsList( $user, $category, $type, $dateFrom, $dateTo );
		$output = $contributionsList->getContributionsList( $format );
		return [ $output, 'noparse' => true, 'isHTML' => true ];
	}

	/**
	 * Converts an array of values in form [0] => "name=value" into a real
	 * associative array in form [name] => value
	 *
	 * @param array $options
	 * @param PPFrame $frame
	 * @return array
	 */
	public static function extractOptions( array $options, PPFrame $frame ) {
		$results = [];

		foreach ( $options as $option ) {
			$pair = explode( '=', $frame->expand( $option ), 2 );
			if ( count( $pair ) == 2 ) {
				$name = strtolower( trim( $pair[0] ) );
				$value = trim( $pair[1] );
				$results[$name] = $value;
			}
		}

		return $results;
	}
}
