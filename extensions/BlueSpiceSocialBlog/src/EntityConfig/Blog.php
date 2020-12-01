<?php

/**
 * Blog class for BSSocial
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
namespace BlueSpice\Social\Blog\EntityConfig;

use BlueSpice\Data\FieldType;
use BlueSpice\Social\Blog\Entity\Blog as Entity;
use BlueSpice\Social\Data\Entity\Schema;
use BlueSpice\Social\EntityConfig\Text;

/**
 * Blog class for BSSocial extension
 * @package BlueSpiceSocial
 * @subpackage BSSocial
 */
class Blog extends Text {
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
		return "\\BlueSpice\\Social\\Blog\\Entity\\Blog";
	}

	/**
	 *
	 * @return string
	 */
	protected function get_ParserClass() {
		return 'Parser';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_EntityTemplateDefault() {
		return "BlueSpiceSocialBlog.Entity.Blog.Default";
	}

	/**
	 *
	 * @return string
	 */
	protected function get_EntityTemplatePage() {
		return "BlueSpiceSocialBlog.Entity.Blog.Page";
	}

	/**
	 *
	 * @return string
	 */
	protected function get_Renderer() {
		return 'socialentityblog';
	}

	/**
	 *
	 * @return array
	 */
	protected function get_ModuleScripts() {
		return array_merge( parent::get_ModuleScripts(), [
			'ext.bluespice.social.entity.text',
			'ext.bluespice.social.entity.blog',
		] );
	}

	/**
	 *
	 * @return string[]
	 */
	protected function get_ModuleEditScripts() {
		return array_merge( parent::get_ModuleEditScripts(), [
			'ext.bluespice.social.entity.editor.blog'
		] );
	}

	/**
	 *
	 * @return array
	 */
	protected function get_ModuleStyles() {
		return array_merge( parent::get_ModuleStyles(), [
			'ext.bluespice.socialblog.styles'
		] );
	}

	/**
	 *
	 * @return string
	 */
	protected function get_HeaderMessageKeyCreateNew() {
		return 'bs-socialblog-entityblog-header-create';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_HeaderMessageKey() {
		return 'bs-socialblog-entityblog-header';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_TypeMessageKey() {
		return 'bs-socialblog-type';
	}

	/**
	 *
	 * @return array
	 */
	protected function get_VarMessageKeys() {
		return array_merge(
			parent::get_VarMessageKeys(),
			[
				Entity::ATTR_TEXT => 'bs-social-var-text',
				Entity::ATTR_BLOG_TITLE => 'bs-socialblog-var-blogtitle',
				Entity::ATTR_TEASER_TEXT => 'bs-social-var-teasertext',
			]
		);
	}

	/**
	 *
	 * @return array
	 */
	protected function get_AttributeDefinitions() {
		return array_merge(
			parent::get_AttributeDefinitions(),
			[
				Entity::ATTR_BLOG_TITLE => [
					Schema::FILTERABLE => true,
					Schema::SORTABLE => true,
					Schema::TYPE => FieldType::STRING,
					Schema::INDEXABLE => true,
					Schema::STORABLE => true,
				],
				Entity::ATTR_TEASER_TEXT => [
					Schema::FILTERABLE => true,
					Schema::SORTABLE => false,
					Schema::TYPE => FieldType::STRING,
					Schema::INDEXABLE => true,
					Schema::STORABLE => true,
				],
			]
		);
	}

	/**
	 *
	 * @return string
	 */
	protected function get_NotificationObjectClass() {
		return \BlueSpice\Social\Blog\Notification\SocialBlogNotification::class;
	}

	/**
	 *
	 * @return string
	 */
	protected function get_NotificationTypePrefix() {
		return 'bs-social-blog';
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_HasNotifications() {
		return true;
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_EntityListSpecialBlogTypeAllowed() {
		return true;
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_ExtendedSearchListable() {
		return true;
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_EntityListSpecialTimelineTypeSelected() {
		return true;
	}

	/**
	 *
	 * @return string
	 */
	protected function get_EntityListPreloadTitle() {
		return $this->get( 'SocialBlogPreloadTitle' );
	}

}
