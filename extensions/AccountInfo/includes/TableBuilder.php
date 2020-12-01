<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 */

namespace MediaWiki\AccountInfo;

use Html;
use Xml;

/**
 * Fork of Xml::buildTable to use classes instead of ids.
 */
class TableBuilder {

	/**
	 * @param array $rows array of rows, will not be escaped
	 * @param array $headers raw HTML, will not be escaped
	 * @param array $attribs
	 * @return string
	 */
	public static function buildTable( $rows, $headers, $attribs = [] ) {
		$s = Xml::openElement( 'table', $attribs );

		if ( is_array( $headers ) ) {
			$s .= Xml::openElement( 'thead', $attribs );

			foreach ( $headers as $class => $header ) {
				$attribs = [];

				if ( is_string( $class ) ) {
					$attribs['class'] = $class;
				}

				$s .= Html::rawElement( 'th', $attribs, $header );
			}
			$s .= Xml::closeElement( 'thead' );
		}

		foreach ( $rows as $class => $row ) {
			$attribs = [];

			if ( is_string( $class ) ) {
				$attribs['class'] = $class;
			}

			$s .= self::buildTableRow( $attribs, $row );
		}

		$s .= Xml::closeElement( 'table' );

		return $s;
	}

	/**
	 * Build a row for a table
	 * @param array $attribs An array of attributes to apply to the tr tag
	 * @param array $cells An array of strings to put in <td>
	 * @return string
	 */
	protected static function buildTableRow( $attribs, $cells ) {
		$s = Xml::openElement( 'tr', $attribs );

		foreach ( $cells as $class => $cell ) {
			$attribs = [];

			if ( is_string( $class ) ) {
				$attribs['class'] = $class;
			}

			$s .= Html::rawElement( 'td', $attribs, $cell );
		}

		$s .= Xml::closeElement( 'tr' );

		return $s;
	}
}
