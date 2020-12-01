<?php

namespace BlueSpice\EchoConnector;

interface IParamParser {

	/**
	 *
	 * @param \EchoEvent $event
	 * @param string $distributionType
	 */
	public function __construct( \EchoEvent $event, $distributionType );

	/**
	 * Receives param name and determines value
	 * for given param based on Event data and
	 * sets the value to \Message object
	 * @param \Message $message
	 * @param array $param
	 */
	public function parseParam( \Message $message, $param );
}
