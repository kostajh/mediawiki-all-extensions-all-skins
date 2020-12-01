<?php

namespace BlueSpice\Social\Notifications;

use BlueSpice\BaseNotification;
use BlueSpice\Social\Entity as SocialEntity;
use MediaWiki\MediaWikiServices;

class SocialNotification extends BaseNotification {
	const ACTION_EDIT = 'edit';
	const ACTION_CREATE = 'create';
	const ACTION_DELETE = 'delete';

	/**
	 *
	 * @var string
	 */
	protected $key;

	/**
	 *
	 * @var string
	 */
	protected $action;

	/**
	 *
	 * @var \User
	 */
	protected $user;

	/**
	 *
	 * @var string
	 */
	protected $realname;

	/**
	 *
	 * @var BlueSpice\Social\Entity
	 */
	protected $entity;

	/**
	 *
	 * @var bool
	 */
	protected $notifyAll = false;

	/**
	 *
	 * @param string $key
	 * @param SocialEntity $entity
	 * @param \User $agent
	 * @param string $action
	 */
	public function __construct( $key, SocialEntity $entity, \User $agent,
		$action = self::ACTION_EDIT ) {
		$this->key = $key;
		$this->entity = $entity;
		$this->user = $agent;
		$this->action = $action;

		$realname = MediaWikiServices::getInstance()->getService( 'BSUtilityFactory' )
			->getUserHelper( $this->user )->getDisplayName();

		$this->realname = $realname;
	}

	/**
	 *
	 * @param bool $value
	 */
	public function setNotifyAll( $value = true ) {
		$this->notifyAll = $value;
	}

	/**
	 *
	 * @return array
	 */
	public function getAudience() {
		return $this->getUsersWatching();
	}

	/**
	 *
	 * @return string
	 */
	public function getKey() {
		return $this->key . '-' . $this->action;
	}

	/**
	 *
	 * @return array
	 */
	public function getParams() {
		return [
			'entitytype' => wfMessage(
				$this->entity->getConfig()->get( 'TypeMessageKey' )
			)->plain(),
			'realname' => $this->realname,
			'primary-link-label' => wfMessage(
				'bs-social-notification-primary-link-label'
			)->plain(),
			// Todo: Find better way to diferentiate between social notifs and other
			'social-notification' => true
		];
	}

	/**
	 *
	 * @return \User
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * Returns SocialEntity this notifiaction is about
	 *
	 * @return SocialEntity
	 */
	public function getEntity() {
		return $this->entity;
	}

	/**
	 *
	 * @return \Title|null
	 */
	public function getTitle() {
		$title = $this->entity->getTitle();
		if ( $title instanceof \Title && $title->exists() ) {
			return $title;
		}
		return null;
	}

	/**
	 * Gets all users watching the given entity page
	 *
	 * @return array
	 */
	protected function getUsersWatching() {
		$users = [];

		$title = $this->getWatchedTitle();
		if ( !$title instanceof \Title || !$title->exists() ) {
			return $users;
		}

		$userIdProperty = $this->getUserIdProperty();

		$res = $this->runQuery( $title );

		foreach ( $res as $row ) {
			$user = \User::newFromId( $row->$userIdProperty );
			if ( $user instanceof \User ) {
				if ( in_array( $user->getId(), $this->getUserIdsToSkip() ) ) {
					continue;
				}

				if ( MediaWikiServices::getInstance()
					->getPermissionManager()
					->userCan( 'read', $user, $title ) == false
				) {
					continue;
				}
				$users[] = $user->getId();
			}
		}

		return $users;
	}

	/**
	 *
	 * @return \Title
	 */
	protected function getWatchedTitle() {
		return $this->entity->getTitle();
	}

	/**
	 *
	 * @param \Title $title
	 * @return bool|\Wikimedia\Rdbms\IResultWrapper
	 */
	protected function runQuery( $title ) {
		if ( $this->notifyAll ) {
			return wfGetDB( DB_REPLICA )->select(
				'user',
				'user_id'
			);
		} else {
			return wfGetDB( DB_REPLICA )->select(
				'watchlist',
				'wl_user',
				[
					'wl_namespace' => $title->getNamespace(),
					'wl_title' => $title->getText()
				]
			);
		}
	}

	/**
	 *
	 * @return string
	 */
	protected function getUserIdProperty() {
		if ( $this->notifyAll ) {
			return 'user_id';
		}
		return 'wl_user';
	}

	/**
	 *
	 * @return array
	 */
	protected function getUserIdsToSkip() {
		return [ $this->user->getId() ];
	}
}
