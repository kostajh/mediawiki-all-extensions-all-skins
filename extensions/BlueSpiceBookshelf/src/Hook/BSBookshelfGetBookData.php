<?php
/**
 * Hook handler base class for BlueSpice hook BSBookshelfNodeTag
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
 * @package    BlueSpiceBookshelf
 * @copyright  Copyright (C) 2020 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */
namespace BlueSpice\Bookshelf\Hook;

use BlueSpice\Bookshelf\BookEditData;
use BlueSpice\Hook;
use Config;
use IContextSource;
use stdClass;

abstract class BSBookshelfGetBookData extends Hook {

	/**
	 *
	 * @var BookEditData
	 */
	protected $editDataProvider = null;

	/**
	 *
	 * @var string
	 */
	protected $bookData = null;

	/**
	 *
	 * @param BookEditData $editData
	 * @param stdClass &$bookData
	 * @return bool
	 */
	public static function callback( BookEditData $editData, &$bookData ) {
		$className = static::class;
		$hookHandler = new $className(
			null,
			null,
			$editData,
			$bookData
		);
		return $hookHandler->process();
	}

	/**
	 *
	 * @param IContextSource $context
	 * @param Config $config
	 * @param BookEditData $editData
	 * @param stdClass &$bookData
	 * @return bool
	 */
	public function __construct( $context, $config, BookEditData $editData, &$bookData ) {
		parent::__construct( $context, $config );

		$this->editDataProvider = $editData;
		$this->bookData =& $bookData;
	}
}
