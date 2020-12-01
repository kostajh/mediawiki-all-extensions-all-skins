<?php

/**
 * BlueSpiceSocialEntity class for BlueSpiceSocial
 *
 * add desc
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
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
 * @package    BlueSpiceSocial
 * @subpackage BlueSpiceSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */
namespace BlueSpice\Social;

use BlueSpice\Context;
use BlueSpice\Data\ReaderParams;
use BlueSpice\Social\Job\Archive;
use BsNamespaceHelper;
use Exception;
use JobQueueGroup;
use MediaWiki\MediaWikiServices;
use Message;
use MWException;
use RequestContext;
use Status;
use Title;
use User;
use WikiPage;

/**
 * BlueSpiceSocialEntity class for BlueSpiceSocial extension
 * @package BlueSpiceSocial
 * @subpackage BlueSpiceSocial
 */
abstract class Entity extends \BlueSpice\Entity\Content {
	const NS = NS_SOCIALENTITY;

	const ATTR_PARENT_ID = 'parentid';
	const ATTR_HEADER = 'header';
	const ATTR_RELATED_TITLE = 'relatedtitle';
	const ATTR_OWNER_NAME = 'ownername';
	const ATTR_OWNER_REAL_NAME = 'ownerrealname';
	const ATTR_ACTIONS = 'actions';
	const ATTR_PRELOAD = 'preload';

	/**
	 *
	 * @var Entity[]
	 */
	protected $children = null;

	/**
	 *
	 * @var Title
	 */
	protected $relatedTitle = null;

	/**
	 *
	 * @var User[]
	 */
	protected static $ownerLookup = [];

	/**
	 * Returns an entity's attributes or the given default, if not set
	 * @param string $attrName
	 * @param mixed|null $default
	 * @return mixed
	 */
	public function get( $attrName, $default = null ) {
		// This is for a prerendered form of entities, that require the current
		// user
		if ( $attrName == static::ATTR_OWNER_ID ) {
			if ( !$this->exists() && empty( $this->attributes[$attrName] ) ) {
				return RequestContext::getMain()->getUser()->getId();
			}
		}
		if ( $attrName == static::ATTR_OWNER_NAME ) {
			return $this->getOwner()->getName();
		}
		if ( $attrName == static::ATTR_OWNER_REAL_NAME ) {
			return $this->getOwner()->getRealName();
		}
		if ( $attrName == static::ATTR_RELATED_TITLE ) {
			return $this->getRelatedTitle()->getFullText();
		}
		if ( $attrName == static::ATTR_HEADER ) {
			return $this->getHeader()->parse();
		}
		if ( $attrName == static::ATTR_PRELOAD && empty( parent::get( $attrName, '' ) ) ) {
			return $this->getConfig()->get( 'EntityListPreloadTitle' );
		}

		return parent::get( $attrName, $default );
	}

	/**
	 * Returns the User object of the entity's owner
	 * @return User
	 */
	public function getOwner() {
		$id = $this->get( static::ATTR_OWNER_ID, 0 );
		if ( $id < 1 ) {
			return new User();
		}
		if ( isset( static::$ownerLookup[$id] ) ) {
			return static::$ownerLookup[$id];
		}
		$user = User::newFromId( $id );
		if ( $user && !$user->isAnon() ) {
			static::$ownerLookup[$id] = $user;
		}
		return $user;
	}

	/**
	 * Returns the owners username
	 * @return string
	 */
	public function getOwnerName() {
		return $this->getOwner()->getName();
	}

	/**
	 * Returns the owners real name or the username if empty
	 * @return string
	 */
	public function getOwnerRealName() {
		$sName = $this->getOwner()->getRealName();
		if ( empty( $sName ) ) {
			$sName = $this->getOwnerName();
		}
		return $sName;
	}

	/**
	 * Returns the Message Key for the entity header
	 * @return string
	 */
	public function getHeaderMessageKey() {
		if ( $this->exists() ) {
			return $this->getConfig()->get( 'HeaderMessageKey' );
		}
		return $this->getConfig()->get( 'HeaderMessageKeyCreateNew' );
	}

