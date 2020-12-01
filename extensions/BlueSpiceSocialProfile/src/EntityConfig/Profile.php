<?php

/**
 * SocialEntityProfileConfig class for BSSocial
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

namespace BlueSpice\Social\Profile\EntityConfig;

use BlueSpice\Social\Data\Entity\Schema;
use BlueSpice\Social\EntityConfig\Page;
use BlueSpice\Social\Profile\Field;
use MediaWiki\MediaWikiServices;
use MWException;

/**
 * SocialEntityProfileConfig class for BSSocial extension
 * @package BlueSpiceSocial
 * @subpackage BSSocial
 */
class Profile extends Page {
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
		return "\\BlueSpice\\Social\\Profile\\Entity\\Profile";
	}

	/**
	 *
	 * @return string
	 */
	protected function get_EntityTemplateDefault() {
		return 'BlueSpiceSocialProfile.Entity.Profile.Default';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_EntityTemplatePage() {
		return 'BlueSpiceSocialProfile.Entity.Profile.Page';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_Renderer() {
		return 'socialentityprofile';
	}

	/**
	 *
	 * @return array
	 */
	protected function get_ModuleStyles() {
		return array_merge( parent::get_ModuleStyles(), [
			'ext.bluespice.socialprofile.styles'
		] );
	}

	/**
	 *
	 * @return array
	 */
	protected function get_ModuleScripts() {
		return array_merge( parent::get_ModuleScripts(), [
			'ext.bluespice.social.entity.profile',
		] );
	}

	/**
	 *
	 * @return string[]
	 */
	protected function get_ModuleEditScripts() {
		return array_merge( parent::get_ModuleEditScripts(), [
			'ext.bluespice.social.entity.editor.profile'
		] );
	}

	/**
	 *
	 * @return string
	 */
	protected function get_TypeMessageKey() {
		return 'bs-socialprofile-type';
	}

	/**
	 *
	 * @return array
	 */
	protected function get_VarMessageKeys() {
		$messageKeys = parent::get_VarMessageKeys();
		$fields = array_merge(
			$this->get_ProfileCustomFieldsDefinitions(),
			$this->get_ProfileFieldsDefinitions()
		);
		foreach ( $fields as $name => $definition ) {
			if ( isset( $messageKeys[$name] ) ) {
				throw new MWException( "fieldname $name already in use!" );
			}
			if ( !isset( $definition[Field::KEY_I18N] ) ) {
				continue;
			}
			$messageKeys[$name] = $definition[Field::KEY_I18N];
		}
		return $messageKeys;
	}

	/**
	 *
	 * @return string
	 */
	protected function get_HeaderMessageKey() {
		return 'bs-socialprofile-entityprofile-header';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_HeaderMessageKeyCreateNew() {
		return 'bs-socialprofile-entityprofile-header';
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_isDeleteable() {
		return false;
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_isCreatable() {
		return false;
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
	protected function get_IsTagable() {
		return false;
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_IsGroupable() {
		return false;
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_IsRateable() {
		return false;
	}

	/**
	 *
	 * @return string
	 */
	protected function get_EditothersPermission() {
		return 'social-editothersprofile';
	}

	/**
	 *
	 * @return array
	 * @throws MWException
	 */
	protected function get_AttributeDefinitions() {
		$definitions = parent::get_AttributeDefinitions();
		$fields = array_merge(
			$this->get_ProfileCustomFieldsDefinitions(),
			$this->get_ProfileFieldsDefinitions()
		);
		foreach ( $fields as $name => $definition ) {
			if ( isset( $definitions[$name] ) ) {
				throw new MWException( "fieldname $name already in use!" );
			}
			$definitions[$name] = [
				Schema::FILTERABLE => $definition[Schema::FILTERABLE],
				Schema::SORTABLE => $definition[Schema::SORTABLE],
				Schema::TYPE => $definition[Schema::TYPE],
				Schema::INDEXABLE => $definition[Schema::INDEXABLE],
				Schema::STORABLE => $definition[Schema::STORABLE],
			];
		}
		return $definitions;
	}

	/**
	 *
	 * @return array
	 */
	public function get_ProfileFieldsDefinitions() {
		$factory = MediaWikiServices::getInstance()->getService(
			'BSSocialProfileFieldsFactory'
		);
		return $factory->getFieldDefinitions();
	}

	/**
	 *
	 * @return array
	 */
	public function get_ProfileCustomFieldsDefinitions() {
		$factory = MediaWikiServices::getInstance()->getService(
			'BSSocialProfileCustomFieldsFactory'
		);
		$defs = $factory->getFieldDefinitions();
		foreach ( $defs as &$def ) {
			if ( isset( $def['ldap'] ) ) {
				unset( $def['ldap'] );
			}
		}
		return $defs;
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_EntityListPrivacyHandlerTypeAllowed() {
		return false;
	}
}
