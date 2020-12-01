<?php

/**
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
use BlueSpice\Social\Entity\Text as Entity;
use BlueSpice\Social\EntityConfig;
use BlueSpice\Social\ExtendedSearch\Formatter\Internal\TextFormatter;

/**
 *  class for BlueSpiceSocial extension
 * @package BlueSpiceSocial
 * @subpackage BlueSpiceSocial
 */
abstract class Text extends EntityConfig {

	/**
	 *
	 * @return string
	 */
	protected function get_EntityClass() {
		return "\\BlueSpice\\Social\\Entity\\Text";
	}

	/**
	 *
	 * @return string
	 */
	protected function get_ParserClass() {
		return '\\BlueSpice\\Social\\Parser\\WikiText';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_Renderer() {
		return 'socialentitytext';
	}

	/**
	 *
	 * @return array
	 */
	protected function get_ModuleScripts() {
		return array_merge(
			parent::get_ModuleScripts(), [
				'ext.bluespice.social.entity.text',
			]
		);
	}

	/**
	 *
	 * @return string[]
	 */
	protected function get_ModuleEditScripts() {
		return array_merge( parent::get_ModuleEditScripts(), [
			'ext.bluespice.social.entity.editor.text'
		] );
	}

	/**
	 *
	 * @return string
	 */
	protected function get_HeaderMessageKey() {
		return 'bs-social-entitytext-header';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_HeaderMessageKeyCreateNew() {
		return 'bs-social-entitytext-header-create';
	}

	/**
	 *
	 * @return array
	 */
	protected function get_VarMessageKeys() {
		return array_merge(
			parent::get_VarMessageKeys(),
			[ Entity::ATTR_TEXT => 'bs-social-var-text' ]
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
				Entity::ATTR_TEXT => [
					Schema::FILTERABLE => true,
					Schema::SORTABLE => false,
					Schema::TYPE => FieldType::TEXT,
					Schema::INDEXABLE => true,
					Schema::STORABLE => true,
				],
				Entity::ATTR_PARSED_TEXT => [
					Schema::FILTERABLE => true,
					Schema::SORTABLE => false,
					Schema::TYPE => FieldType::TEXT,
					Schema::INDEXABLE => true,
					Schema::STORABLE => false,
				],
				Entity::ATTR_ATTACHMENTS => [
					Schema::FILTERABLE => false,
					Schema::SORTABLE => false,
					Schema::TYPE => FieldType::MIXED,
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
	protected function get_ExtendedSearchResultFormatter() {
		return TextFormatter::class;
	}

	/**
	 *
	 * @return string
	 */
	protected function get_NotificationObjectClass() {
		return \BlueSpice\Social\Notifications\SocialTextNotification::class;
	}

	/**
	 *
	 * @return string
	 */
	protected function get_NotificationTypePrefix() {
		return 'bs-social-entity-text';
	}

	/**
	 *
	 * @return array
	 */
	protected function get_AvailableAttachments() {
		return [
			'images',
			'links',
			'interwikilinks'
		];
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_CanHaveAttachments() {
		return true;
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_ForceRelatedTitleTag() {
		return false;
	}
}
