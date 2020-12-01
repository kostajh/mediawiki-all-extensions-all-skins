<?php

namespace BlueSpice\EchoConnector;

use BlueSpice\Data\Filter\StringValue;
use BlueSpice\Data\ReaderParams;
use BlueSpice\Data\Watchlist\Record;
use BlueSpice\EchoConnector\Data\Watchlist\Store;
use IContextSource;
use LoadBalancer;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\Permissions\PermissionManager;
use MWException;
use Title;
use User;
use WikiPage;
use WikitextContent;

class UserLocator {
	protected $loadBalancer = null;
	protected $context = null;
	/**
	 *
	 * @var PermissionManager
	 */
	protected $permissionManager = null;

	/**
	 *
	 * @var HookContainer
	 */
	protected $hookContainer = null;

	/**
	 * @param LoadBalancer $loadBalancer
	 * @param IContextSource $context
	 * @param PermissionManager $permissionManager
	 * @param HookContainer $hookContainer
	 */
	public function __construct( LoadBalancer $loadBalancer, IContextSource $context,
		PermissionManager $permissionManager, HookContainer $hookContainer ) {
		$this->loadBalancer = $loadBalancer;
		$this->context = $context;
		$this->permissionManager = $permissionManager;
		$this->hookContainer = $hookContainer;
	}

	/**
	 * Get all uses watching the title
	 *
	 * @param string $titleText
	 * @param Title $title
	 * @return User[]
	 */
	public function getWatchers( $titleText, Title $title ) {
		$wlStore = new Store( $this->context );
		$params = new ReaderParams( [
			ReaderParams::PARAM_LIMIT => ReaderParams::LIMIT_INFINITE,
			ReaderParams::PARAM_FILTER => [ [
				StringValue::KEY_COMPARISON => StringValue::COMPARISON_EQUALS,
				StringValue::KEY_PROPERTY => Record::PAGE_PREFIXED_TEXT,
				StringValue::KEY_VALUE => $titleText,
				StringValue::KEY_TYPE => 'string'
			] ]
		] );

		$users = [];
		foreach ( $wlStore->getReader()->read( $params )->getRecords() as $record ) {
			$users[] = $record->get( Record::USER_ID, 0 );
		}
		if ( empty( $users ) ) {
			return [];
		}
		return $this->getValidUsersFromIds( $users, $title );
	}

	/**
	 * Get all users from the particular group
	 *
	 * @param array $groups
	 * @return User[]
	 */
	public function getUsersFromGroups( $groups ) {
		if ( !is_array( $groups ) ) {
			$conds = 'ug_group = "' . $groups . '"';
		} else {
			$conds = [];
			foreach ( $groups as $group ) {
				$conds[] = 'ug_group = "' . $group . '"';
			}
			$conds = implode( ' OR ', $conds );
		}

		$dbr = $this->loadBalancer->getConnection( DB_REPLICA );
		$res = $dbr->select(
			'user_groups',
			'ug_user',
			$conds,
			__METHOD__
		);

		$users = [];
		foreach ( $res as $row ) {
			$users[] = $row->ug_user;
		}
		if ( empty( $users ) ) {
			return [];
		}
		return $this->getValidUsersFromIds( $users );
	}

	/**
	 * Get all users subscribed to the particular category
	 *
	 * @param string $cat Notification category
	 * @param Title $title
	 * @return User[]
	 */
	public function getAllSubscribed( $cat, Title $title ) {
		$dbr = $this->loadBalancer->getConnection( DB_REPLICA );
		$resUser = $dbr->select(
			"user_properties",
			"DISTINCT up_user",
			[
				"up_property" => [
					"echo-subscriptions-web-$cat",
					"echo-subscriptions-email-$cat"
				],
				"up_value" => 1
			],
			__METHOD__
		);
		$users = [];
		foreach ( $resUser as $row ) {
			$users[] = $row->up_user;
		}
		if ( empty( $users ) ) {
			return [];
		}
		return $this->getValidUsersFromIds( $users, $title );
	}

	/**
	 * @param int $nsId
	 * @param string $action
	 * @param Title $title
	 *
	 * @return User[]
	 */
	public function getUsersSubscribedToNamespace( $nsId, $action, Title $title ) {
		$users = $this->getUsersWithSubscriptionPreference( 'namespace', $action, $nsId );
		if ( empty( $users ) ) {
			return [];
		}
		return $this->getValidUsersFromIds( $users, $title );
	}

	/**
	 * @param Title $title
	 * @param string $action
	 * @return User[]
	 * @throws MWException
	 */
	public function getUsersSubscribedToTitleCategories( Title $title, $action ) {
		$wikipage = WikiPage::factory( $title );
		$content = $wikipage->getContent();

		if ( !$content instanceof WikitextContent ) {
			return [];
		}

		$categories = $content->getParserOutput( $title )->getCategoryLinks();

		$users = [];
		foreach ( $categories as $cat ) {
			$users += $this->getUsersWithSubscriptionPreference( 'category', $action, $cat );
		}
		if ( empty( $users ) ) {
			return [];
		}

		return $this->getValidUsersFromIds( $users, $title );
	}

	/**
	 * Get all users that have a particular user preference
	 *
	 * @param string $type
	 * @param string $action
	 * @param string $key
	 * @return int[]|array
	 */
	private function getUsersWithSubscriptionPreference( $type, $action, $key ) {
		$db = $this->loadBalancer->getConnection( DB_REPLICA );
		// Sanity, in case something calls this on install, before tables are created
		if ( !$db->tableExists( 'user_properties' ) ) {
			return [];
		}

		$prefName = "notify-$type-selectionpage-$action-$key";
		$res = $db->select(
			'user_properties',
			'up_user',
			[ 'up_property' => $prefName ],
			__METHOD__
		);
		$return = [];
		foreach ( $res as $row ) {
			$return[] = $row->up_user;
		}

		return $return;
	}

	/**
	 *
	 * @param int[] $users
	 * @param Title|null $title
	 * @return User[]
	 */
	private function getValidUsersFromIds( array $users, Title $title = null ) {
		$return = [];
		foreach ( $users as $id ) {
			if ( empty( $id ) ) {
				continue;
			}
			$user = User::newFromId( $id );
			$user->load();
			if ( !$user || $user->isAnon() || $user->isBlocked() ) {
				continue;
			}
			if ( isset( $return[ (int)$user->getId() ] ) ) {
				continue;
			}
			if ( $title ) {
				if ( !$this->permissionManager->userCan( 'read', $user, $title ) ) {
					continue;
				}
			} elseif ( !$this->permissionManager->userHasRight( $user, 'read' ) ) {
				continue;
			}
			$return[ (int)$user->getId() ] = $user;
		}
		$this->hookContainer->run( 'BlueSpiceEchoConnectorUserLocatorValidUsers', [
			&$return,
			$title
		] );
		return $return;
	}
}
