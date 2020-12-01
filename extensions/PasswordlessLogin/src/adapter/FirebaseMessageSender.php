<?php

namespace PasswordlessLogin\adapter;

use Config;
use MWException;
use PasswordlessLogin\model\Challenge;
use PasswordlessLogin\model\Device;

class FirebaseMessageSender {
	const SERVICE_NAME = 'FirebaseMessageSender';
	const FCM_SEND_URL = 'https://fcm.googleapis.com/fcm/send';
	const FCM_CONTENT_TYPE = 'Content-Type: application/json';

	/** @var Config */
	private $config;
	/** @var string */
	private $apiUrl;

	/**
	 * FirebaseMessageSender constructor.
	 * @param Config $config
	 * @param string $apiUrl
	 */
	public function __construct( Config $config, $apiUrl ) {
		$this->config = $config;
		$this->apiUrl = $apiUrl;
	}

	/**
	 * Sends a data message to the Device with the required data from the Challenge to present
	 * the user with a login request.
	 *
	 * @param Device $device The device to send the message to
	 * @param Challenge $challenge The login challenge the device needs to solve
	 * @throws MWException
	 */
	public function send( Device $device, Challenge $challenge ) {
		$curlSession = $this->curlSession( $device, $challenge );

		$result = json_decode( curl_exec( $curlSession ) );
		curl_close( $curlSession );
		if ( $result->success !== 1 ) {
			throw new MWException( 'Message could not be sent. Response: ' . $result );
		}
	}

	private function messageContent( Device $device, Challenge $challenge ) {
		$messageContent = [
			'to' => $device->getDeviceId(),
			'data' => [
				"challenge" => $challenge->getChallenge(),
				"apiUrl" => $this->apiUrl,
			],
			"priority" => "high",
		];

		return json_encode( $messageContent );
	}

	private function messageHeaders() {
		return [
			$this->fcmAuthorization(),
			self::FCM_CONTENT_TYPE,
		];
	}

	private function fcmAuthorization() {
		return 'Authorization: key=' . $this->config->get( 'PLFirebaseAccessToken' );
	}

	private function curlSession( Device $device, Challenge $challenge ) {
		$curlSession = curl_init();
		curl_setopt( $curlSession, CURLOPT_URL, self::FCM_SEND_URL );
		curl_setopt( $curlSession, CURLOPT_POST, true );
		curl_setopt( $curlSession, CURLOPT_HTTPHEADER, $this->messageHeaders() );
		curl_setopt( $curlSession, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $curlSession, CURLOPT_POSTFIELDS,
			$this->messageContent( $device, $challenge ) );

		return $curlSession;
	}
}