	/**
	 * Returns the Message object for the entity header
	 * @param Message|null $oMsg
	 * @return Message
	 */
	public function getHeader( $oMsg = null ) {
		if ( !$oMsg instanceof Message ) {
			$oMsg = Message::newFromKey( $this->getHeaderMessageKey() );
		}

		return $oMsg->title( $this->getTitle() )->params( [
			$this->getOwner()->getName(),
			$this->getOwnerRealName(),
			$this->getTitle()->getText(),
			$this->getTitle()->getNamespace(),
			BsNamespaceHelper::getNamespaceName(
				$this->getTitle()->getNamespace()
			),
		] );
	}

	/**
	 * Saves the current Entity
	 * @param User|null $user
	 * @param array $options
	 * @return Status
	 */
	public function save( User $user = null, $options = [] ) {
		// force the recreation of the related title before the entity is saved
		$this->relatedTitle = null;
		if ( !$user instanceof User ) {
			return Status::newFatal( wfMessage(
				'bs-social-entity-fatalstatus-save-nouser'
			) );
		}
		return parent::save( $user, $options );
	}

	/**
	 * Deletes the current BlueSpiceSocialEntity
	 * @param User|null $oUser
	 * @return Status
	 */
	public function delete( User $oUser = null ) {
		if ( !$oUser instanceof User ) {
			$oUser = MediaWikiServices::getInstance()->getService( 'BSUtilityFactory' )
				->getMaintenanceUser()->getUser();
		}
		$status = parent::delete( $oUser );
		if ( !$status->isOK() ) {
			return $status;
		}

		$this->deleteChildren( $oUser );
		return $status;
	}

	/**
	 * Restores the current Entity from archived state
	 * @param User|null $user
	 * @return Status
	 */
	public function undelete( User $user = null ) {
		if ( !$user instanceof User ) {
			$user = MediaWikiServices::getInstance()->getService( 'BSUtilityFactory' )
				->getMaintenanceUser()->getUser();
		}
		return parent::undelete( $user );
	}

	/**
	 * Deletes all children of the current BlueSpiceSocialEntity
	 * @param User|null $user
	 * @return Status
	 */
	public function deleteChildren( User $user = null ) {
		$status = Status::newGood( $this );
		foreach ( $this->getChildren() as $entity ) {
			try {
				$job = new Archive(
					$entity->getTitle()
				);
				JobQueueGroup::singleton()->push(
					$job
				);
			} catch ( Exception $e ) {
				$status->error( $e->getMessage() );
			}
		}

		return $status;
	}

	/**
	 * Gets the BlueSpiceSocialEntity attributes formated for the api
	 * @param array $a
	 * @return object
	 */
	public function getFullData( $a = [] ) {
		return parent::getFullData( array_merge(
			$a,
			[
				static::ATTR_PARENT_ID => $this->get( static::ATTR_PARENT_ID, 0 ),
				static::ATTR_PRELOAD => $this->get( static::ATTR_PRELOAD, '' ),
				static::ATTR_ACTIONS => $this->getActions(),
				static::ATTR_HEADER => $this->getHeader()->parse(),
				static::ATTR_RELATED_TITLE => $this->getRelatedTitle()->getFullText(),
				static::ATTR_OWNER_NAME => $this->getOwnerName(),
				static::ATTR_OWNER_REAL_NAME => $this->getOwnerRealName()
			]
		) );
	}

	/**
	 * Returns an array of the entitys children. Does not check permission.
	 * SLOW, for internal use only!
	 * @return array
	 */
	public function getChildren() {
		if ( $this->children !== null ) {
			return $this->children;
		}

		$this->children = [];
		if ( !$this->getConfig()->get( 'CanHaveChildren' ) || !$this->exists() ) {
			return $this->children;
		}
		$context = new Context(
			RequestContext::getMain(),
			$this->getConfig()
		);
		$user = MediaWikiServices::getInstance()->getService( 'BSUtilityFactory' )
			->getMaintenanceUser()->getUser();

		$listContext = new EntityListContext\Children(
			$context,
			$this->getConfig(),
			$user,
			$this
		);
		$params = new ReaderParams( [
			'filter' => $listContext->getFilters(),
			'sort' => $listContext->getSort(),
			'limit' => ReaderParams::LIMIT_INFINITE,
			'start' => 0,
		] );
		$res = $this->getStore()->getReader( $listContext )->read( $params );
		foreach ( $res->getRecords() as $row ) {
			$entity = $this->entityFactory->newFromObject( $row->getData() );
			if ( !$entity instanceof Entity ) {
				continue;
			}
			$this->children[] = $entity;
		}

		return $this->children;
	}

