<?php
/**
 * UniversalExport HTML Module extension for BlueSpice
 *
 * Enables MediaWiki to export pages into HTML format.
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
 * @package    BlueSpice_Extensions
 * @subpackage UEModuleHTML
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

/**
 * Base class for UniversalExport HTML Module extension
 * @package BlueSpice_Extensions
 * @subpackage UEModuleHTML
 */
class UEModuleHTML extends BsExtensionMW {

	/**
	 * Initialization of UEModuleHTML extension
	 */
	protected function initExt() {
		$this->setHook( 'BSUniversalExportSpecialPageExecute' );
	}

	/**
	 *
	 * @param SpecialUniversalExport $specialPage
	 * @param string $param
	 * @param array &$modules
	 * @return true
	 */
	public function onBSUniversalExportSpecialPageExecute( $specialPage, $param, &$modules ) {
		$modules['html'] = new ExportModuleHTML();
		return true;
	}
}
