<?php

namespace BlueSpice\EchoConnector\PresentationModel;

use BlueSpice\EchoConnector\EchoEventPresentationModel;

class DeletePresentationModel extends EchoEventPresentationModel {
	/**
	 * Gets appropriate messages keys and params
	 * for header message
	 *
	 * @return array
	 */
	public function getHeaderMessageContent() {
		$bundleKey = '';
		$bundleParams = [];

		$headerKey = 'bs-notifications-delete';
		$headerParams = [ 'title' ];

		if ( $this->distributionType == 'email' ) {
			$headerKey = 'bs-notifications-email-delete-subject';
			$headerParams = [ 'title', 'agent', 'realname', 'time' ];
		}

		return [
			'key' => $headerKey,
			'params' => $headerParams,
			'bundle-key' => $bundleKey,
			'bundle-params' => $bundleParams
		];
	}

	/**
	 *
	 * @return array|false
	 */
	public function getPrimaryLink() {
		return false;
	}

	/**
	 * Gets appropriate message key and params for
	 * web notification message
	 *
	 * @return array
	 */
	public function getBodyMessageContent() {
		$bodyKey = 'bs-notifications-web-delete-body';
		$bodyParams = [ 'title', 'agent', 'realname', 'time' ];

		if ( $this->distributionType == 'email' ) {
			$bodyKey = 'bs-notifications-email-delete-body';
			$bodyParams = [ 'title', 'agent', 'realname', 'deletereason' ];
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

	/**
	 *
	 * @return string
	 */
	public function getIcon() {
		return 'delete';
	}
}
