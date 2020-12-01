<?php

namespace BlueSpice\PageAssignments\Notifications\PresentationModel;

use BlueSpice\EchoConnector\EchoEventPresentationModel;

class GroupsRemove extends EchoEventPresentationModel {
	/**
	 * Gets appropriate messages keys and params
	 * for header message
	 *
	 * @return array
	 */
	public function getHeaderMessageContent() {
		$bundleKey = '';
		$bundleParams = [];

		$headerKey = 'notification-bs-pageassignments-user-group-remove-summary';
		$headerParams = [ 'agent', 'title', 'title', 'group', 'groupcount' ];

		if ( $this->distributionType == 'email' ) {
			$headerKey = 'notification-bs-pageassignments-user-group-remove-subject';
			$headerParams = [ 'agent', 'title', 'title', 'group', 'groupcount' ];
		}

		return [
			'key' => $headerKey,
			'params' => $headerParams,
			'bundle-key' => $bundleKey,
			'bundle-params' => $bundleParams
		];
	}

	/**
	 * Gets appropriate message key and params for
	 * web notification message
	 *
	 * @return array
	 */
	public function getBodyMessageContent() {
		$bodyKey = 'notification-bs-pageassignments-user-group-remove-body';
		$bodyParams = [ 'agent', 'title', 'title', 'group', 'groupcount' ];

		if ( $this->distributionType == 'email' ) {
			$bodyKey = 'notification-bs-pageassignments-user-group-remove-body';
			$bodyParams = [ 'agent', 'title', 'title', 'group', 'groupcount' ];
		}

		return [
			'key' => $bodyKey,
			'params' => $bodyParams
		];
	}

	/**
	 *
	 * @return array
	 */
	public function getSecondaryLinks() {
		if ( $this->isBundled() ) {
			// For the bundle, we don't need secondary actions
			return [];
		}

		return [ $this->getAgentLink() ];
	}
}
