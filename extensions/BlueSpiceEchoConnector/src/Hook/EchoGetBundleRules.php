<?php

namespace BlueSpice\EchoConnector\Hook;

use Config;
use EchoEvent;
use IContextSource;

abstract class EchoGetBundleRules extends \BlueSpice\Hook {
	/**
	 *
	 * @var EchoEvent
	 */
	protected $event;

	/**
	 *
	 * @var string
	 */
	protected $bundleString;

	/**
	 *
	 * @param EchoEvent $event
	 * @param string &$bundleString
	 * @return bool|null
	 */
	public static function callback( $event, &$bundleString ) {
		$className = static::class;
		$hookHandler = new $className(
			null,
			null,
			$event,
			$bundleString
		);
		return $hookHandler->process();
	}

	/**
	 *
	 * @param IContextSource $context
	 * @param Config $config
	 * @param EchoEvent $event
	 * @param string &$bundleString
	 */
	public function __construct( $context, $config, $event, &$bundleString ) {
		parent::__construct( $context, $config );

		$this->event = $event;
		$this->bundleString = &$bundleString;
	}
}
