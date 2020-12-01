<?php

namespace BlueSpice\Social\Comments\Notification;

use User;

class SocialCommentsOwnerNotification extends SocialCommentsNotification {
	/**
	 *
	 * @return string
	 */
	public function getKey() {
		return $this->key . '-owner-' . $this->action;
	}

	/**
	 * Send this notification only to parent user
	 * @return array
	 */
	protected function getUsersWatching() {
		if ( $this->parentEntityOwner instanceof User ) {
			return [ $this->parentEntityOwner->getId() => $this->parentEntityOwner ];
		}
	}
}