	/**
	 * Checks if the entity has a parent entity
	 * @return bool
	 */
	public function hasParent() {
		return !empty( $this->get( static::ATTR_PARENT_ID, 0 ) );
	}

	/**
	 * Returns the parent entity or null, if there is non
	 * @return Entity | null
	 */
	public function getParent() {
		if ( !$this->hasParent() ) {
			return null;
		}
		return $this->entityFactory->newFromID(
			$this->get( static::ATTR_PARENT_ID, 0 ),
			static::NS
		);
	}

	/**
	 *
	 * @param \stdClass $o
	 */
	public function setValuesByObject( \stdClass $o ) {
		if ( isset( $o->{static::ATTR_PARENT_ID} ) ) {
			$this->set( static::ATTR_PARENT_ID, $o->{static::ATTR_PARENT_ID} );
		}
		parent::setValuesByObject( $o );
	}

	/**
	 *
	 * @return Title
	 */
	public function getRelatedTitle() {
		if ( $this->relatedTitle ) {
			return $this->relatedTitle;
		}
		if ( $this->hasParent() ) {
			$oParent = $this->getParent();
			if ( !$oParent ) {
				// very bad!!!
				// parent was removed menually... just do not fatal and hope for
				// the best ;)
				return \BlueSpice\Social\Extension::getDefaultRelatedTitle();
			}
			$this->relatedTitle = $oParent->getRelatedTitle();
			return $this->relatedTitle;
		}
		$this->relatedTitle
			= \BlueSpice\Social\Extension::getDefaultRelatedTitle();
		return $this->relatedTitle;
	}

	/**
	 * @param sring $sVarName
	 * @return Message
	 */
	public function getVarMessage( $sVarName ) {
		$aVarMsg = $this->getConfig()->get( 'VarMessageKeys' );
		return isset( $aVarMsg[$sVarName] )
			? wfMessage( $aVarMsg[$sVarName] )
			: wfMessage( $sVarName );
	}

	/**
	 *
	 * @param string $sAction
	 * @param User $oUser
	 * @param Title|null $oTitle
	 * @return Status
	 */
	protected function checkPermission( $sAction, User $oUser, Title $oTitle = null ) {
		// check the delete or deleteothers permission instead of the
		// read-permission when an entity is marked as archived, as not everyone
		// should be able to see archived entities
		if ( $sAction === 'read' && $this->isArchived() ) {
			$sAction = $this->userIsOwner( $oUser ) ? 'delete' : 'deleteothers';
		}

		$sPermission = $this->getConfig()->get(
			ucfirst( $sAction ) . "Permission"
		);

		if ( !$sPermission ) {
			return Status::newFatal( wfMessage(
				'bs-social-entity-fatalstatus-permission-unknownaction',
				$sAction
			) );
		}
		$oStatus = Status::newGood( $this );
		$b = MediaWikiServices::getInstance()->getHookContainer()->run( 'BSSocialEntityUserCan', [
			$this,
			$oUser,
			$sPermission,
			$oTitle,
			&$oStatus,
			$sAction
		] );
		if ( !$b || !$oStatus->isOK() ) {
			return $oStatus;
		}
		if ( $oTitle instanceof Title ) {
			if ( $oTitle->getNamespace() == NS_SOCIALENTITY ) {
				return Status::newFatal( wfMessage(
					'bs-social-entity-fatalstatus-permission-recursion'
				) );
			}
			if ( !MediaWikiServices::getInstance()
				->getPermissionManager()
				->userCan( $sPermission, $oUser, $oTitle )
			) {
				return Status::newFatal( wfMessage(
					'bs-social-entity-fatalstatus-permission-permissiondeniedusercan',
					$sAction,
					$oTitle->getFullText()
				) );
			}
			return $oStatus;
		}

		$isAllowed = MediaWikiServices::getInstance()->getPermissionManager()->userHasRight(
			$oUser,
			$sPermission
		);
		if ( !$isAllowed ) {
			return Status::newFatal( wfMessage(
				'bs-social-entity-fatalstatus-permission-permissiondenieduserisallowed',
				$sAction
			) );
		}
		return $oStatus;
	}

