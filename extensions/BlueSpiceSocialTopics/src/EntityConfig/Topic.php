<?php

/**
 * Topic class for BSSocial
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
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    BlueSpiceSocial
 * @subpackage BlueSpiceSocialTopics
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */
namespace BlueSpice\Social\Topics\EntityConfig;

use BlueSpice\Data\FieldType;
use BlueSpice\Social\Data\Entity\Schema;
use BlueSpice\Social\EntityConfig\Text;
use BlueSpice\Social\Topics\Entity\Topic as Entity;

/**
 * Topic class for BSSocial extension
 * @package BlueSpiceTopics
 * @subpackage BSSocial
 */
class Topic extends Text {
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
	public function get_EntityClass() {
		return "\\BlueSpice\\Social\\Topics\\Entity\\Topic";
	}

	/**
	 *
	 * @return string
	 */
	protected function get_Renderer() {
		return 'social-topics-entity-topic';
	}

	/**
	 *
	 * @return array
	 */
	protected function get_ModuleScripts() {
		return array_merge(
			parent::get_ModuleScripts(),
			[ 'ext.bluespice.social.entity.topic' ]
		);
	}

	/**
	 *
	 * @return string[]
	 */
	protected function get_ModuleEditScripts() {
		return array_merge( parent::get_ModuleEditScripts(), [
			'ext.bluespice.social.entity.editor.topic'
		] );
	}

	/**
	 *
	 * @return string
	 */
	protected function get_TypeMessageKey() {
		return 'bs-socialdiscussion-topictype';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_HeaderMessageKeyCreateNew() {
		return 'bs-social-entitytopic-header-create';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_HeaderMessageKey() {
		return 'bs-social-entitytopic-header';
	}

	/**
	 *
	 * @return array
	 */
	protected function get_VarMessageKeys() {
		return array_merge(
			parent::get_VarMessageKeys(),
			[
				Entity::ATTR_TOPIC_TITLE => 'bs-socialtopics-var-topictitle',
				Entity::ATTR_DISCUSSION_TITLE_ID => 'bs-socialtopics-var-discussiontitleid'
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
				Entity::ATTR_TOPIC_TITLE => [
					Schema::FILTERABLE => true,
					Schema::SORTABLE => true,
					Schema::TYPE => FieldType::STRING,
					Schema::INDEXABLE => true,
					Schema::STORABLE => true,
				],
				Entity::ATTR_DISCUSSION_TITLE_ID => [
					Schema::FILTERABLE => true,
					Schema::SORTABLE => true,
					Schema::TYPE => FieldType::INT,
					Schema::INDEXABLE => true,
					Schema::STORABLE => true,
				],
			]
		);
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_EntityListDiscussionPageTypeAllowed() {
		return true;
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_EntityListAfterContentTypeAllowed() {
		return true;
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_IsResolvable() {
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
	protected function get_ForceRelatedTitleTag() {
		return true;
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_EntityListInitiallyHiddenChildrenDefault() {
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
