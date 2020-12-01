<?php

/**
 *  class for BlueSpiceSocial
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
namespace BlueSpice\Social\EntityConfig;

use BlueSpice\Data\FieldType;
use BlueSpice\Social\Data\Entity\Schema;
use BlueSpice\Social\Entity\Action as Entity;
use BlueSpice\Social\Renderer\Entity as Renderer;

/**
 *  class for BlueSpiceSocial extension
 * @package BlueSpiceSocial
 * @subpackage BlueSpiceSocial
 */
abstract class Action extends Text {

	/**
	 *
	 * @return bool
	 */
	protected function get_IsEditable() {
		return false;
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_IsCreatable() {
		return false;
	}

	/**
	 *
	 * @return array
	 */
	protected function get_ModuleScripts() {
		return array_merge( parent::get_ModuleScripts(), [
			'ext.bluespice.social.entity.action',
		] );
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
	protected function get_AttributeDefinitions() {
		return array_merge(
			parent::get_AttributeDefinitions(),
			[
				Entity::ATTR_ACTION => [
					Schema::FILTERABLE => true,
					Schema::SORTABLE => true,
					Schema::TYPE => FieldType::STRING,
					Schema::INDEXABLE => true,
					Schema::STORABLE => true,
				],
				Entity::ATTR_SUMMARY => [
					Schema::FILTERABLE => true,
					Schema::SORTABLE => true,
					Schema::TYPE => FieldType::STRING,
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
	protected function get_ExtendedSearchListable() {
		return false;
	}

	/**
	 *
	 * @return string
	 */
	protected function get_EntityListOutputType() {
		return Renderer::RENDER_TYPE_LIST;
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
	protected function get_EntityListDiscussionPageTypeAllowed() {
		return true;
	}

	/**
	 *
	 * @return string
	 */
	protected function get_EntityListSpecialActivitiesOutputType() {
		return Renderer::RENDER_TYPE_LIST;
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_EntityListSpecialActivitiesTypeAllowed() {
		return true;
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_EntityListSpecialActivitiesTypeSelected() {
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
	protected function get_HasNotifications() {
		return false;
	}
}
