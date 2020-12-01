<?php

namespace BlueSpice\EchoConnector\Hook\TitleMoveComplete;

use BlueSpice\EchoConnector\Notification\TitleMoveNotification;
use BlueSpice\Hook\TitleMoveComplete;
use MediaWiki\MediaWikiServices;

class NotifyUsers extends TitleMoveComplete {
	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		return $this->getServices()->getPermissionManager()->userHasRight(
			$this->user,
			'bot'
		);
	}

	protected function doProcess() {
		$notificationsManager = MediaWikiServices::getInstance()->getService(
			'BSNotificationManager'
		);

		$notifier = $notificationsManager->getNotifier();
		$notification = new TitleMoveNotification(
			$this->user,
			$this->newTitle,
			$this->title,
			$this->reason
		);
		$notifier->notify( $notification );

		return true;
	}
}
