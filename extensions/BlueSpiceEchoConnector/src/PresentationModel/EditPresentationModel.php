<?php

namespace BlueSpice\EchoConnector\PresentationModel;

use BlueSpice\EchoConnector\EchoEventPresentationModel;

class EditPresentationModel extends EchoEventPresentationModel {
	/**
	 * Gets appropriate messages keys and params
	 * for header message
	 *
	 * @return array
	 */
	public function getHeaderMessageContent() {
		$bundleKey = '';
		$bundleParams = [];
		if ( isset( $this->notificationConfig['bundle'] ) ) {
			$bundleKey = 'bs-notifications-edit-bundle';
			$bundleParams = [ 'title' ];
		}

		$headerKey = 'bs-notifications-edit';
		$headerParams = [ 'title' ];

		if ( $this->distributionType == 'email' ) {
			$headerKey = 'bs-notifications-email-edit-subject';
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
	 * Gets appropriate message key and params for
	 * web notification message
	 *
	 * @return array
	 */
	public function getBodyMessageContent() {
		$bodyKey = 'bs-notifications-web-edit-body';
		$bodyParams = [ 'title', 'agent', 'realname' ];

		if ( $this->distributionType == 'email' ) {
			$bodyKey = 'bs-notifications-email-edit-body';
			$bodyParams = [ 'title', 'agent', 'summary', 'realname', 'time' ];
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

		$secondaryLinks = [ $this->getAgentLink() ];

		$extra = $this->event->getExtra();
		if ( !isset( $extra['secondary-links'] ) ) {
			$extra['secondary-links'] = [];
		}

		if ( !isset( $extra['secondary-links']['difflink'] ) ) {
			return $secondaryLinks;
		}

		$diffLinkValue = $extra['secondary-links']['difflink'];
		$label = wfMessage( 'bs-notifications-edit-difflink-label' )->plain();

		$secondaryLinks[] = [
			'url' => $diffLinkValue,
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
		return 'edit';
	}
}
