<?php

namespace BlueSpice\ExtendedStatistics\Entity;

use BlueSpice\Data\FieldType;
use BlueSpice\ExtendedStatistics\Data\Entity\Collection\Schema;
use MediaWiki\MediaWikiServices;
use RequestContext;
use Status;
use Title;
use User;

abstract class Collection extends \BlueSpice\Entity {

	/**
	 * Checks, if the current Entity exists in the Wiki
	 * @return bool
	 */
	public function exists() {
		return true;
	}

	/**
	 * Returns an entity's attributes or the given default, if not set
	 * @param string $attrName
	 * @param mixed|null $default
	 * @return mixed
	 */
	public function get( $attrName, $default = null ) {
		foreach ( $this->getRealAttributes() as $name => $definition ) {
			if ( $attrName !== $name ) {
				continue;
			}
			if ( empty( $this->attributes[$attrName] ) ) {
				if ( $definition[Schema::TYPE] === FieldType::INT ) {
					return 0;
				}
				if ( $definition[Schema::TYPE] === FieldType::STRING ) {
					return '';
				}
			}
		}
		if ( !isset( $this->attributes[$attrName] ) ) {
			return $default;
		}
		return $this->attributes[$attrName];
	}

	/**
	 * Gets the Entity attributes formated for the api
	 * @param array $data
	 * @return array
	 */
	public function getFullData( $data = [] ) {
		foreach ( $this->getRealAttributes() as $name => $definition ) {
			if ( $this->get( $name, false ) === false ) {
				continue;
			}
			$data[$name] = $this->get( $name );
		}
		return parent::getFullData( $data );
	}

	/**
	 *
	 * @return array
	 */
	private function getRealAttributes() {
		return array_diff_key(
			$this->getConfig()->get( 'AttributeDefinitions' ),
			$this->getConfig()->get( 'DefaultAttributeDefinitions' )
		);
	}

	/**
	 * @param \stdClass $data
	 */
	public function setValuesByObject( \stdClass $data ) {
		$attributes = $this->getRealAttributes();
		if ( !empty( $data->{static::ATTR_TIMESTAMP_CREATED} ) ) {
			$this->set(
				static::ATTR_TIMESTAMP_CREATED,
				$data->{static::ATTR_TIMESTAMP_CREATED}
			);
		}
		if ( !empty( $data->{static::ATTR_TIMESTAMP_TOUCHED} ) ) {
			$this->set(
				static::ATTR_TIMESTAMP_TOUCHED,
				$data->{static::ATTR_TIMESTAMP_TOUCHED}
			);
		}
		foreach ( (array)$data as $key => $val ) {
			if ( !isset( $attributes[$key] ) ) {
				continue;
			}
			$this->set( $key, $data->{$key} );
		}
		parent::setValuesByObject( $data );
	}

	/**
	 *
	 * @return Title|null
	 */
	protected function getRelatedTitle() {
		return null;
	}

	/**
	 *
	 * @param string $action
	 * @param User $user
	 * @param Title|null $title
	 * @return Status
	 */
	protected function checkPermission( $action, User $user, Title $title = null ) {
		$permission = $this->getConfig()->get(
			ucfirst( $action ) . "Permission"
		);

		if ( !$permission ) {
			return Status::newFatal(
				'bs-extendedstatistics-collection-fatalstatus-unknownaction',
				$action
			);
		}
		$status = Status::newGood( $this );
		if ( $title instanceof Title && !\MediaWiki\MediaWikiServices::getInstance()
			->getPermissionManager()
			->userCan( $permission, $user, $title )
		) {
			$status->fatal(
				'bs-extendedstatistics-collection-fatalstatus-permissiondeniedusercan',
				$action,
				$title->getFullText()
			);
			return $status;
		}

		$isAllowed = MediaWikiServices::getInstance()->getPermissionManager()->userHasRight(
			$user,
			$permission
		);
		if ( !$isAllowed ) {
			$status->fatal(
				'bs-extendedstatistics-collection-fatalstatus-permissiondenieduserisallowed',
				$action
			);
			return $status;
		}
		return $status;
	}

	/**
	 * @param string $action
	 * @param User|null $user
	 * @return Status
	 */
	public function userCan( $action = 'read', User $user = null ) {
		$title = null;
		if ( !$user instanceof User ) {
			$user = RequestContext::getMain()->getUser();
		}
		if ( $this->getConfig()->get( 'PermissionTitleRequired' ) ) {
			$title = $this->getRelatedTitle();
			if ( !$title instanceof Title ) {
				return Status::newFatal(
					'bs-extendedstatistics-collection-fatalstatus-notitle'
				);
			}
		}
		return $this->checkPermission( $action, $user, $title );
	}
}
