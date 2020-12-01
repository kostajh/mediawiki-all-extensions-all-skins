<?php

namespace BlueSpice\Social\Notifications;

use BlueSpice\Social\Entity\Text as TextEntity;

/**
 * This class is used for "Text" entities.
 */
class SocialTextNotification extends SocialNotification {
	/**
	 * Adds text field to params
	 * @return array
	 */
	public function getParams() {
		$params = parent::getParams();
		$params['entitytext'] = $this->entity->get( TextEntity::ATTR_PARSED_TEXT );

		return $params;
	}
}
