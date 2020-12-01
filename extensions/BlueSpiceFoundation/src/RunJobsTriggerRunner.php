<?php

namespace BlueSpice;

use BlueSpice\RunJobsTriggerHandler\Job\RunRunJobsTriggerHandlerRunner;
use BlueSpice\RunJobsTriggerHandler\JSONFileBasedRunConditionChecker;
use ConfigException;
use JobQueueGroup;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;

class RunJobsTriggerRunner {

	/**
	 *
	 * @var IRegistry
	 */
	protected $registry = null;

	/**
	 *
	 * @var \Config
	 */
	protected $config = null;

	/**
	 *
	 * @var \Wikimedia\Rdbms\LoadBalancer
	 */
	protected $loadBalancer = null;

	/**
	 *
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger = null;

	/**
	 *
	 * @var INotifier
	 */
	protected $notifier = null;

	/**
	 *
	 * @var RunJobsTriggerHandler\IRunConditionChecker
	 */
	protected $runConditionChecker = null;

	/**
	 *
	 * @var IRunJobsTriggerHandler
	 */
	protected $currentTriggerHandler = null;

	/**
	 *
	 * @param IRegistry $registry
	 * @param \Psr\Log\LoggerInterface $logger
	 * @param RunJobsTriggerHandler\IRunConditionChecker $runConditionChecker
	 * @param \Config $config
	 * @param \Wikimedia\Rdbms\LoadBalancer $loadBalancer
	 * @param INotifier $notifier
	 */
	public function __construct( $registry, $logger, $runConditionChecker, $config,
		$loadBalancer, $notifier ) {
		$this->registry = $registry;
		$this->logger = $logger;
		$this->runConditionChecker = $runConditionChecker;
		$this->config = $config;
		$this->loadBalancer = $loadBalancer;
		$this->notifier = $notifier;
	}

	public function execute() {
		$factoryKeys = $this->registry->getAllKeys();
		foreach ( $factoryKeys as $regKey ) {
			$factoryCallback = $this->registry->getValue( $regKey );
			$this->currentTriggerHandler = call_user_func_array(
				$factoryCallback,
				[
					$this->config,
					$this->loadBalancer,
					$this->notifier
				]
			);

			$this->checkHandlerInterface( $regKey );

			if ( $this->shouldRunCurrentHandler( $regKey ) ) {
				try {
					$this->runCurrentHandler( $regKey );
				} catch ( \Exception $ex ) {
					$message = $ex->getMessage();
					$message .= "\n";
					$message .= $ex->getTraceAsString();
					$this->logger->critical( $message );
				}
			} else {
				$this->logger->info(
					"Skipped run of hanlder for '$regKey' due to"
					. "run-condition-check"
				);
			}
		}
	}

	/**
	 *
	 * @param string $regKey
	 * @return bool
	 */
	protected function shouldRunCurrentHandler( $regKey ) {
		return $this->runConditionChecker->shouldRun(
			$this->currentTriggerHandler, $regKey
		);
	}

	/**
	 *
	 * @param string $regKey
	 * @throws \Exception
	 */
	protected function checkHandlerInterface( $regKey ) {
		$doesImplementInterface =
			$this->currentTriggerHandler instanceof IRunJobsTriggerHandler;

		if ( !$doesImplementInterface ) {
			throw new \Exception(
				"RunJobsTriggerHanlder factory '$regKey' did not return "
					. "'IRunJobsTriggerHandler' instance!"
			);
		}
	}

	/**
	 * @param string $regKey
	 * @throws \Exception
	 */
	protected function runCurrentHandler( $regKey ) {
		$status = $this->currentTriggerHandler->run();
		if ( $status->isOK() ) {
			$this->logger->info(
				"Successfully ran handler for '$regKey'"
			);
		} else {
			$messageText = $status->getMessage()->plain();
			$this->logger->error(
				"There was a error during run of handler for '$regKey':"
				. "\n$messageText"
			);
		}
	}

	/**
	 * Called from $wgExtensionFunctions
	 */
	public static function runDeferred() {
		if ( !defined( 'MEDIAWIKI_JOB_RUNNER' ) ) {
			return;
		}

		JobQueueGroup::singleton()->push(
			new RunRunJobsTriggerHandlerRunner()
		);
	}

	/**
	 * Runs the runner immediately
	 *
	 * @return bool
	 * @throws ConfigException
	 */
	public static function run() {
		$services = MediaWikiServices::getInstance();

		$registry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceFoundationRunJobsTriggerHandlerRegistry'
		);

		$logger = LoggerFactory::getInstance( 'runjobs-trigger-runner' );

		$runConditionChecker = new JSONFileBasedRunConditionChecker(
			new \DateTime(),
			BSDATADIR,
			$logger,
			$services->getConfigFactory()->makeConfig( 'bsg' )
		);

		$runner = new \BlueSpice\RunJobsTriggerRunner(
			$registry,
			$logger,
			$runConditionChecker,
			$services->getConfigFactory()->makeConfig( 'bsg' ),
			$services->getDBLoadBalancer(),
			$services->getService( 'BSNotificationManager' )->getNotifier()
		);

		$runner->execute();

		return true;
	}
}
