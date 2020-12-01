<?php

namespace BlueSpice\ReadConfirmation\Mechanism;

use BlueSpice\NotificationManager;
use BlueSpice\PageAssignments\AssignmentFactory;
use BlueSpice\PageAssignments\TitleTarget;
use BlueSpice\ReadConfirmation\IMechanism;
use BlueSpice\ReadConfirmation\Notifications\Remind;
use MediaWiki\MediaWikiServices;
use Title;
use User;
use Wikimedia\Rdbms\LoadBalancer;

class NonMinorEdit implements IMechanism {

	/**
	 * @var LoadBalancer
	 */
	private $dbLoadBalancer;

	/**
	 * @var int|null
	 */
	private $revisionId = null;

	/**
	 * @var array
	 */
	private $enabledNamespaces;

	/**
	 * @return NonMinorEdit
	 */
	public static function factory() {
		global $wgNamespacesWithEnabledReadConfirmation;
		$dbLoadBalancer = MediaWikiServices::getInstance()->getDBLoadBalancer();
		return new self(
			$dbLoadBalancer,
			$wgNamespacesWithEnabledReadConfirmation
		);
	}

	/**
	 * NonMinorEdit constructor.
	 * @param LoadBalancer $dbLoadBalancer
	 * @param array $enabledNamespaces
	 */
	protected function __construct( $dbLoadBalancer, $enabledNamespaces ) {
		$this->dbLoadBalancer = $dbLoadBalancer;
		$this->enabledNamespaces = $enabledNamespaces;
	}

	/**
	 * @return void
	 */
	public function wireUpNotificationTrigger() {
	}

	/**
	 * @return void
	 */
	public function autoNotify() {
	}

	/**
	 * @param Title $title
	 * @param User $userAgent
	 * @return User[]|bool
	 */
	public function notify( Title $title, User $userAgent ) {
		$target = $this->getTargetFromTitle( $title );
		if ( $target === false ) {
			return false;
		}
		/** @var NotificationManager $notificationsManager */
		$notificationsManager = MediaWikiServices::getInstance()->getService(
			'BSNotificationManager'
		);
		$notifier = $notificationsManager->getNotifier();
		$notifyUsers = $this->getNotifyUsers( $target );
		$notification = new Remind( $userAgent, $title, [], $notifyUsers );
		$notifier->notify( $notification );

		$notifiedUsers = [];
		foreach ( $notifyUsers as $userId ) {
			$user = User::newFromId( $userId );
			if ( !$user ) {
				continue;
			}
			$notifiedUsers[] = $user;

		}

		return $notifiedUsers;
	}

	/**
	 * @param Title $title
	 * @param User $user
	 * @param null $revId
	 * @return bool
	 */
	public function canConfirm( Title $title, User $user, $revId = null ) {
		$currentReadConfirmations = $this->getCurrentReadConfirmations(
			[ $user->getId() ],
			[ $title->getArticleID() ]
		);

		if ( isset( $currentReadConfirmations[ $title->getArticleID() ][ $user->getId() ] ) ) {
			return false;
		}

		$target = $this->getTargetFromTitle( $title );
		if ( $target === false || !$target->isUserAssigned( $user ) ) {
			return false;
			/**
			 * If the user is not assigned we bail out telling
			 * the caller that it already has been confirmed. This is not the
			 * truth and therefore not nice, but for the time being it is
			 * sufficient. Better solution would probably be to throw an
			 * exception
			 */
		}

		$revision = $this->getRecentRevisions( [ $title->getArticleID() ] );
		if ( count( $revision ) < 1 || !isset( $revision[ $title->getArticleID() ] ) ) {
			return false;
		}

		$this->revisionId = $revision[ $title->getArticleID() ];

		return true;
	}

	/**
	 * @param Title $title
	 * @param User $user
	 * @param null $revId
	 * @return bool
	 */
	public function confirm( Title $title, User $user, $revId = null ) {
		if ( !$this->canConfirm( $title, $user, $revId ) ) {
			return false;
		}

		$row = [
			'rc_rev_id' => $this->revisionId,
			'rc_user_id' => $user->getId()
		];

		$this->dbLoadBalancer->getConnection( DB_MASTER )->delete( 'bs_readconfirmation', $row );
		$row[ 'rc_timestamp' ] = wfTimestampNow();
		$this->dbLoadBalancer->getConnection( DB_MASTER )->insert( 'bs_readconfirmation', $row );

		return true;
	}

