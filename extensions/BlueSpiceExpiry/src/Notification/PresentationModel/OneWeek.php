<?php

namespace BlueSpice\Expiry\Notification\PresentationModel;

use BlueSpice\EchoConnector\EchoEventPresentationModel;

class OneWeek extends EchoEventPresentationModel {
	/**
	 *
	 * @return array
	 */
	public function getHeaderMessageContent() {
		$headerKey = 'notification-bs-expiry-one-week-subject';
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
		$bodyKey = 'notification-bs-expiry-one-week-web-body';
		$bodyParams = [ 'title', 'comment' ];

		if ( $this->distributionType == 'email' ) {
			$bodyKey = 'notification-bs-expiry-one-week-email-body';
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
