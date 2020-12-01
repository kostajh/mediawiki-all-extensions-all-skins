<?php

namespace BlueSpice\Social\Comments\Notification;

use BlueSpice\NotificationManager;

class Registrator {
	/**
	 *
	 * @param NotificationManager $notificationsManager
	 */
	public static function registerNotifications( NotificationManager $notificationsManager ) {
		$echoNotifier = $notificationsManager->getNotifier();

		$echoNotifier->registerNotificationCategory(
			'bs-social-comment-cat',
			[ 'priority' => 3 ]
		);

		$config = [
			'category' => 'bs-social-comment-cat',
			'summary-params' => [
				'agent', 'realname'
			],
			'email-subject-params' => [
				'agent', 'realname'
			],
			'email-body-params' => [
				'agent', 'realname', 'parentownerrealname', 'entitytext'
			],
			'web-body-params' => [
				'agent', 'realname', 'parentownerrealname', 'entitytext'
			],
			'extra-params' => [
				'secondary-links' => [
					'agentlink' => [],
					'parententity' => [
						'prioritized' => true,
						'label' => 'bs-social-notification-comment-parententity-label'
					],
					'parentowner' => [
						'prioritized' => true,
						'label' => 'bs-social-notification-comment-parentowner-label',
						'icon' => 'userAvatar'
					]
				]
			]
		];

		$notificationsManager->registerNotification(
			'bs-social-comment-create',
			array_merge( $config, [
				'summary-message' => 'bs-social-notifications-comment-create',
				'email-subject-message' => 'bs-social-notifications-email-comment-create-subject',
				'email-body-message' => 'bs-social-notifications-email-comment-create-body',
				'web-body-message' => 'bs-social-notifications-web-comment-create-body',
			] )
		);

		$notificationsManager->registerNotification(
			'bs-social-comment-edit',
			array_merge( $config, [
				'summary-message' => 'bs-social-notifications-comment-edit',
				'email-subject-message' => 'bs-social-notifications-email-comment-edit-subject',
				'email-body-message' => 'bs-social-notifications-email-comment-edit-body',
				'web-body-message' => 'bs-social-notifications-web-comment-edit-body'
			] )
		);

		$notificationsManager->registerNotification(
			'bs-social-comment-delete',
			array_merge( $config, [
				'summary-message' => 'bs-social-notifications-comment-delete',
				'email-subject-message' => 'bs-social-notifications-email-comment-delete-subject',
				'email-body-message' => 'bs-social-notifications-email-comment-delete-body',
				'email-body-params' => [
					'agent', 'realname', 'parentownerrealname'
				],
				'web-body-message' => 'bs-social-notifications-web-comment-delete-body',
			] )
		);

		$notificationsManager->registerNotification(
			'bs-social-comment-owner-create',
			array_merge( $config, [
				'section' => 'message',
				'summary-message' => 'bs-social-notifications-comment-owner-create',
				'email-subject-message' => 'bs-social-notifications-email-comment-owner-create-subject',
				'email-body-message' => 'bs-social-notifications-email-comment-owner-create-body',
				'email-body-params' => [
					'agent', 'realname', 'entitytext'
				],
				'web-body-message' => 'bs-social-notifications-web-comment-owner-create-body',
				'web-body-params' => [
					'agent', 'realname', 'entitytext'
				]
			] )
		);

		$notificationsManager->registerNotification(
			'bs-social-comment-owner-edit',
			array_merge( $config, [
				'section' => 'message',
				'summary-message' => 'bs-social-notifications-comment-owner-edit',
				'email-subject-message' => 'bs-social-notifications-email-comment-owner-edit-subject',
				'email-body-message' => 'bs-social-notifications-email-comment-owner-edit-body',
				'email-body-params' => [
					'agent', 'realname', 'entitytext'
				],
				'web-body-message' => 'bs-social-notifications-web-comment-owner-edit-body',
				'web-body-params' => [
					'agent', 'realname', 'entitytext'
				],
				'extra-params' => [
					'bundle' => [
						'web' => true,
						'email' => true,
						'expandable' => true,
						'bundle-message' => 'bs-social-notifications-comment-owner-edit-bundle',
						'bundle-params' => []
					]
				]
			] )
		);

		$notificationsManager->registerNotification(
			'bs-social-comment-owner-delete',
			array_merge( $config, [
				'section' => 'message',
				'summary-message' => 'bs-social-notifications-comment-owner-delete',
				'email-subject-message' => 'bs-social-notifications-email-comment-owner-delete-subject',
				'email-body-message' => 'bs-social-notifications-email-comment-owner-delete-body',
				'email-body-params' => [
					'agent', 'realname'
				],
				'web-body-message' => 'bs-social-notifications-web-comment-owner-delete-body',
			] )
		);
	}
}