	/**
	 * @param array $userIds
	 * @param array $pageIds
	 * @return array [ <page_id> => [ <user_id1>, <user_id2>, ...], ... ]
	 */
	public function getCurrentReadConfirmations( array $userIds = [], array $pageIds = [] ) {
		$currentReadConfirmations = [];
		$userReadRevisions = $this->getUserReadRevisions( $userIds );
		$recentRevisions = $this->getRecentRevisions( $pageIds );

		foreach ( $pageIds as $pageId ) {
			$reads = [];
			if (
				isset( $recentRevisions[ $pageId ] ) &&
				isset( $userReadRevisions[ $recentRevisions[ $pageId ] ] )
			) {
				$reads = $userReadRevisions[ $recentRevisions[ $pageId ] ];
			}
			$currentReadConfirmations[ $pageId ] = $reads;
		}
		return $currentReadConfirmations;
	}

	/**
	 * @param TitleTarget $target
	 * @return array
	 */
	private function getNotifyUsers( $target ) {
		$assignedUserIds = $target->getAssignedUserIDs();
		$currentReads = $this->getCurrentReadConfirmations(
			$assignedUserIds,
			[ $target->getTitle()->getArticleID() ]
		);

		$affectedUsers = array_filter(
			$target->getAssignedUserIDs(),
			function ( $e ) use( $target, $currentReads ) {
				return !isset( $currentReads[$target->getTitle()->getArticleID()][$e] );
			}
		);

		return $affectedUsers;
	}

	/**
	 * @param Title $title
	 * @return TitleTarget|bool
	 */
	private function getTargetFromTitle( $title ) {
		if ( $title instanceof Title === false ) {
			return false;
		}

		if ( !$this->isNamespaceEnabled( $title ) ) {
			return false;
		}

		/** @var TitleTarget $target */
		$target = $this->getAssignmentFactory()->newFromTargetTitle( $title );
		if ( $target === false || empty( $target->getAssignedUserIDs() ) ) {
			return false;
		}

		return $target;
	}

	/**
	 * @param array $pageIds
	 * @return array in form [ <page_id> => <rev_id>, ... ]
	 */
	private function getRecentRevisions( $pageIds = [] ) {
		$recentData = [];

		$conds = [ 'rev_minor_edit' => 0 ];

		if ( !empty( $pageIds ) ) {
			$conds['rev_page'] = $pageIds;
		}

		$res = $this->dbLoadBalancer->getConnection( DB_REPLICA )->select(
			'revision',
			[ 'rev_id', 'rev_page', 'rev_minor_edit' ],
			$conds,
			__METHOD__,
			[ 'ORDER BY' => 'rev_id DESC' ]
		);

		foreach ( $res as $row ) {
			$pageId = (int)$row->rev_page;
			if ( isset( $recentData[$pageId] ) ) {
				continue;
				// This way we get only the latest revisions
			}
			$recentData[$pageId] = (int)$row->rev_id;
		}

		return $recentData;
	}

	/**
	 * @param array $userIds
	 * @return array
	 */
	private function getUserReadRevisions( $userIds = [] ) {
		$conds = [];
		if ( !empty( $userIds ) ) {
			$conds['rc_user_id'] = $userIds;
		}
		$res = $this->dbLoadBalancer
			->getConnection( DB_REPLICA )
			->select( 'bs_readconfirmation', '*', $conds, __METHOD__ );

		$readRevisions = [];
		foreach ( $res as $row ) {
			$revId = (int)$row->rc_rev_id;
			if ( !isset( $readRevisions[ $revId ] ) ) {
				$readRevisions[ $revId ] = [];
			}
			$readRevisions[ $revId ][ (int)$row->rc_user_id ] = $row->rc_timestamp;
		}

		return $readRevisions;
	}

	/**
	 * @param Title $title
	 * @return bool
	 */
	private function isNamespaceEnabled( $title ) {
		if ( !$title instanceof Title ) {
			return true;
		}
		$ns = $title->getNamespace();
		if ( isset( $this->enabledNamespaces[$ns] ) && $this->enabledNamespaces[$ns] ) {
			return true;
		}
		return false;
	}

	/**
	 * @return AssignmentFactory
	 */
	private function getAssignmentFactory() {
		$factory = MediaWikiServices::getInstance()->getService(
			'BSPageAssignmentsAssignmentFactory'
		);
		return $factory;
	}

	/**
	 * @param Title $title
	 * @return bool
	 */
	public function mustRead( Title $title ) {
		if ( !$title instanceof Title ) {
			return false;
		}
		if ( !isset( $this->enabledNamespaces[$title->getNamespace()] )
			|| !$this->enabledNamespaces[$title->getNamespace()] ) {
			return false;
		}
		return true;
	}
}
