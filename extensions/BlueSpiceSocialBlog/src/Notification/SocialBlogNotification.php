<?php

namespace BlueSpice\Social\Blog\Notification;

use BlueSpice\Social\Blog\Entity\Blog as BlogEntity;
use BlueSpice\Social\Notifications\SocialNotification;
use Message;

class SocialBlogNotification extends SocialNotification {
	/**
	 *
	 * @return array
	 */
	public function getParams() {
		$params = parent::getParams();

		$params['primary-link-label'] = Message::newFromKey(
			'bs-social-notification-blog-primary-link-label'
		)->plain();

		$params['blogname'] = $this->entity->get( BlogEntity::ATTR_BLOG_TITLE );
		$params['blogteaser'] = $this->entity->get( BlogEntity::ATTR_TEASER_TEXT );

		return $params;
	}
}
