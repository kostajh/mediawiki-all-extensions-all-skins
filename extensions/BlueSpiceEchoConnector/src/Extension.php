<?php

namespace BlueSpice\EchoConnector;

use BlueSpice\EchoConnector\Notifier\NotificationsEchoNotifier;
use EchoEvent;
use MediaWiki\MediaWikiServices;
use MWException;

class Extension {

	public static function onRegistration() {
		$GLOBALS['bsgNotifierClass'] = NotificationsEchoNotifier::class;

		$GLOBALS['wgDefaultUserOptions']['echo-subscriptions-web-bs-namespace-category-notify-cat'] = 1;
		$GLOBALS['wgDefaultUserOptions']['echo-subscriptions-email-bs-namespace-category-notify-cat'] = 1;
	}

	/**
	 *
	 * @param \BlueSpice\NotificationManager $notificationsManager
	 */
	public static function registerNotifications(
		\BlueSpice\NotificationManager $notificationsManager
	) {
		$notificationsManager->registerNotificationCategory(
			'bs-admin-cat',
			[
				'priority' => 3,
				'usergroups' => [ 'sysop' ],
				'tooltip' => "echo-pref-tooltip-bs-admin-cat"
			]
		);
		$notificationsManager->registerNotificationCategory(
			'bs-namespace-category-notify-cat',
			[
				'no-dismiss' => [ 'all' ]
			]
		);
		$notificationsManager->registerNotificationCategory( 'bs-page-create-cat', [
			'priority' => 3,
			'tooltip' => "echo-pref-tooltip-bs-page-create-cat"
		] );

		$notificationsManager->registerNotificationCategory( 'bs-page-actions-cat', [
			'priority' => 3,
			'tooltip' => "echo-pref-tooltip-bs-page-actions-cat"
		] );

		$notificationsManager->registerNotification(
			'bs-adduser',
			[
				'category' => 'bs-admin-cat',
				'presentation-model' => PresentationModel\AddUserPresentationModel::class,
				'user-locators' => [ static::class . '::getSysops' ]
			]
		);

		$notificationsManager->registerNotification(
			'bs-edit',
			[
				'category' => 'bs-page-actions-cat',
				'presentation-model' => PresentationModel\EditPresentationModel::class,
				'bundle' => [
					'web' => true,
					'email' => true,
					'expandable' => true
				],
				'notifyAgent' => false,
				'user-locators' => [ static::class . '::getWatchers' ],
				'user-filters' => [ static::class . '::getEditNamespaceCategorySubscribers' ],
			]
		);

		$notificationsManager->registerNotification(
			'bs-page-in-namespace-category-edit',
			[
				'category' => 'bs-namespace-category-notify-cat',
				'presentation-model' => PresentationModel\EditPresentationModel::class,
				'bundle' => [
					'web' => true,
					'email' => true,
					'expandable' => true
				],
				'user-locators' => [ static::class . '::getEditNamespaceCategorySubscribers' ],
				'user-filters' => [ static::class . '::getWatchers' ],
			]
		);

		$notificationsManager->registerNotification(
			'bs-create',
			[
				'category' => 'bs-page-create-cat',
				'presentation-model' => PresentationModel\CreatePresentationModel::class,
				'user-locators' => [ static::class . '::getAllSubscribersForCreate' ],
				'user-filters' => [ static::class . '::getCreateNamespaceCategorySubscribers' ],
			]
		);

		$notificationsManager->registerNotification(
			'bs-page-in-namespace-category-create',
			[
				'category' => 'bs-namespace-category-notify-cat',
				'presentation-model' => PresentationModel\CreatePresentationModel::class,
				'user-locators' => [ static::class . '::getCreateNamespaceCategorySubscribers' ],
			]
		);

		$notificationsManager->registerNotification(
			'bs-delete',
			[
				'category' => 'bs-page-actions-cat',
				'presentation-model' => PresentationModel\DeletePresentationModel::class,
				'extra-params' => [
					// usually only existing titles can produce notifications
					// we do not have a title after its deleted
					'forceRender' => true
				],
				'user-locators' => [ static::class . '::getForDeletedTitle' ]
			]
		);

		$notificationsManager->registerNotification(
			'bs-move',
			[
				'category' => 'bs-page-actions-cat',
				'presentation-model' => PresentationModel\MovePresentationModel::class,
				'user-locators' => [ static::class . '::getWatchers' ]
			]
		);

		$notificationsManager->registerNotification(
			'bs-registeruser',
			[
				'category' => 'bs-admin-cat',
				'presentation-model' => PresentationModel\RegisterUserPresentationModel::class,
				'user-locators' => [ static::class . '::getSysops' ]
			]
		);
	}

	/**
	 * @param EchoEvent $event
	 * @return array
	 */
	public static function getWatchers( EchoEvent $event ) {
		/** @var UserLocator $userLocator */
		$userLocator = MediaWikiServices::getInstance()->getService(
			'BSEchoConnectorUserLocator'
		);

		return $userLocator->getWatchers(
			$event->getTitle()->getPrefixedText(),
			$event->getTitle()
		);
	}

	/**
	 * @param EchoEvent $event
	 * @return array
	 */
	public static function getSysops( EchoEvent $event ) {
		/** @var UserLocator $userLocator */
		$userLocator = MediaWikiServices::getInstance()->getService(
			'BSEchoConnectorUserLocator'
		);
		return $userLocator->getUsersFromGroups( [ 'sysop' ] );
	}

	/**
	 * @param EchoEvent $event
	 * @return array
	 */
	public static function getForDeletedTitle( EchoEvent $event ) {
		/** @var UserLocator $userLocator */
		$userLocator = MediaWikiServices::getInstance()->getService(
			'BSEchoConnectorUserLocator'
		);

		$extra = $event->getExtra();
		if ( isset( $extra['title'] ) && $extra['title'] instanceof \Title ) {
			$title = $extra['title'];
			return $userLocator->getWatchers( $title->getPrefixedText(), $extra['title'] );
		}

		return [];
	}

	/**
	 * @param EchoEvent $event
	 * @return array
	 */
	public static function getAllSubscribersForCreate( EchoEvent $event ) {
		/** @var UserLocator $userLocator */
		$userLocator = MediaWikiServices::getInstance()->getService(
			'BSEchoConnectorUserLocator'
		);

		return $userLocator->getAllSubscribed( 'bs_create', $event->getTitle() );
	}

	/**
	 * @param EchoEvent $event
	 * @return array
	 */
	public static function getCreateNamespaceCategorySubscribers( EchoEvent $event ) {
		return static::getNamespaceCategorySubscribers( $event, 'create' );
	}

	/**
	 * @param EchoEvent $event
	 * @return array
	 */
	public static function getEditNamespaceCategorySubscribers( EchoEvent $event ) {
		return static::getNamespaceCategorySubscribers( $event, 'edit' );
	}

	/**
	 * @param EchoEvent $event
	 * @param string $type
	 * @return array
	 */
	protected static function getNamespaceCategorySubscribers( EchoEvent $event, $type ) {
		/** @var UserLocator $userLocator */
		$userLocator = MediaWikiServices::getInstance()->getService(
			'BSEchoConnectorUserLocator'
		);

		try {
			return $userLocator->getUsersSubscribedToTitleCategories(
				$event->getTitle(), $type
			) + $userLocator->getUsersSubscribedToNamespace(
				$event->getTitle()->getNamespace(), $type, $event->getTitle()
			);
		} catch ( MWException $ex ) {
			return [];
		}
	}
}
