<?php

namespace BlueSpice\Expiry\RunJobsTriggerHandler;

use BlueSpice\Expiry\Data\Record;
use BlueSpice\Expiry\Factory;
use BlueSpice\Expiry\Utils;
use BlueSpice\INotifier;
use BlueSpice\IRunJobsTriggerHandler;
use BlueSpice\RunJobsTriggerHandler;
use BlueSpice\UtilityFactory;
use Config;
use MediaWiki\MediaWikiServices;
use Status;
use Title;
use Wikimedia\Rdbms\LoadBalancer;

abstract class SendNotification extends RunJobsTriggerHandler {

	/**
	 *
	 * @var Factory
	 */
	protected $factory = null;

	/**
	 *
	 * @var UtilityFactory
	 */
	protected $util = null;

	/**
	 *
	 * @param Config $config
	 * @param LoadBalancer $loadBalancer
	 * @param INotifier $notifier
	 * @param Factory $factory
	 * @param UtilityFactory $util
	 */
	public function __construct( $config, $loadBalancer, $notifier, $factory, $util ) {
		parent::__construct( $config, $loadBalancer, $notifier );
		$this->factory = $factory;
		$this->util = $util;
	}

	/**
	 * @param Config $config
	 * @param LoadBalancer $loadBalancer
	 * @param INotifier $notifier
	 * @return IRunJobsTriggerHandler
	 * @param Factory|null $factory
	 * @param UtilityFactory|null $util
	 */
	public static function factory( $config, $loadBalancer, $notifier,
		Factory $factory = null, UtilityFactory $util = null ) {
		if ( !$factory ) {
			$factory = MediaWikiServices::getInstance()->getService(
				'BSExpiryFactory'
			);
		}
		if ( !$util ) {
			$util = MediaWikiServices::getInstance()->getService(
				'BSUtilityFactory'
			);
		}
		return new static( $config, $loadBalancer, $notifier, $factory, $util );
	}

	/**
	 * @return Status
	 */
	protected function doRun() {
		$status = Status::newGood();

		$utils = new Utils( $this->loadBalancer );
		$this->expiredTitles = $this->getExpiredTitles();
		if ( empty( $this->expiredTitles ) ) {
			return $status;
		}

		foreach ( $this->expiredTitles as $title ) {
			$expiry = $this->factory->newFromTitle( $title );
			$usersToNotify = $utils->getPageModerators( $title );
			if ( empty( $usersToNotify ) ) {
				// TODO: Use some kind of extended Data\User\Store and filter for
				// group sysop!
				$usersToNotify = $utils->getSysops();
			}
			if ( empty( $usersToNotify ) ) {
				continue;
			}

			$this->sendNotifications( $title, $expiry, $usersToNotify );
		}

		return $status;
	}

	/**
	 * @return Title[]
	 */
	protected function getExpiredTitles() {
		return $this->factory->getExpiredTitles( null );
	}

	/**
	 * @param Title $title
	 * @param Record $record
	 * @param User[] $users
	 */
	abstract protected function sendNotifications( Title $title, Record $record, array $users );
}
