<?php

namespace BlueSpice\EchoConnector\PresentationModel;

use BlueSpice\EchoConnector\EchoEventPresentationModel;

class AddUserPresentationModel extends EchoEventPresentationModel {
	/**
	 * Gets appropriate messages keys and params
	 * for header message
	 *
	 * @return array
	 */
	public function getHeaderMessageContent() {
		$bundleKey = '';
		$bundleParams = [];

		$headerKey = 'bs-notifications-addaccount';
		$headerParams = [ 'username' ];

		if ( $this->distributionType == 'email' ) {
			$headerKey = 'bs-notifications-email-addaccount-subject';
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
		$bodyKey = 'bs-notifications-web-addaccount-body';
		$bodyParams = [ 'username', 'realname' ];

		if ( $this->distributionType == 'email' ) {
			$bodyKey = 'bs-notifications-email-addaccount-body';
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
		if ( $this->isBundled() ) {
			// For the bundle, we don't need secondary actions
			return [];
		}

		$secondaryLinks = [];

		$extra = $this->event->getExtra();
		if ( !isset( $extra['secondary-links'] ) ) {
			$extra['secondary-links'] = [];
		}

		if ( !isset( $extra['secondary-links']['performer'] ) ) {
			return $secondaryLinks;
		}

		$performerParams = $extra['secondary-links']['performer'];
		$label = wfMessage(
			'bs-notifications-addaccount-performer',
			$performerParams['label-params']
		)->parse();

		$secondaryLinks[] = [
			'url' => $performerParams['url'],
			'label' => $label,
			'tooltip' => $label,
			'description' => '',
			'prioritized' => true
		];

		return $secondaryLinks;
	}

	/**
	 *
	 * @return string
	 */
	public function getIcon() {
		return 'edit-user-talk';
	}
}
