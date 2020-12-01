<?php
/**
 * Hook handler base class for BlueSpice hook BSBookshelfTagBeforeRender
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

use BlueSpice\Hook;
use Config;
use IContextSource;

abstract class BSBookshelfTagBeforeRender extends Hook {

	/**
	 *
	 * @var string
	 */
	protected $sourceArticle = null;

	/**
	 *
	 * @var \stdClass
	 */
	protected $JSTree = null;

	/**
	 *
	 * @var string
	 */
	protected $number = null;

	/**
	 *
	 * @var array
	 */
	protected $attribs = null;

	/**
	 *
	 * @param string &$sourceArticle
	 * @param \stdClass $JSTree
	 * @param string &$number
	 * @param array &$attribs
	 * @return bool
	 */
	public static function callback( &$sourceArticle, $JSTree, &$number, &$attribs ) {
		$className = static::class;
		$hookHandler = new $className(
			null,
			null,
			$sourceArticle,
			$JSTree,
			$number,
			$attribs
		);
		return $hookHandler->process();
	}

	/**
	 *
	 * @param IContextSource $context
	 * @param Config $config
	 * @param string &$sourceArticle
	 * @param \stdClass $JSTree
	 * @param string &$number
	 * @param array &$attribs
	 */
	public function __construct( $context, $config, &$sourceArticle, $JSTree, &$number, &$attribs ) {
		parent::__construct( $context, $config );

		$this->sourceArticle =& $sourceArticle;
		$this->JSTree = $JSTree;
		$this->number =& $number;
		$this->attribs =& $attribs;
	}
}