	/**
	 * @param string $sAction
	 * @param User|null $oUser
	 * @return Status
	 */
	public function userCan( $sAction = 'read', User $oUser = null ) {
		if ( $this->hasParent() ) {
			$status = $this->getParent()->userCan( $sAction, $oUser );
			if ( !$status->isOK() ) {
				return $status;
			}
		}
		$oTitle = null;
		if ( !$oUser instanceof User ) {
			$oUser = RequestContext::getMain()->getUser();
		}
		if ( $this->getConfig()->get( 'PermissionTitleRequired' ) ) {
			$oTitle = $this->getRelatedTitle();
			if ( !$oTitle instanceof Title ) {
				return Status::newFatal( wfMessage(
					'bs-social-entity-fatalstatus-permission-notitle'
				) );
			}
			// The default title is the mainpage. However, when this page is
			// protected, no one can add entities anymore :(
			// We dont really need a title to check the permissions in the main
			// namespace
			if ( $oTitle->getNamespace() === NS_MAIN ) {
				$oTitle = null;
			}
		}
		return $this->checkPermission( $sAction, $oUser, $oTitle );
	}

	/**
	 * Returns an array of actions, the given user can do on the Entity
	 * @param array $aActions
	 * @param User|null $oUser
	 * @return array
	 */
	public function getActions( array $aActions = [], User $oUser = null ) {
		if ( !$oUser ) {
			$oUser = RequestContext::getMain()->getUser();
		}
		$bAnon = $oUser->isAnon();

		if ( $this->userCan( 'read', $oUser ) ) {
			$aActions['read'] = [];
		} else {
			return $aActions;
		}

		if ( $this->getConfig()->get( 'IsEditable' ) && !$bAnon ) {
			if ( $this->userIsOwner( $oUser ) ) {
				$oStatus = $this->userCan( 'edit', $oUser );
			} else {
				$oStatus = $this->userCan( 'editothers', $oUser );
			}
			if ( $oStatus->isOK() ) {
				$aActions['edit'] = [];
			}
		}
		if ( $this->getConfig()->get( 'IsDeleteable' ) && !$bAnon ) {
			if ( $this->userIsOwner( $oUser ) ) {
				$oStatus = $this->userCan( 'delete', $oUser );
			} else {
				$oStatus = $this->userCan( 'deleteothers', $oUser );
			}
			if ( $oStatus->isOK() ) {
				$aActions['delete'] = [];
			}
		}
		if ( $this->getConfig()->get( 'IsCreatable' ) && !$this->exists() && !$bAnon ) {
			$oStatus = $this->userCan( 'create', $oUser );
			if ( $oStatus->isOK() ) {
				$aActions['create'] = [];
			}
		}
		if ( $this->exists() && !$bAnon ) {
			$oStatus = $this->userCan( 'source', $oUser );
			if ( $oStatus->isOK() ) {
				$aActions['source'] = [];
			}
		}

		MediaWikiServices::getInstance()->getHookContainer()->run( 'BSSocialEntityGetActions', [
			$this,
			&$aActions
		] );
		return $aActions;
	}

	/**
	 * Invalidate the cache
	 * @return Entity
	 */
	public function invalidateCache() {
		parent::invalidateCache();
		$this->getRenderer()->invalidate();
		if ( $this->getRelatedTitle() instanceof Title ) {
			$this->getRelatedTitle()->invalidateCache();
			$this->runSecondaryDataUpdates( $this->getRelatedTitle() );
		}
		if ( $this->hasParent() ) {
			$this->getParent()->invalidateCache();
		}

		return $this;
	}

	/**
	 *
	 * @return Title
	 */
	public function getBackLinkTitle() {
		return $this->getRelatedTitle();
	}

	/**
	 * @param Title $title
	 * @throws MWException
	 */
	private function runSecondaryDataUpdates( Title $title ) {
		$wikipage = WikiPage::factory( $this->getRelatedTitle() );
		$content = $wikipage->getContent();
		if ( !$content ) {
			return;
		}
		$updates = $content->getSecondaryDataUpdates( $title );
		foreach ( $updates as $update ) {
			$update->doUpdate();
		}
	}
}
