<?php

namespace BlueSpice\EchoConnector\PresentationModel;

use BlueSpice\EchoConnector\EchoEventPresentationModel;

class RegisterUserPresentationModel extends EchoEventPresentationModel {
	/**
	 * Gets appropriate messages keys and params
	 * for header message
	 *
	 * @return array
	 */
	public function getHeaderMessageContent() {
		$bundleKey = '';
		$bundleParams = [];

		$headerKey = 'bs-notifications-registeruser';
		$headerParams = [ 'username' ];

		if ( $this->distributionType == 'email' ) {
			$headerKey = 'bs-notifications-email-registeruser-subject';
			$headerParams = [ 'username', 'realname' ];
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
		$bodyKey = 'bs-notifications-web-registeruser-body';
		$bodyParams = [ 'username', 'realname' ];

		if ( $this->distributionType == 'email' ) {
			$bodyKey = 'bs-notifications-email-registeruser-body';
			$bodyParams = [ 'username', 'realname' ];
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
		return [];
	}

	/**
	 *
	 * @return bool
	 */
	public function getPrimaryLink() {
		return false;
	}

	/**
	 *
	 * @return string
	 */
	public function getIcon() {
		return 'edit-user-talk';
	}
}
