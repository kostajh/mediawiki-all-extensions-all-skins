<?php
/**
 * RatingFactory class for BlueSpice
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
 * @package    BlueSpice Pro
 * @subpackage BlueSpiceRating
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */
namespace BlueSpice\Rating;

use BlueSpice\Rating\Data\Record;

class RatingFactory {
	/**
	 *
	 * @var array
	 */
	protected $ratingItems = [];

	/**
	 *
	 * @var RatingRegistry
	 */
	protected $ratingRegistry = null;

	/**
	 *
	 * @var RatingConfigFactory
	 */
	protected $configFactory = null;

	/**
	 *
	 * @var \Config
	 */
	protected $config = null;

	/**
	 * @param RatingRegistry $ratingRegistry
	 * @param RatingConfigFactory $configFactory
	 * @param \Config $config
	 * @return Rating | null
	 */
	public function __construct( $ratingRegistry, $configFactory, $config ) {
		$this->ratingRegistry = $ratingRegistry;
		$this->configFactory = $configFactory;
		$this->config = $config;
	}

	/**
	 *
	 * @param string $type
	 * @param \stdClass $data
	 * @return RatingItem
	 */
	protected function factory( $type, $data ) {
		$ratingConfig = $this->configFactory->newFromType( $type );
		if ( !$ratingConfig instanceof RatingConfig ) {
			// TODO: Return a DummyRating instead of null.
			return null;
		}

		$ratingClass = $ratingConfig->get( 'RatingClass' );
		return $ratingClass::newFromFactory( $data, $ratingConfig, $this );
	}

	/**
	 * @param \stdClass|null $data
	 * @return Status
	 */
	public function ensureBasicParams( \stdClass $data = null ) {
		if ( $data === null ) {
			return \Status::newFatal( 'No Data Given' );
		}
		if ( empty( $data->{Record::REF} ) ) {
			return \Status::newFatal( 'No reference Given' );
		}
		if ( empty( $data->{Record::REFTYPE} ) ) {
			return \Status::newFatal( 'No reference type Given' );
		}
		if ( empty( $data->{Record::SUBTYPE} ) ) {
			$data->{Record::SUBTYPE} = 'default';
		}
		return \Status::newGood( $data );
	}

	/**
	 * RatingItem from a set of data
	 * @param \stdClass $data
	 * @return \RatingItem
	 */
	public function newFromObject( \stdClass $data ) {
		$status = $this->ensureBasicParams( $data );
		if ( !$status->isOK() ) {
			return null;
		}
		$instance = $this->getInstanceFromCache(
			$status->getValue()
		);
		if ( $instance instanceof RatingItem ) {
			return $instance;
		}
		return $this->factory( $data->{Record::REFTYPE}, $data );
	}

	/**
	 * TODO: real object cache!
	 * @param \stdClass $data
	 * @return RatingItem - or null
	 */
	protected function getInstanceFromCache( \stdClass $data ) {
		if ( !isset( $this->ratingItems[$data->{Record::REFTYPE}] ) ) {
			return null;
		}
		if ( !isset( $this->ratingItems[$data->{Record::REFTYPE}][$data->{Record::REF}] ) ) {
			return null;
		}
		if ( !isset( $this->ratingItems[$data->{Record::REFTYPE}][$data->{Record::REF}]
			[$data->{Record::SUBTYPE}] ) ) {
			return null;
		}
		return $this->ratingItems
			[$data->{Record::REFTYPE}]
			[$data->{Record::REF}]
			[$data->{Record::SUBTYPE}];
	}

	/**
	 * @param RatingItem $instance
	 * @return \RatingItem
	 */
	protected function appendCache( RatingItem $instance ) {
		$this->ratingItems
			[$instance->getRefType()]
			[$instance->getRef()]
			[$instance->getSubType()]
		= $instance;
		return $instance;
	}

	/**
	 *
	 * @param RatingItem $instance
	 * @return bool
	 */
	public function invalidateCache( RatingItem $instance ) {
		if ( !isset( $this->ratingItems[$instance->getRefType()] ) ) {
			return false;
		}
		if ( !isset( $this->ratingItems[$instance->getRefType()][$instance->getRef()] ) ) {
			return false;
		}
		if ( !isset( $this->ratingItems[$instance->getRefType()][$instance->getRef()]
			[$instance->getSubType()] ) ) {
			return false;
		}
		unset( $this->ratingItems
			[$instance->getRefType()]
			[$instance->getRef()]
			[$instance->getSubType()]
		);
		return true;
	}
}
