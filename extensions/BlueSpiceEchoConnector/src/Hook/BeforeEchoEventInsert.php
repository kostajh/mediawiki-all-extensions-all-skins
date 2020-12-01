<?php

namespace BlueSpice\EchoConnector\Hook;

use Config;
use EchoEvent;
use IContextSource;

abstract class BeforeEchoEventInsert extends \BlueSpice\Hook {
	/**
	 *
	 * @var EchoEvent
	 */
	protected $event;

	/**
	 *
	 * @param EchoEvent $event
	 * @return bool|null
	 */
	public static function callback( $event ) {
		$className = static::class;
		$hookHandler = new $className(
			null,
			null,
			$event
		);
		return $hookHandler->process();
	}

	/**
	 *
	 * @param IContextSource $context
	 * @param Config $config
	 * @param EchoEvent $event
	 */
	public function __construct( $context, $config, $event ) {
		parent::__construct( $context, $config );

		$this->event = $event;
	}
}
