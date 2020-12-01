<?php

namespace BlueSpice\Social\Hook\BSEntitySaveComplete;

use BlueSpice\Hook\BSEntitySaveComplete;
use BlueSpice\Social\Notifications\SocialNotification;
use MediaWiki\MediaWikiServices;

class NotifyUsers extends BSEntitySaveComplete {
	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		if ( !$this->entity instanceof \BlueSpice\Social\Entity ) {
			return true;
		}

		if ( !$this->entity->getConfig()->get( 'HasNotifications' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	protected function doProcess() {
		$action = SocialNotification::ACTION_EDIT;
		if ( $this->entity->getTitle()->isNewPage() ) {
			$action = SocialNotification::ACTION_CREATE;
		}

		$notifyAll = false;

		$services = $this->getServices();

		if ( $services->hasService( 'BSSocialAutoWatcherFactory' ) ) {
			$autoWatcherFactory = $services->getService( 'BSSocialAutoWatcherFactory' );
			$autoWatcher = $autoWatcherFactory->factory( $this->entity, $this->getContext() );
			$autoWatcher->autoWatch();
		} else {
			// Service not defined
			$notifyAll = true;
		}

		$notificationsManager = MediaWikiServices::getInstance()->getService(
			'BSNotificationManager'
		);

		$notifier = $notificationsManager->getNotifier();

		$notificationClasses = $this->entity->getConfig()->get( 'NotificationObjectClass' );
		if ( !is_array( $notificationClasses ) ) {
			$notificationClasses = [ $notificationClasses ];
		}
		$notificationTypePrefix = $this->entity->getConfig()->get( 'NotificationTypePrefix' );

		foreach ( $notificationClasses as $notificationClass ) {
			$notification = new $notificationClass(
				$notificationTypePrefix,
				$this->entity,
				$this->user,
				$action
			);
			$notification->setNotifyAll( $notifyAll );

			$notifier->notify( $notification );
		}
		return true;
	}
}
