<?php

/**
 * RatingConfigFactory class for BlueSpice
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
 * RatingConfigFactory class for BlueSpice
 * @package BlueSpiceFoundation
 */
class RatingConfigFactory {
	protected $ratingConfigs = null;

	protected $config = null;
	protected $ratingRegistry = null;

	/**
	 * @param \RatingRegistry $ratingRegistry
	 * @param \Config $config
	 */
	public function __construct( $ratingRegistry, $config ) {
		$this->ratingRegistry = $ratingRegistry;
		$this->config = $config;
	}

	/**
	 * RatingConfig factory
	 * @param string $type - Rating type
	 * @return RatingConfig - or null
	 */
	public function newFromType( $type ) {
		if ( $this->ratingConfigs ) {
			if ( !isset( $this->ratingConfigs[$type] ) ) {
				return null;
			}
			return $this->ratingConfigs[$type];
		}
		$this->ratingConfigs = [];

		$ratingDefinitions = $this->ratingRegistry->getRatingDefinitions();
		$defaults = [];

		// Deprecated: This hook should not be used anymore - Use the bluespice
		// global config mechanism instead
		MediaWikiServices::getInstance()->getHookContainer()->run( 'BSRatingConfigDefaults', [
			&$defaults
		] );
		foreach ( $ratingDefinitions as $key => $sConfigClass ) {
			$this->ratingConfigs[$key] = new $sConfigClass(
				$this->config,
				$key,
				$defaults
			);
		}

		if ( !isset( $this->ratingConfigs[$type] ) ) {
			return null;
		}
		return $this->ratingConfigs[$type];
	}
}
