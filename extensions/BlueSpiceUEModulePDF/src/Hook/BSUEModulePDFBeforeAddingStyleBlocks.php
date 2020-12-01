<?php
/**
 * Hook handler base class for BlueSpice hook BSUEModulePDFBeforeAddingStyleBlocks
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
 * @package    BlueSpiceFoundation
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */
namespace BlueSpice\UEModulePDF\Hook;

abstract class BSUEModulePDFBeforeAddingStyleBlocks extends \BlueSpice\Hook {

	/**
	 *
	 * @var array
	 */
	protected $template = null;

	/**
	 *
	 * @var array
	 */
	protected $styleBlocks = null;

	/**
	 *
	 * @param array &$template
	 * @param array &$styleBlocks
	 * @return bool
	 */
	public static function callback( &$template, &$styleBlocks ) {
		$className = static::class;
		$hookHandler = new $className(
			null,
			null,
			$template,
			$styleBlocks
		);
		return $hookHandler->process();
	}

	/**
	 *
	 * @param \IContextSource $context
	 * @param \Config $config
	 * @param array &$template
	 * @param array &$styleBlocks
	 */
	public function __construct( $context, $config, &$template, &$styleBlocks ) {
		parent::__construct( $context, $config );

		$this->template =& $template;
		$this->styleBlocks =& $styleBlocks;
	}
}
