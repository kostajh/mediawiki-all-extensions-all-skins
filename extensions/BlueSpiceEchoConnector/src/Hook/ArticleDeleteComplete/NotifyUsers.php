<?php

namespace BlueSpice\EchoConnector\Hook\ArticleDeleteComplete;

use BlueSpice\EchoConnector\Notification\DeleteNotification;
use BlueSpice\Hook\ArticleDeleteComplete;
use MediaWiki\MediaWikiServices;

class NotifyUsers extends ArticleDeleteComplete {
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
		$notification = new DeleteNotification( $this->user, $this->wikipage->getTitle(), $this->reason );
		$notifier->notify( $notification );

		return true;
	}
}
