<?php

namespace BlueSpice\Expiry\Notification\PresentationModel;

use BlueSpice\EchoConnector\EchoEventPresentationModel;

class Today extends EchoEventPresentationModel {
	/**
	 *
	 * @return array
	 */
	public function getHeaderMessageContent() {
		$headerKey = 'notification-bs-expiry-today-subject';
		$headerParams = [ 'title' ];

		return [
			'key' => $headerKey,
			'params' => $headerParams
		];
	}

	/**
	 * Gets appropriate message key and params for
	 * web notification message
	 *
	 * @return array
	 */
	public function getBodyMessageContent() {
		$bodyKey = 'notification-bs-expiry-today-web-body';
		$bodyParams = [ 'title', 'comment' ];

		if ( $this->distributionType == 'email' ) {
			$bodyKey = 'notification-bs-expiry-today-email-body';
		}

		return [
			'key' => $bodyKey,
			'params' => $bodyParams
		];
	}

	/**
	 *
	 * @return string
	 */
	public function getIcon() {
		return 'expiry';
	}
}
