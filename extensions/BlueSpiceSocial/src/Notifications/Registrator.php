<?php

namespace BlueSpice\Social\Notifications;

class Registrator {

	/**
	 * Registeres base notifications used for Social Entities
	 *
	 * @param \BlueSpice\NotificationManager $notificationsManager
	 */
	public static function registerNotifications(
		\BlueSpice\NotificationManager $notificationsManager ) {
		$echoNotifier = $notificationsManager->getNotifier();

		$echoNotifier->registerNotificationCategory( 'bs-social-entity-cat', [ 'priority' => 3 ] );

		$config = [
			'category' => 'bs-social-entity-cat',
			'summary-params' => [
				'entitytype'
			],
			'email-subject-params' => [
				'entitytype'
			],
			'email-body-params' => [
				'agent', 'realname', 'entitytype'
			],
			'web-body-params' => [
				'agent', 'realname', 'entitytype'
			],
			'extra-params' => [
				'secondary-links' => [
					'agentlink' => []
				]
			]
		];

		$notificationsManager->registerNotification(
			'bs-social-entity-create',
			array_merge( $config, [
				'summary-message' => 'bs-social-notifications-entity-create',
				'email-subject-message' => 'bs-social-notifications-email-entity-create-subject',
				'email-body-message' => 'bs-social-notifications-email-entity-create-body',
				'web-body-message' => 'bs-social-notifications-web-entity-create-body',
			] )
		);

		$notificationsManager->registerNotification(
			'bs-social-entity-text-create',
			array_merge( $config, [
				'summary-message' => 'bs-social-notifications-entity-create',
				'email-subject-message' => 'bs-social-notifications-email-entity-create-subject',
				'email-body-message' => 'bs-social-notifications-email-entity-create-text-body',
				'email-body-params' => [
					'agent', 'realname', 'entitytype', 'entitytext'
				],
				'web-body-message' => 'bs-social-notifications-web-entity-create-body',
			] )
		);

		$notificationsManager->registerNotification(
			'bs-social-entity-edit',
			array_merge( $config, [
				'summary-message' => 'bs-social-notifications-entity-edit',
				'email-subject-message' => 'bs-social-notifications-email-entity-edit-subject',
				'email-body-message' => 'bs-social-notifications-email-entity-edit-body',
				'web-body-message' => 'bs-social-notifications-web-entity-edit-body'
			] )
		);

		$notificationsManager->registerNotification(
			'bs-social-entity-text-edit',
			array_merge( $config, [
				'summary-message' => 'bs-social-notifications-entity-edit',
				'email-subject-message' => 'bs-social-notifications-email-entity-edit-subject',
				'email-body-message' => 'bs-social-notifications-email-entity-edit-text-body',
				'email-body-params' => [
					'agent', 'realname', 'entitytype', 'entitytext'
				],
				'web-body-message' => 'bs-social-notifications-web-entity-edit-body'
			] )
		);

		$notificationsManager->registerNotification(
			'bs-social-entity-delete',
			array_merge( $config, [
				'summary-message' => 'bs-social-notifications-entity-delete',
				'email-subject-message' => 'bs-social-notifications-email-entity-delete-subject',
				'email-body-message' => 'bs-social-notifications-email-entity-delete-body',
				'web-body-message' => 'bs-social-notifications-web-entity-delete-body',
			] )
		);

		$notificationsManager->registerNotification(
			'bs-social-entity-text-delete',
			array_merge( $config, [
				'summary-message' => 'bs-social-notifications-entity-delete',
				'email-subject-message' => 'bs-social-notifications-email-entity-delete-subject',
				'email-body-message' => 'bs-social-notifications-email-entity-delete-body',
				'web-body-message' => 'bs-social-notifications-web-entity-delete-body',
			] )
		);
	}
}
