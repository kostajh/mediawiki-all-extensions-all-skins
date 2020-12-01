<?php
/**
 * EntityConfig class for BlueSpiceSocial
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
 * @subpackage BlueSpiceSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */
namespace BlueSpice\Social;

use BlueSpice\Data\FieldType;
use BlueSpice\Social\Data\Entity\Schema;
use BlueSpice\Social\ExtendedSearch\Formatter\Internal\EntityFormatter;

/**
 * EntityConfig class for BlueSpiceSocial extension
 * @package BlueSpiceSocial
 * @subpackage BlueSpiceSocial
 */
abstract class EntityConfig extends \BlueSpice\EntityConfig\Content {

	/**
	 *
	 * @return array
	 */
	public function addGetterDefaults() {
		return [
			'ModuleStyles' => [],
			'ModuleScripts' => [],
		];
	}

	/**
	 *
	 * @return string
	 */
	protected function get_OutputClass() {
		return '';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_Renderer() {
		return "socialentity";
	}

	/**
	 *
	 * @return string
	 */
	protected function get_StoreClass() {
		return "\\BlueSpice\\Social\\Data\\Entity\\Store";
	}

	/**
	 *
	 * @return string
	 */
	protected function get_ChildListContextClass() {
		return "\\BlueSpice\\Social\\EntityListContext\\Children";
	}

	/**
	 *
	 * @return string
	 */
	protected function get_TypeMessageKey() {
		return '';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_HeaderMessageKey() {
		return '';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_HeaderMessageKeyCreateNew() {
		return '';
	}

	/**
	 *
	 * @return array
	 */
	protected function get_VarMessageKeys() {
		return [
			Entity::ATTR_ID => 'bs-social-var-id',
			Entity::ATTR_OWNER_ID => 'bs-social-var-ownerid',
			Entity::ATTR_TYPE => 'bs-social-var-type',
			Entity::ATTR_ARCHIVED => 'bs-social-var-archived',
			Entity::ATTR_TIMESTAMP_CREATED => 'bs-social-var-timestampcreated',
			Entity::ATTR_TIMESTAMP_TOUCHED => 'bs-social-var-timestamptouched',
		];
	}

	/**
	 *
	 * @return string
	 */
	protected function get_DeleteConfirmMessageKey() {
		return 'bs-social-entityaction-delete-confirmtext';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_UnDeleteConfirmMessageKey() {
		return 'bs-social-entityaction-undelete-confirmtext';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_ContentClass() {
		return '\\BlueSpice\\Social\\Content\\Entity';
	}

	/**
	 *
	 * @return array
	 */
	protected function get_AttributeDefinitions() {
		return array_merge(
			parent::get_AttributeDefinitions(),
			[
				Entity::ATTR_PARENT_ID => [
					Schema::FILTERABLE => true,
					Schema::SORTABLE => true,
					Schema::TYPE => FieldType::INT,
					Schema::INDEXABLE => true,
					Schema::STORABLE => true,
				],
				Entity::ATTR_HEADER => [
					Schema::FILTERABLE => true,
					Schema::SORTABLE => true,
					Schema::TYPE => FieldType::TEXT,
					Schema::INDEXABLE => true,
					Schema::STORABLE => false,
				],
				Entity::ATTR_RELATED_TITLE => [
					Schema::FILTERABLE => true,
					Schema::SORTABLE => true,
					Schema::TYPE => FieldType::STRING,
					Schema::INDEXABLE => true,
					Schema::STORABLE => false,
				],
				Entity::ATTR_OWNER_NAME => [
					Schema::FILTERABLE => true,
					Schema::SORTABLE => true,
					Schema::TYPE => FieldType::STRING,
					Schema::INDEXABLE => true,
					Schema::STORABLE => false,
				],
				Entity::ATTR_OWNER_REAL_NAME => [
					Schema::FILTERABLE => true,
					Schema::SORTABLE => true,
					Schema::TYPE => FieldType::STRING,
					Schema::INDEXABLE => true,
					Schema::STORABLE => false,
				],
				Entity::ATTR_ACTIONS => [
					Schema::FILTERABLE => false,
					Schema::SORTABLE => false,
					Schema::TYPE => FieldType::LISTVALUE,
					Schema::INDEXABLE => false,
					Schema::STORABLE => false,
				],
				Entity::ATTR_PRELOAD => [
					Schema::FILTERABLE => false,
					Schema::SORTABLE => false,
					Schema::TYPE => FieldType::STRING,
					Schema::INDEXABLE => false,
					Schema::STORABLE => false,
				],
			]
		);
	}

	/**
	 *
	 * @return string
	 */
	protected function get_EntityTemplateDefault() {
		return 'BlueSpiceSocial.Entity.Default';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_EntityTemplatePage() {
		return 'BlueSpiceSocial.Entity.Page';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_EntityTemplateShort() {
		return 'BlueSpiceSocial.Entity.Short';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_EntityTemplateList() {
		return 'BlueSpiceSocial.Entity.List';
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_IsEditable() {
		return true;
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_IsCreatable() {
		return true;
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_IsDeleteable() {
		return true;
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_IsSpawnable() {
		return $this->get_IsCreatable();
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_CanHaveChildren() {
		return true;
	}

	/**
	 *
	 * @return string[]
	 */
	protected function get_ModuleScripts() {
		return [
			'ext.bluespice.social',
			'ext.bluespice.social.entity',
		];
	}

	/**
	 *
	 * @return string[]
	 */
	protected function get_ModuleEditScripts() {
		return [
			'ext.bluespice.social.entity.editor',
		];
	}

	/**
	 *
	 * @return string[]
	 */
	protected function get_ModuleStyles() {
		return [
			'ext.bluespice.social.styles',
		];
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_PermissionTitleRequired() {
		return true;
	}

	/**
	 *
	 * @return string
	 */
	protected function get_ReadPermission() {
		return 'read';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_CreatePermission() {
		return 'edit';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_EditPermission() {
		return 'edit';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_DeletePermission() {
		return 'edit';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_DeleteothersPermission() {
		return 'social-deleteothers';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_EditothersPermission() {
		return 'social-editothers';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_SourcePermission() {
		return 'social-source';
	}

	/**
	 *
	 * @return array
	 */
	protected function get_AvailableAttachments() {
		return [];
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_CanHaveAttachments() {
		return false;
	}

	/**
	 *
	 * @return string
	 */
	protected function get_ExtendedSearchResultFormatter() {
		return EntityFormatter::class;
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_ExtendedSearchListable() {
		return false;
	}

	/**
	 *
	 * @return string
	 */
	protected function get_NotificationObjectClass() {
		return \BlueSpice\Social\Notifications\SocialNotification::class;
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
	 * @return string
	 */
	protected function get_NotificationTypePrefix() {
		return 'bs-social-entity';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_EntityListOutputType() {
		return 'Default';
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_EntityListTypeAllowed() {
		return true;
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_EntityListTypeSelected() {
		return true;
	}

	/**
	 *
	 * @return string
	 */
	protected function get_EntityListChildrenOutputType() {
		return 'Default';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_EntityListPreloadTitle() {
		return '';
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_EntityListInitiallyHiddenChildrenDefault() {
		return true;
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_EntityListInitiallyHiddenChildrenShort() {
		return true;
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_EntityListInitiallyHiddenChildrenList() {
		return true;
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_EntityListInitiallyHiddenChildrenPage() {
		return false;
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_EntityListTypeChildrenAllowed() {
		return false;
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_EntityListSpecialTimelineTypeSelected() {
		return false;
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_EntityListPrivacyHandlerTypeAllowed() {
		return true;
	}
}
