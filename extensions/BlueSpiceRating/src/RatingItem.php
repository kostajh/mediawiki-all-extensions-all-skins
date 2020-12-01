<?php
/**
 * RatingItem class for extension Rating
 *
 * Provides a rating item.
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
 * @subpackage Rating
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

namespace BlueSpice\Rating;

use BlueSpice\Context;
use BlueSpice\Data\Filter;
use BlueSpice\Data\ReaderParams;
use BlueSpice\Rating\Data\RatingSet;
use BlueSpice\Rating\Data\Record;
use BlueSpice\Rating\Data\Store;
use MediaWiki\MediaWikiServices;

/**
 * RatingItem class for Rating extension
 * @package BlueSpiceRating
 * @subpackage Rating
 */
class RatingItem implements \JsonSerializable {

	protected $config = null;
	protected $refType = '';
	protected $subType = 'default';
	protected $ref = '';
	/**
	 *
	 * @var RatingSet
	 */
	protected $ratings = null;

	/**
	 * Contructor of the Rating class
	 * @param \stdClass $data
	 * @param RatingConfig $config
	 */
	private function __construct( \stdClass $data, RatingConfig $config ) {
		$this->refType = $data->{Record::REFTYPE};
		$this->ref = $data->{Record::REF};
		$this->subType = $data->{Record::SUBTYPE};
		$this->config = $config;
		$this->loadRating();
	}

	public function jsonSerialize() {
		// TODO: There is currently no way to filter by context!
		$ratings = $this->getRatingSet()->getRatings();
		$userRatings = $this->getRatingSet()->getUserRatings(
			\RequestContext::getMain()->getUser(),
			$ratings
		);

		if ( $this->getConfig()->get( 'IsAnonymous' ) ) {
			$ratings = $this->getRatingSet()->getAnonRatings( $ratings );
		}

		return [
			Record::REFTYPE => $this->getRefType(),
			Record::REF => $this->getRef(),
			Record::SUBTYPE => $this->getSubType(),
			'ratings' => $ratings,
			'userratings' => $userRatings,
		];
	}

	/**
	 * Returns the instance - Should not be used directly. This is a workaround
	 * as all rating __construct methods are protected. Use mediawiki service
	 * 'BSRatingFactory' instead
	 * @param \stdClass $data
	 * @param RatingConfig $config
	 * @param RatingFactory $ratingFactory
	 * @return static
	 */
	public static function newFromFactory( \stdClass $data, RatingConfig $config,
		RatingFactory $ratingFactory ) {
		return new static( $data, $config );
	}

	/**
	 * @param mixed $value
	 * @param int $context
	 * @return \Status
	 */
	public function checkValue( $value = false, $context = 0 ) {
		if ( $this->getConfig()->get( 'MultiValue' ) && empty( $context ) ) {
			return \Status::newFatal(
				'Context cannot be empty when multivalue!'
			);
		}
		if ( $value === false ) {
			// stands for a delete
			return \Status::newGood( $value );
		}
		if ( !$this->isAllowedValue( $value ) ) {
			return \Status::newFatal( 'Value not allowed' );
		}
		return \Status::newGood( $value );
	}

	/**
	 * @param mixed $value
	 * @return \Status
	 */
	public function isAllowedValue( $value = false ) {
		if ( $value === false ) {
			return \Status::newGood( $value );
		}
		$allowedValues = $this->getConfig()->get( 'AllowedValues' );
		return in_array( $value, $allowedValues )
			? \Status::newGood( $value )
			: \Status::newFatal( 'Value not allowed' );
	}

	/**
	 *
	 * @param string $action
	 * @param \User $user
	 * @param \Title|null $title
	 * @return bool
	 */
	protected function checkPermission( $action, \User $user, \Title $title = null ) {
		$action = ucfirst( $action );
		$permission = $this->getConfig()->get( "{$action}Permission" );
		if ( !$permission ) {
			return false;
		}
		if ( $title instanceof \Title ) {
			return MediaWikiServices::getInstance()
				->getPermissionManager()
				->userCan( $permission, $user, $title );
		}
		return MediaWikiServices::getInstance()->getPermissionManager()->userHasRight(
			$user,
			$permission
		);
	}

	/**
	 * @param \User $user
	 * @param string $action
	 * @param \Title|null $title
	 * @return \Status
	 */
	public function userCan( \User $user, $action = 'read', \Title $title = null ) {
		$bTitleRequired = $this->getConfig()->get( 'PermissionTitleRequired' );
		if ( $bTitleRequired && !$title instanceof \Title ) {
			return \Status::newFatal( "Title Required" );
		}
		if ( !$this->checkPermission( $action, $user, $title ) ) {
			return \Status::newFatal( "User is not Allowed $action" );
		}
		return \Status::newGood( $user );
	}

