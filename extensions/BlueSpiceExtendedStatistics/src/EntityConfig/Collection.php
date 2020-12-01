<?php

namespace BlueSpice\ExtendedStatistics\EntityConfig;

use BlueSpice\EntityConfig;
use BlueSpice\ExtendedStatistics\Entity\Collection as Entity;

class Collection extends EntityConfig {

	/**
	 *
	 * @return bool
	 */
	protected function get_IsCollection() {
		return true;
	}

	/**
	 *
	 * @return string
	 */
	protected function get_TypeMessageKey() {
		return $this->getType();
	}

	/**
	 *
	 * @return array
	 */
	protected function get_VarMessageKeys() {
		return [
			Entity::ATTR_TYPE => 'bs-extendedstatistics-collection-var-type',
			Entity::ATTR_TIMESTAMP_CREATED => 'bs-extendedstatistics-collection-var-timestampcreated',
		];
	}

	/**
	 *
	 * @return string[]
	 */
	protected function get_Modules() {
		return [
			'ext.bluespice.extendedstatistics.collection',
		];
	}

	/**
	 *
	 * @return string
	 */
	protected function get_EntityClass() {
		return "\\BlueSpice\\ExtendedStatistics\\Entity\\Collection";
	}

	/**
	 *
	 * @return array
	 */
	protected function get_AttributeDefinitions() {
		$attributes = parent::get_AttributeDefinitions();
		return $attributes;
	}

	/**
	 *
	 * @return array
	 */
	final protected function get_DefaultAttributeDefinitions() {
		return parent::get_AttributeDefinitions();
	}

	/**
	 *
	 * @return array
	 */
	protected function addGetterDefaults() {
		return [];
	}

	/**
	 *
	 * @return string
	 */
	protected function get_StoreClass() {
		return "\\BlueSpice\\ExtendedStatistics\\Data\\Entity\\Collection\\Store";
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_PermissionTitleRequired() {
		return false;
	}

	/**
	 *
	 * @return string
	 */
	protected function get_ReadPermission() {
		return 'wikiadmin';
	}

}
