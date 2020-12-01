<?php

namespace BlueSpice\EchoConnector\Hook\PageSaveComplete;

use BlueSpice\EchoConnector\Notification\CreateInNamespaceOrCategoryNotification;
use BlueSpice\EchoConnector\Notification\CreateNotification;
use BlueSpice\EchoConnector\Notification\EditInNamespaceOrCategoryNotification;
use BlueSpice\EchoConnector\Notification\EditNotification;
use BlueSpice\Hook\PageSaveComplete;
use BlueSpice\INotifier;
use MediaWiki\MediaWikiServices;
use Title;

class NotifyUsers extends PageSaveComplete {

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

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$notificationsManager = MediaWikiServices::getInstance()->getService( 'BSNotificationManager' );
		$notifier = $notificationsManager->getNotifier();

		if ( !$notifier ) {
			return true;
		}

		$title = $this->wikiPage->getTitle();
		$this->fireNamespaceCategoryNotifications( $notifier, $title );

		if ( $title->getNamespace() === NS_USER_TALK ) {
			return true;
		}

		if ( $this->flags & EDIT_NEW ) {
			$notification = new CreateNotification( $this->user, $title, $this->summary );
			$notifier->notify( $notification );

			return true;
		}

		$notification = new EditNotification( $this->user, $title, $this->revisionRecord, $this->summary );
		$notifier->notify( $notification );

		return true;
	}

	/**
	 * Send notifications for the namespace and category subscriptions
	 *
	 * @param INotifier $notifier
	 * @param Title $title
	 */
	private function fireNamespaceCategoryNotifications( INotifier $notifier, Title $title ) {
		if ( $this->flags & EDIT_NEW ) {
			$notifier->notify(
				new CreateInNamespaceOrCategoryNotification( $this->user, $title, $this->summary )
			);
		} else {
			$notifier->notify( new EditInNamespaceOrCategoryNotification(
				$this->user, $title,  $this->revisionRecord, $this->summary
			) );
		}
	}

}
