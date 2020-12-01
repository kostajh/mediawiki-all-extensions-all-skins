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

use BlueSpice\Hook;
use Config;
use IContextSource;
use Parser;

abstract class BSBookshelfNodeTag extends Hook {

	/**
	 *
	 * @var string
	 */
	protected $type = null;

	/**
	 *
	 * @var string
	 */
	protected $nodeText = null;

	/**
	 *
	 * @var array
	 */
	protected $attribs = null;

	/**
	 *
	 * @var string
	 */
	protected $element = null;

	/**
	 *
	 * @var Parser
	 */
	protected $parser = null;

	/**
	 *
	 * @param string $type
	 * @param string &$nodeText
	 * @param array &$attribs
	 * @param string &$element
	 * @param Parser $parser
	 * @return bool
	 */
	public static function callback( $type, &$nodeText, &$attribs, &$element, $parser ) {
		$className = static::class;
		$hookHandler = new $className(
			null,
			null,
			$type,
			$nodeText,
			$attribs,
			$element,
			$parser
		);
		return $hookHandler->process();
	}

	/**
	 *
	 * @param IContextSource $context
	 * @param Config $config
	 * @param string $type
	 * @param string &$nodeText
	 * @param array &$attribs
	 * @param string &$element
	 * @param Parser $parser
	 * @return bool
	 */
	public function __construct( $context, $config, $type, &$nodeText, &$attribs, &$element,
		$parser ) {
		parent::__construct( $context, $config );

		$this->type = $type;
		$this->nodeText =& $nodeText;
		$this->attribs =& $attribs;
		$this->element =& $element;
		$this->parser =& $parser;
	}
}
