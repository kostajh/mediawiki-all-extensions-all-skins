<?php

namespace BlueSpice\ExtendedStatistics\EntityConfig;

class Snapshot extends \BlueSpice\EntityConfig {

	/**
	 *
	 * @return string
	 */
	protected function get_EntityClass() {
		return "\\BlueSpice\\ExtendedStatistics\\Entity\\Snapshot";
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
	protected function addGetterDefaults() {
		return [];
	}

	/**
	 *
	 * @return string
	 */
	protected function get_StoreClass() {
		return "\\BlueSpice\\ExtendedStatistics\\Data\\Snapshot\\Store";
	}

}
