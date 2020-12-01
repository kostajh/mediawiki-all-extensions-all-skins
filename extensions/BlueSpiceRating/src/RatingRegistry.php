<?php
/**
 * RatingRegistry class for Rating extension
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
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

namespace BlueSpice\Rating;

use MediaWiki\MediaWikiServices;

/**
 * RatingRegistry class for Rating extension
 * @package BlueSpiceRating
 */
class RatingRegistry {
	protected $ratingDefinitions = null;

	/**
	 *
	 * @var \Config
	 */
	protected $config = null;

	/**
	 *
	 * @param type $config
	 */
	public function __construct( $config ) {
		$this->config = $config;
	}

	/**
	 *
	 * @param bool $forceReload
	 * @return true
	 */
	protected function runRegister( $forceReload = false ) {
		if ( $this->ratingDefinitions && !$forceReload ) {
			return true;
		}

		$extRegistry = \ExtensionRegistry::getInstance();
		$this->ratingDefinitions = $extRegistry->getAttribute(
			'BlueSpiceRatingRatingRegistry'
		);

		// This hook is deprecated - Use attributes mechanism in extension.json
		// to register entities
		MediaWikiServices::getInstance()->getHookContainer()->run( 'RatingRegister', [
			&$this->ratingDefinitions
		] );

		return true;
	}

	/**
	 * Returns all registered entities ( type => RatingConfigClass )
	 * @return array
	 */
	public function getRatingDefinitions() {
		if ( !$this->runRegister() ) {
			return [];
		}
		return $this->ratingDefinitions;
	}

	/**
	 * Checks if given type is a registered Rating
	 * @param string $sType
	 * @return bool
	 */
	public function isRegisteredType( $sType ) {
		return in_array(
			$sType,
			$this->getRegisterdTypeKeys()
		);
	}

	/**
	 * Returns a registered rating by given type
	 * @param string $sType
	 * @return array
	 */
	public function getRegisteredRatingByType( $sType ) {
		if ( !$this->isRegisteredType( $sType ) ) {
			return [];
		}
		return $this->ratingDefinitions[$sType];
	}

	/**
	 * Returns all registered rating types
	 * @return array
	 */
	public function getRegisterdTypeKeys() {
		return array_keys(
			$this->getRatingDefinitions()
		);
	}
}
