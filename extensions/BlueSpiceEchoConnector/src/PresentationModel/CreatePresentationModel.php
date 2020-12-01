<?php

namespace BlueSpice\EchoConnector\PresentationModel;

use BlueSpice\EchoConnector\EchoEventPresentationModel;

class CreatePresentationModel extends EchoEventPresentationModel {
	/**
	 * Gets appropriate messages keys and params
	 * for header message
	 *
	 * @return array
	 */
	public function getHeaderMessageContent() {
		$headerKey = 'bs-notifications-create';
		$headerParams = [ 'title' ];

		if ( $this->distributionType == 'email' ) {
			$headerKey = 'bs-notifications-email-create-subject';
			$headerParams = [ 'title', 'agent', 'realname', 'time' ];
		}

		return [
			'key' => $headerKey,
			'params' => $headerParams,
			'bundle-key' => '',
			'bundle-params' => []
		];
	}

	/**
	 * Gets appropriate message key and params for
	 * web notification message
	 *
	 * @return array
	 */
	public function getBodyMessageContent() {
		$bodyKey = 'bs-notifications-web-create-body';
		$bodyParams = [ 'title', 'agent', 'realname', 'time' ];

		if ( $this->distributionType == 'email' ) {
			$bodyKey = 'bs-notifications-email-create-body';
			$bodyParams = [ 'title', 'agent', 'realname', 'summary' ];
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
		return 'edit';
	}
}
