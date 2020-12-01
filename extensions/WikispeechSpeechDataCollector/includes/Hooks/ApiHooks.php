<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Hooks;

/**
 * @file
 * @ingroup Extensions
 * @license GPL-2.0-or-later
 */

use ApiMain;
use Config;
use ConfigFactory;
use MediaWiki\Hook\ApiBeforeMainHook;
use MediaWiki\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;

/**
 * Class WikispeechSpeechDataCollectorHooks
 *
 * @since 0.1.0
 */
class ApiHooks
	implements ApiBeforeMainHook
{
	/** @var Config */
	private $config;

	/** @var LoggerInterface */
	private $logger;

	/**
	 * WikispeechSpeechDataCollectorHooks constructor.
	 *
	 * @param ConfigFactory $configFactory
	 */
	public function __construct( $configFactory ) {
		$this->config = $configFactory->makeConfig( 'wikispeech-speech-data-collector' );
		$this->logger = LoggerFactory::getInstance( __CLASS__ );
	}

	/**
	 * Investigates whether or not configuration is valid.
	 *
	 * Writes all invalid configuration entries to the log.
	 *
	 * @since 0.1.0
	 * @return bool true if all configuration passes validation
	 */
	private function validateConfiguration() {
		$success = true;

		// @todo Validate configuration values. Set success to false in case of failure
		// and log a warning or error depending on severity.

		return $success;
	}

	/**
	 * Hook for ApiBeforeMain.
	 *
	 * Calls configuration validation for logging purposes on API calls,
	 * but doesn't stop the use of the API due to invalid configuration.
	 * Generally a user would not call the API at this point as the module
	 * wouldn't actually have been added in onBeforePageDisplay.
	 *
	 * @since 0.1.0
	 * @param ApiMain &$main The ApiMain instance being used.
	 */
	public function onApiBeforeMain( &$main ) {
		$this->validateConfiguration();
	}

}
