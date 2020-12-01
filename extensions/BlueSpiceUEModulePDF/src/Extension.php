<?php
/**
 * UniversalExport PDF Module extension for BlueSpice
 *
 * Enables MediaWiki to export pages into PDF format.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit https://bluespice.com
 *
 * @author     Robert Vogel <vogel@hallowelt.com>
 * @package    BlueSpiceUEModulePDF
 * @copyright  Copyright (C) 2019 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

namespace BlueSpice\UEModulePDF;

/**
 * Base class for UniversalExport PDF Module extension
 * @package BlueSpiceUEModulePDF
 */
class Extension extends \BlueSpice\Extension {

	/**
	 * extension.json callback
	 */
	public static function onRegistration() {
		/**
		 * Allows modification for CURL request. E.g. setting an CA file for
		 * HTTPS
		 */
		$GLOBALS['bsgUEModulePDFCURLOptions'] = [];

		/**
		 * This value is considered when asseta are being uploaded to the PDF
		 * service
		 */
		$GLOBALS['bsgUEModulePDFUploadThreshold'] = 50 * 1024 * 1024;

		// Remove if minimal system requirements of MW changes to PHP <= 5.5
		if ( !defined( 'CURLOPT_SAFE_UPLOAD' ) ) {
			define( 'CURLOPT_SAFE_UPLOAD', -1 );
		}
	}

}
