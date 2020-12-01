<?php
/**
 * RatingConfig class for Rating extension
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
 * @package    BlueSpiceRating
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

namespace BlueSpice\Rating;

/**
 * RatingConfig class for Rating extension
 * @package BlueSpiceFoundation
 */
abstract class RatingConfig implements \JsonSerializable, \Config {
	protected $type = '';

	/**
	 *
	 * @var \Config
	 */
	protected $config = null;

	/**
	 *
	 * @var array
	 */
	protected $defaults = [];

	/**
	 *
	 * @param \Config $config
	 * @param string $type
	 * @param array $defaults
	 */
	public function __construct( $config, $type, $defaults = [] ) {
		$this->config = $config;
		$this->type = $type;
		$this->defaults = array_merge(
			$this->addGetterDefaults(),
			$defaults
		);
	}

	/**
	 *
	 * @param string $sOption
	 * @return mixed|false
	 */
	protected function getDefault( $sOption ) {
		if ( isset( $this->defaults[$sOption] ) ) {
			return $this->defaults[$sOption];
		}
		return $this->getConfig()->has( $sOption )
			? $this->getConfig()->get( $sOption )
			: false;
	}

	/**
	 *
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Getter for config methods
	 * @param string $method
	 * @return mixed - The return value of the internaly called method or the
	 * default
	 */
	public function get( $method ) {
		$method = "get_$method";
		if ( !is_callable( [ $this, $method ] ) ) {
			$this->getDefault( $method );
		}
		return $this->$method();
	}

	/**
	 * check for config methods
	 * @param string $method
	 * @return bool
	 */
	public function has( $method ) {
		$method = "get_$method";
		if ( is_callable( [ $this, $method ] ) ) {
			return true;
		}
		if ( isset( $this->defaults[$method] ) ) {
			return true;
		}
		return $this->config->has( $method );
	}

	/**
	 * Returns a json serializable object
	 * @return stdClass
	 */
	public function jsonSerialize() {
		$aConfig = [];
		foreach ( get_class_methods( $this ) as $method ) {
			if ( strpos( $method, 'get_' ) !== 0 ) {
				continue;
			}
			// remove the get_
			$sVarName = substr( $method, 4 );
			$aConfig[$sVarName] = $this->$method();
		}
		return (object)array_merge(
			$this->defaults,
			$aConfig
		);
	}

	/**
	 *
	 * @return array
	 */
	protected function addGetterDefaults() {
		return [];
	}

	/**
	 * @return string
	 */
	abstract protected function get_RatingClass();

	/**
	 * @return string
	 */
	abstract protected function get_TypeMsgKey();

	/**
	 *
	 * @return string
	 */
	protected function get_StoreClass() {
		return "\\BlueSpice\\Rating\\Data\\Store";
	}

	/**
	 *
	 * @return string
	 */
	protected function get_RatingSetClass() {
		return "\\BlueSpice\\Rating\\Data\\RatingSet";
	}

	/**
	 *
	 * @return string[]
	 */
	protected function get_ModuleScripts() {
		return [ 'ext.bluespice.rating', 'ext.bluespice.ratingItem' ];
	}

	/**
	 *
	 * @return string[]
	 */
	protected function get_ModuleStyles() {
		return [ 'ext.bluespice.rating.styles' ];
	}

	/**
	 *
	 * @return mixed[]
	 */
	protected function get_AllowedValues() {
		return [ 1 ];
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_UserCanRemoveVote() {
		return true;
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_MultiValue() {
		return false;
	}

	/**
	 *
	 * @return string
	 */
	protected function get_ReadPermission() {
		return 'rating-read';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_UpdatePermission() {
		return 'rating-write';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_DeletePermission() {
		return 'rating-write';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_DeleteOthersPermission() {
		return 'rating-archive';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_UpdateOthersPermission() {
		return 'rating-archive';
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_PermissionTitleRequired() {
		return false;
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_IsAnonymous() {
		return true;
	}

	/**
	 *
	 * @return string
	 */
	protected function get_HTMLTag() {
		return 'div';
	}

	/**
	 *
	 * @return array
	 */
	protected function get_HTMLTagOptions() {
		return [
			'class' => [ 'bs-rating' ],
			'data-type' => $this->type,
		];
	}
}
