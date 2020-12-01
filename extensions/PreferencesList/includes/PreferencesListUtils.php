<?php
/*
 * Copyright (C) 2017 Ike Hecht <tosfos@yahoo.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

/**
 * Some utilities for PreferencesList
 *
 * @author Ike Hecht <tosfos@yahoo.com>
 */
class PreferencesListUtils {
	/**
	 * Convert an array to a CSV
	 * From https://stackoverflow.com/a/16251849
	 *
	 * @param array $array Numbered array where each element is an array that will be converted
	 * to a row
	 * @param string $filename
	 * @param string $delimiter
	 */
	public static function arrayToCsvDownload(
	array $array, $filename = 'export.csv', $delimiter = ';' ) {
		/** @todo Should the filename get i18n? */
		header( 'Content-Type: application/csv' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '";' );

		$f = fopen( 'php://output', 'w' );

		foreach ( $array as $line ) {
			fputcsv( $f, $line, $delimiter );
		}
		/** @todo return success or failure */
	}

	/**
	 * Given a HTMLForm descriptor-style array, returns the appropriate label for the field
	 *
	 * @param string $key
	 * @param array $params
	 * @param IContextSource $context
	 * @return string
	 */
	public static function getMessage( $key, array $params, IContextSource $context ) {
		# Based on HtmlFormField::__construct version 1.26
		# Generate the label from a message, if possible
		if ( isset( $params['label-message'] ) ) {
			$msgInfo = $params['label-message'];

			if ( is_array( $msgInfo ) ) {
				$msg = array_shift( $msgInfo );
			} else {
				$msg = $msgInfo;
				$msgInfo = [];
			}

			return $context->msg( $msg, $msgInfo )->parse();
		} elseif ( isset( $params['label'] ) ) {
			if ( $params['label'] === '&#160;' ) {
				// Apparently some things set &nbsp directly and in an odd format
				return '&#160;' . $key;
			} else {
				return htmlspecialchars( $params['label'] );
			}
		} elseif ( isset( $params['label-raw'] ) ) {
			return $params['label-raw'];
		}
		return '';
	}
}
