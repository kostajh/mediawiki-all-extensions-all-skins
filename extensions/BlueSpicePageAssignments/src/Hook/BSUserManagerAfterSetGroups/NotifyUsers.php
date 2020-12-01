<?php

namespace BlueSpice\PageAssignments\Hook\BSUserManagerAfterSetGroups;

use BlueSpice\Context;
use BlueSpice\Data\Filter;
use BlueSpice\Data\ReaderParams;
use BlueSpice\PageAssignments\Data\Assignment\Store;
use BlueSpice\PageAssignments\Data\Record;
use BlueSpice\PageAssignments\Notifications\GroupsAdd;
use BlueSpice\PageAssignments\Notifications\GroupsRemove;
use BlueSpice\UserManager\Hook\BSUserManagerAfterSetGroups;
use MediaWiki\MediaWikiServices;

class NotifyUsers extends BSUserManagerAfterSetGroups {
	/** @var array */
	protected $groupsWithAssignments = [];

	protected function skipProcessing() {
		if ( empty( $this->removeGroups ) && empty( $this->addGroups ) ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$notificationsManager = MediaWikiServices::getInstance()
			->getService( 'BSNotificationManager' );
		$notifier = $notificationsManager->getNotifier();
		$this->getGroupsWithAssignments();

		if ( empty( $this->groupsWithAssignments ) ) {
			return true;
		}

		$agent = $this->getContext()->getUser();
		$added = $this->getAssigned(
			array_diff( $this->addGroups, $this->excludeGroups )
		);
		$removed = $this->getAssigned(
			array_diff( $this->removeGroups, $this->excludeGroups )
		);

		if ( !empty( $removed ) ) {
			$notification = new GroupsRemove( $agent, $this->user, $removed );
			$notifier->notify( $notification );
		}
		if ( !empty( $added ) ) {
			$notification = new GroupsAdd( $agent, $this->user, $added );
			$notifier->notify( $notification );
		}

		return true;
	}

	/**
	 *
	 * @return Store
	 */
	private function getStore() {
		return new Store(
			new Context( $this->getContext(), $this->getConfig() ),
			MediaWikiServices::getInstance()->getDBLoadBalancer()
		);
	}

	/**
	 * @param array $groups
	 * @return array
	 */
	private function getAssigned( $groups ) {
		return array_intersect( $groups, $this->groupsWithAssignments );
	}

	/**
	 * Get all assignments of type group
	 */
	private function getGroupsWithAssignments() {
		$recordSet = $this->getStore()->getReader()->read(
			new ReaderParams( [ 'filter' => [
				[
					Filter::KEY_FIELD => Record::ASSIGNEE_TYPE,
					Filter::KEY_VALUE => 'group',
					Filter::KEY_TYPE => 'string',
					Filter::KEY_COMPARISON => Filter::COMPARISON_EQUALS,
				]
			] ] )
		);

		/** @var Record $record */
		foreach ( $recordSet->getRecords() as $record ) {
			$this->groupsWithAssignments[] = $record->get( Record::ASSIGNEE_KEY );
		}

		$this->groupsWithAssignments = array_unique( $this->groupsWithAssignments );
	}
}
