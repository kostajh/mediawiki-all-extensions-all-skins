<?php

namespace BlueSpice\SaferEdit;

use Config;
use Title;
use User;
use Wikimedia\Rdbms\LoadBalancer;

class EditWarningBuilder {

	/**
	 *
	 * @var LoadBalancer
	 */
	protected $loadBalancer = null;

	/**
	 *
	 * @var Config
	 */
	protected $config = null;

	/**
	 *
	 * @var User
	 */
	protected $user = null;

	/**
	 *
	 * @var Title
	 */
	protected $title = null;

	/**
	 *
	 * @var string
	 */
	protected $intermediateEditUsername = '';

	/**
	 *
	 * @var string
	 */
	protected $message = '';

	/**
	 * @param LoadBalancer $loadBalancer
	 * @param Config $config
	 * @param User $user
	 * @param Title $title
	 */
	public function __construct( $loadBalancer, $config, $user, $title ) {
		$this->loadBalancer = $loadBalancer;
		$this->config = $config;
		$this->user = $user;
		$this->title = $title;
	}

	/**
	 * @return string
	 */
	public function getMessage() {
		$this->loadFromDB();
		$this->findIntermediateEdit();
		$this->makeMessage();

		return $this->message;
	}

	protected function makeMessage() {
		if ( empty( $this->intermediateEditUsername ) ) {
			return;
		}

		$showName = $this->config->get( 'SaferEditShowNameOfEditingUser' );

		$message = wfMessage( 'bs-saferedit-someone-editing' );
		if ( $showName ) {
			$message = wfMessage(
				'bs-saferedit-user-editing',
				$this->intermediateEditUsername
			);
		}

		$this->message = $message->text();
	}

	protected $intermediateEdits = [];

	protected function loadFromDB() {
		$dbr = $this->loadBalancer->getConnection( DB_REPLICA );
		$res = $dbr->select(
			'bs_saferedit',
			'*',
			[
				"se_page_title" => $this->title->getDBkey(),
				"se_page_namespace" => $this->title->getNamespace(),
			],
			__METHOD__,
			[ "ORDER BY" => "se_id DESC" ]
		);

		foreach ( $res as $row ) {
			$this->intermediateEdits[] = $row;
		}
	}

	protected function findIntermediateEdit() {
		$interval = $this->getInterval();
		$thresholdTS = wfTimestamp( TS_MW, time() - $interval );
		$currentUserName = $this->user->getName();

		foreach ( $this->intermediateEdits as $row ) {
			if ( $row->se_user_name === $currentUserName ) {
				continue;
			}

			if ( $row->se_timestamp < $thresholdTS ) {
				continue;
			}

			$this->intermediateEditUsername = $row->se_user_name;
			break;
		}
	}

	/**
	 *
	 * @return int
	 */
	protected function getInterval() {
		$saferEditInterval = $this->config->get( 'SaferEditInterval' );
		$pingInterval = $this->config->get( 'PingInterval' );

		// HINT PW from the ancient times: +1 secound response time is enough
		return $saferEditInterval + $pingInterval + 1;
	}

}