	/**
	 * @return Message
	 */
	public function getTypeMessage() {
		return Message::newFromKey( $this->getConfig()->get( 'TypeMsgKey' ) );
	}

	/**
	 *
	 * @return RatingSet
	 */
	public function getRatingSet() {
		return $this->ratings;
	}

	/**
	 *
	 * @return Store
	 */
	protected function getStore() {
		$storeClass = $this->getConfig()->get( 'StoreClass' );
		if ( !class_exists( $storeClass ) ) {
			return \Status::newFatal( "Store class '$storeClass' not found" );
		}
		return new $storeClass(
			new Context( \RequestContext::getMain(), $this->getConfig() ),
			MediaWikiServices::getInstance()->getDBLoadBalancer()
		);
	}

	/**
	 *
	 * @return array
	 */
	protected function makeLoadRatingsFilter() {
		$filter = [];
		$filter[] = [
			Filter::KEY_FIELD => Record::REFTYPE,
			Filter::KEY_VALUE => $this->getRefType(),
			Filter::KEY_TYPE => 'string',
			Filter::KEY_COMPARISON => Filter::COMPARISON_EQUALS
		];
		$filter[] = [
			Filter::KEY_FIELD => Record::REF,
			Filter::KEY_VALUE => $this->getRef(),
			Filter::KEY_TYPE => 'numeric',
			Filter::KEY_COMPARISON => Filter::COMPARISON_EQUALS
		];
		$filter[] = [
			Filter::KEY_FIELD => Record::SUBTYPE,
			Filter::KEY_VALUE => $this->getSubType(),
			Filter::KEY_TYPE => 'string',
			Filter::KEY_COMPARISON => Filter::COMPARISON_EQUALS
		];
		return $filter;
	}

	/**
	 * loads the ratings from the bs_rating table
	 * @return bool
	 */
	protected function loadRating() {
		$this->ratings = null;
		$reader = $this->getStore()->getReader();
		$result = $reader->read( new ReaderParams( [
			'filter' => $this->makeLoadRatingsFilter(),
		] ) );
		$this->ratings = $result;
		return $this->ratings;
	}

	/**
	 * CRUD votes from the rating item. Use $value = false to delete
	 * @param \User $user - \User, that initiated this action
	 * @param mixed $value - use false to delete
	 * @param \User|null $owner - \User, that the vote is related to
	 * @param int $context - context for multi value
	 * @param \Title|null $title - for permission check!
	 * @return \Status
	 */
	public function vote( \User $user, $value, \User $owner = null, $context = 0,
		\Title $title = null ) {
		if ( !$owner instanceof \User ) {
			$owner = $user;
		}
		$status = $this->checkValue( $value, $context );
		if ( !$status->isOK() ) {
			return $status;
		}
		$ratings = $this->getRatingSet()->getUserRatings( $owner, false, $context );
		if ( $value === false ) {
			$status = $this->userCan( $user, 'delete', $title );
			if ( !$status->isOK() ) {
				return $status;
			}
			if ( $owner->getId() != $user->getId() ) {
				$status = $this->userCan( $user, 'deleteOthers', $title );
				if ( !$status->isOK() ) {
					return $status;
				}
			}
			if ( empty( $ratings ) ) {
				return \Status::newFatal( 'Nothing to delete!' );
			}
			return $this->deleteRating( $owner, $context );
		}
		$status = $this->userCan( $user, 'update', $title );
		if ( !$status->isOK() ) {
			return $status;
		}
		if ( $owner->getId() != $user->getId() ) {
			$status = $this->userCan( $user, 'updateOthers', $title );
			if ( !$status->isOK() ) {
				return $status;
			}
		}
		if ( empty( $ratings ) ) {
			$status = $this->insertRating( $owner, $value, $context );
		} else {
			$status = $this->updateRating(
				$owner,
				$value,
				$ratings,
				$context
			);
		}
		MediaWikiServices::getInstance()->getHookContainer()->run(
			'BlueSpiceRatingItemVoteSaveComplete',
			[
				$this,
				$owner,
				$value,
				$context,
			]
		);

		return $status;
	}

