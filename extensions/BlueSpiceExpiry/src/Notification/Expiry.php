<?php

namespace BlueSpice\Expiry\Notification;

use BlueSpice\BaseNotification;
use BlueSpice\Expiry\Notification\PresentationModel\OneWeek;
use BlueSpice\Expiry\Notification\PresentationModel\Today;
use BlueSpice\NotificationManager;
use Title;
use User;

abstract class Expiry extends BaseNotification {

	/**
	 * @param string $type
	 * @param User $agent
	 * @param Title $title
	 * @param User[] $affectedUsers
	 * @param string $comment
	 */
	public function __construct( $type, $agent, $title, $affectedUsers, $comment = '' ) {
		$params = [ 'comment' => $comment ];
		parent::__construct( $type, $agent, $title, $params );

		$audience = [];
		foreach ( $affectedUsers as $user ) {
			if ( !$user instanceof User || $user->isAnon() ) {
				return;
			}
			$audience[] = $user;
		}
		$this->addAffectedUsers( $audience );
	}

	/**
	 *
	 * @param NotificationManager $manager
	 */
	public static function registerNotifications( NotificationManager $manager ) {
		$echoNotifier = $manager->getNotifier();

		$echoNotifier->registerNotificationCategory( 'bs-expiry-daily-cat', [
			'tooltip' => 'echo-pref-tooltip-bs-expiry-daily-cat'
		] );
		$echoNotifier->registerNotificationCategory( 'bs-expiry-weekly-cat', [
			'tooltip' => 'echo-pref-tooltip-bs-expiry-daily-cat'
		] );

		$manager->registerNotification( Daily::TYPE, [
			'category' => 'bs-expiry-daily-cat',
			'presentation-model' => Today::class
		] );

		$manager->registerNotification( Weekly::TYPE, [
			'category' => 'bs-expiry-weekly-cat',
			'presentation-model' => OneWeek::class
		] );
	}
}
