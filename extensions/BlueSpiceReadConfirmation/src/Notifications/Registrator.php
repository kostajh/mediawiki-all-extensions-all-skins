<?php

namespace BlueSpice\ReadConfirmation\Notifications;

use BlueSpice\NotificationManager;

class Registrator {

	/**
	 * Registeres base notifications used for Social Entities
	 *
	 * @param NotificationManager $notificationsManager
	 */
	public static function registerNotifications( NotificationManager $notificationsManager ) {
		$echoNotifier = $notificationsManager->getNotifier();

		$echoNotifier->registerNotificationCategory(
			'bs-readconfirmation-cat',
			[
				'priority' => 100,
				'no-dismiss' => [ 'web', 'email' ],
				'tooltip' => 'echo-pref-tooltip-bs-readconfirmation-cat'
			]
		);

		$notificationsManager->registerNotification(
			'bs-readconfirmation-remind',
			[
				'category' => 'bs-readconfirmation-cat',
				'summary-message' => 'notification-bs-readconfirmation-remind-summary',
				'email-subject-message' => 'notification-bs-readconfirmation-remind-subject',
				'email-body-message' => 'notification-bs-readconfirmation-remind-body',
				'web-body-message' => 'notification-bs-readconfirmation-remind-body',
				'summary-params' => [
					'agent', 'realname', 'title'
				],
				'email-subject-params' => [
					'agent', 'realname', 'title'
				],
				'email-body-params' => [
					'agent', 'realname', 'title'
				],
				'web-body-params' => [
					'agent', 'realname', 'title'
				],
			]
		);
	}
}
