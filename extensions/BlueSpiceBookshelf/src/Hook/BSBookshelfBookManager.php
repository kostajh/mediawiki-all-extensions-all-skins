<?php
/**
 * Hook handler base class for BlueSpice hook BSBookshelfBookManager
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
use BlueSpice\SpecialPage;
use Config;
use IContextSource;
use OutputPage;

abstract class BSBookshelfBookManager extends Hook {

	/**
	 *
	 * @var SpecialPage
	 */
	protected $manager = null;

	/**
	 *
	 * @var OutputPage
	 */
	protected $out = null;

	/**
	 *
	 * @var array
	 */
	protected $configVars = null;

	/**
	 *
	 * @param SpecialPage $manager
	 * @param OutputPage $out
	 * @param array $configVars
	 * @return bool
	 */
	public static function callback( $manager, $out, $configVars ) {
		$className = static::class;
		$hookHandler = new $className(
			$manager->getContext(),
			$manager->getConfig(),
			$manager,
			$out,
			$configVars
		);
		return $hookHandler->process();
	}

	/**
	 *
	 * @param IContextSource $context
	 * @param Config $config
	 * @param SpecialPage $manager
	 * @param OutputPage $out
	 * @param array $configVars
	 * @return bool
	 */
	public function __construct( $context, $config, $manager, $out, $configVars ) {
		parent::__construct( $context, $config );

		$this->manager = $manager;
		$this->out = $out;
		$this->configVars = $configVars;
	}
}
