<?php
/**
 * Hook handler base class for BlueSpice hook BSCustomMenuDefaultRecords
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
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
 * @author     Patric Wirth
 * @package    BlueSpiceCustomMenu
 * @copyright  Copyright (C) 2020 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */
namespace BlueSpice\CustomMenu\Hook;

use BlueSpice\Data\Record;
use BlueSpice\Hook;
use Config;
use IContextSource;

abstract class BSCustomMenuDefaultRecords extends Hook {

	/**
	 *
	 * @var string
	 */
	protected $key = null;

	/**
	 *
	 * @var Record
	 */
	protected $record = null;

	/**
	 *
	 * @param string $key
	 * @param Record &$record
	 * @return bool
	 */
	public static function callback( $key, &$record ) {
		$className = static::class;
		$hookHandler = new $className(
			null,
			null,
			$key,
			$record
		);
		return $hookHandler->process();
	}

	/**
	 *
	 * @param IContextSource $context
	 * @param Config $config
	 * @param string $key
	 * @param Record &$record
	 * @return bool
	 */
	public function __construct( $context, $config, $key, &$record ) {
		parent::__construct( $context, $config );

		$this->key = $key;
		$this->record =& $record;
	}
}
