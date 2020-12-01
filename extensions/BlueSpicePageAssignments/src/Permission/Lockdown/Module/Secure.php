<?php

namespace BlueSpice\PageAssignments\Permission\Lockdown\Module;

use BlueSpice\PageAssignments\AssignmentFactory;
use Config;
use IContextSource;
use MediaWiki\MediaWikiServices;
use Message;
use Title;
use User;

class Secure extends \BlueSpice\Permission\Lockdown\Module {

	/**
	 *
	 * @var AssignmentFactory
	 */
	protected $assignmentFactory = null;

	/**
	 *
	 * @param Config $config
	 * @param IContextSource $context
	 * @param MediaWikiServices $services
	 * @param AssignmentFactory $assignmentFactory
	 */
	protected function __construct( Config $config, IContextSource $context,
		MediaWikiServices $services, AssignmentFactory $assignmentFactory ) {
		parent::__construct( $config, $context, $services );
		$this->assignmentFactory = $assignmentFactory;
	}

	/**
	 *
	 * @param Config $config
	 * @param IContextSource $context
	 * @param MediaWikiServices $services
	 * @param AssignmentFactory|null $assignmentFactory
	 * @return \static
	 */
	public static function getInstance( Config $config, IContextSource $context,
		MediaWikiServices $services, AssignmentFactory $assignmentFactory = null ) {
		if ( !$assignmentFactory ) {
				$assignmentFactory = $services->getService(
				'BSPageAssignmentsAssignmentFactory'
			);
		}
		return new static( $config, $context, $services, $assignmentFactory );
	}

	/**
	 *
	 * @param Title $title
	 * @param User $user
	 * @return bool
	 */
	public function applies( Title $title, User $user ) {
		if ( $title->getNamespace() < 0 ) {
			return false;
		}

		if ( $title->isTalkPage() ) {
			return false;
		}

		if ( !$title->exists() ) {
			return false;
		}

		$enabledNs = $this->getConfig()->get(
			'PageAssignmentsSecureEnabledNamespaces'
		);
		if ( !in_array( $title->getNamespace(), $enabledNs ) ) {
			return false;
		}

		return true;
	}

	/**
	 *
	 * @param Title $title
	 * @param User $user
	 * @param string $action
	 * @return bool
	 */
	public function mustLockdown( Title $title, User $user, $action ) {
		$rightList = $this->getConfig()->get(
			'PageAssignmentsSecureRemoveRightList'
		);
		if ( !in_array( $action, $rightList ) ) {
			return false;
		}
		$target = $this->getPageAssignmentsFactory()->newFromTargetTitle( $title );
		if ( !$target ) {
			return false;
		}

		if ( in_array( $user->getId(), $target->getAssignedUserIDs() ) ) {
			return false;
		}

		return true;
	}

	/**
	 *
	 * @param Title $title
	 * @param User $user
	 * @param string $action
	 * @return Message
	 */
	public function getLockdownReason( Title $title, User $user, $action ) {
		$target = $this->getPageAssignmentsFactory()->newFromTargetTitle( $title );
		$assignments = [];
		foreach ( $target->getAssignments() as $assignment ) {
			$assignments[] = $assignment->getKey();
		}
		$actionMsg = $this->msg( "right-$action" );
		return $this->msg(
			'bs-pageassignments-secure-lockdown-reason',
			$actionMsg->exists() ? $actionMsg : $action,
			count( $assignments ),
			implode( ', ', $assignments )
		);
	}

	/**
	 *
	 * @return AssignmentFactory
	 */
	protected function getPageAssignmentsFactory() {
		return $this->assignmentFactory;
	}

}