	/**
	 *
	 * @param \User $owner
	 * @param mixed $value
	 * @param int $context
	 * @return \Status
	 */
	protected function insertRating( \User $owner, $value, $context = 0 ) {
		$status = \Status::newGood( $this );
		$id = 0;
		MediaWikiServices::getInstance()->getHookContainer()->run(
			'BlueSpiceRatingItemVoteSave',
			[
				$this,
				$owner,
				&$value,
				&$context,
				$status,
				$id,
			]
		);
		if ( !$status->isOK() ) {
			return $status;
		}
		$writer = $this->getStore()->getWriter();
		$setClass = $this->getConfig()->get( 'RatingSetClass' );
		$result = $writer->write( new $setClass( [
			new Record( (object)[
				Record::ID => $id,
				Record::REF => $this->getRef(),
				Record::VALUE => $value,
				Record::TOUCHED => wfTimestampNow(),
				Record::CREATED => wfTimestampNow(),
				Record::CONTEXT => $context,
				Record::SUBTYPE => $this->getSubType(),
				Record::REFTYPE => $this->getRefType(),
				Record::USERID => $owner->getId(),
				Record::USERIP => $owner->getName(),
			] ),
		] ) );
		foreach ( $result->getRecords() as $record ) {
			if ( $record->getStatus()->isOK() ) {
				continue;
			}
			return \Status::newFatal( 'insert database error' );
		}
		return \Status::newGood( $this->invalidateCache() );
	}

	/**
	 *
	 * @param \User $owner
	 * @param mixed $value
	 * @param Record[] $ratings
	 * @param int $context
	 * @return \Status
	 */
	protected function updateRating( \User $owner, $value, $ratings, $context = 0 ) {
		$status = \Status::newGood( $this );
		MediaWikiServices::getInstance()->getHookContainer()->run(
			'BlueSpiceRatingItemVoteSave',
			[
				$this,
				$owner,
				&$value,
				&$context,
				$status,
				$ratings,
			]
		);
		if ( !$status->isOK() ) {
			return $status;
		}
		foreach ( $ratings as &$record ) {
			$record->set( Record::VALUE, $value );
			$record->set( Record::TOUCHED, wfTimestampNow() );
		}
		$writer = $this->getStore()->getWriter();
		$setClass = $this->getConfig()->get( 'RatingSetClass' );
		$result = $writer->write( new $setClass( $ratings ) );
		foreach ( $result->getRecords() as $record ) {
			if ( $record->getStatus()->isOK() ) {
				continue;
			}
			return \Status::newFatal( 'update database error' );
		}
		return \Status::newGood( $this->invalidateCache() );
	}

	/**
	 * Deletes all user ratings for this RatingItem
	 * @return \Status
	 */
	public function deleteRatingItem() {
		return $this->deleteRating();
	}

	/**
	 * Deletes given \User rating or all ratings when no \User given
	 * @param \User|null $user
	 * @param int $context
	 * @return bool - true or false
	 */
	protected function deleteRating( \User $user = null, $context = 0 ) {
		$ratings = $this->getRatingSet()->getRatings( $context );
		if ( $user ) {
			$ratings = $this->getRatingSet()->getUserRatings( $user, $ratings );
		}
		if ( empty( $ratings ) ) {
			return \Status::newGood( $this->invalidateCache() );
		}

		$writer = $this->getStore()->getWriter();
		$setClass = $this->getConfig()->get( 'RatingSetClass' );
		$result = $writer->remove( new $setClass( $ratings ) );
		foreach ( $result->getRecords() as $record ) {
			if ( $record->getStatus()->isOK() ) {
				continue;
			}
			return \Status::newFatal( 'delete from database error' );
		}
		return \Status::newGood( $this->invalidateCache() );
	}

	/**
	 *
	 * @return string
	 */
	public function getRefType() {
		return $this->refType;
	}

	/**
	 *
	 * @return string
	 */
	public function getSubType() {
		return $this->subType;
	}

	/**
	 *
	 * @return string
	 */
	public function getRef() {
		return $this->ref;
	}

	/**
	 * @return RatingConfig
	 */
	public function getConfig() {
		return $this->config;
	}

	/**
	 *
	 * @return array
	 */
	public function getTagData() {
		return [
			'data-ref' => $this->getRef(),
			'data-subtype' => $this->getSubType(),
			'data-item' => json_encode( $this ),
		];
	}

	/**
	 *
	 * @return string
	 */
	public function getTag() {
		$aOptions = array_merge_recursive(
			$this->getConfig()->get( 'HTMLTagOptions' ),
			$this->getTagData()
		);
		return \HTML::element(
			$this->getConfig()->get( 'HTMLTag' ),
			$aOptions
		);
	}

	public function invalidateCache() {
		MediaWikiServices::getInstance()->getService( 'BSRatingFactory' )
			->invalidateCache( $this );
		$this->loadRating();
		return $this;
	}
}
