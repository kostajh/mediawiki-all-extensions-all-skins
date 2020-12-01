<?php

/**
 * SocialEntityCommentConfig class for BSSocial
 *
 * add desc
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit https://bluespice.com
 *
 * @author     Patric Wirth
 * @package    BlueSpiceSocial
 * @subpackage BSSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */
namespace BlueSpice\Social\Comments\EntityConfig;

use BlueSpice\Social\EntityConfig\Text;

/**
 * Comment class for BSSocial extension
 * @package BlueSpiceSocial
 * @subpackage BSSocial
 */
class Comment extends Text {
	/**
	 *
	 * @return array
	 */
	public function addGetterDefaults() {
		return [];
	}

	/**
	 *
	 * @return string
	 */
	protected function get_EntityClass() {
		return "\\BlueSpice\\Social\\Comments\\Entity\\Comment";
	}

	/**
	 *
	 * @return array
	 */
	protected function get_ModuleStyles() {
		return array_merge( parent::get_ModuleStyles(), [
			'ext.bluespice.social.comments.styles',
		] );
	}

	/**
	 *
	 * @return array
	 */
	protected function get_ModuleScripts() {
		return array_merge( parent::get_ModuleScripts(), [
			'ext.bluespice.social.entity.comment',
		] );
	}

	/**
	 *
	 * @return string
	 */
	protected function get_HeaderMessageKeyCreateNew() {
		return 'bs-socialcomments-header-create';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_HeaderMessageKey() {
		return 'bs-socialcomments-header';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_TypeMessageKey() {
		return 'bs-socialcomments-type';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_EntityTemplateShort() {
		return 'BlueSpiceSocialComments.Entity.Comment.Short';
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_IsWatchable() {
		return false;
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_IsSpawnable() {
		return false;
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_IsTagable() {
		return false;
	}

	/**
	 *
	 * @return array
	 */
	protected function get_NotificationObjectClass() {
		return [
			\BlueSpice\Social\Comments\Notification\SocialCommentsNotification::class,
			\BlueSpice\Social\Comments\Notification\SocialCommentsOwnerNotification::class
		];
	}

	/**
	 *
	 * @return string
	 */
	protected function get_NotificationTypePrefix() {
		return 'bs-social-comment';
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_EntityListTypeChildrenAllowed() {
		return true;
	}

	/**
	 *
	 * @return string
	 */
	protected function get_EntityListChildrenOutputType() {
		return 'Short';
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_CanHaveChildren() {
		return false;
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_EntityListSpecialTimelineTypeSelected() {
		return true;
	}
}
