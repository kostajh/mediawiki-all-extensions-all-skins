<?php
/**
 * Hook handler base class for BlueSpice hook BsContextMenuGetItems
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
 * @package    BlueSpiceContextMenu
 * @copyright  Copyright (C) 2018 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

namespace BlueSpice\ContextMenu\Hook;

abstract class BsContextMenuGetItems extends \BlueSpice\Hook {

	/**
	 *
	 * @var array
	 */
	protected $items = null;

	/**
	 *
	 * @var \Title
	 */
	protected $title = null;

	/**
	 * Located in BSApiContextMenuTasks::task_getMenuItems. Before items are
	 * returned
	 * @param array &$items
	 * @param \Title $title
	 * @return bool
	 */
	public static function callback( &$items, $title ) {
		$className = static::class;
		$hookHandler = new $className(
			null,
			null,
			$items,
			$title
		);
		return $hookHandler->process();
	}

	/**
	 * @param \IContextSource $context
	 * @param \Config $config
	 * @param array &$items
	 * @param \Title $title
	 */
	public function __construct( $context, $config, &$items, $title ) {
		parent::__construct( $context, $config );

		$this->items =& $items;
		$this->title = $title;
	}
}
