<?php

namespace BlueSpice\EchoConnector\Job;

class SendNotification extends \Job {

	/**
	 *
	 * @param \Title $title
	 * @param array $params
	 */
	public function __construct( $title, $params ) {
		parent::__construct( 'sendNotification', $title, $params );
	}

	public function run() {
		\EchoEvent::create( $this->params );
	}
}
