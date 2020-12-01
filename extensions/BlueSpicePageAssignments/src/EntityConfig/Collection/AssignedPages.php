<?php

namespace BlueSpice\PageAssignments\EntityConfig\Collection;

use BlueSpice\Data\FieldType;
use BlueSpice\EntityConfig;
use BlueSpice\ExtendedStatistics\Data\Entity\Collection\Schema;
use BlueSpice\ExtendedStatistics\EntityConfig\Collection;
use BlueSpice\PageAssignments\Entity\Collection\AssignedPages as Entity;
use Config;
use MediaWiki\MediaWikiServices;

class AssignedPages extends EntityConfig {

	/**
	 *
	 * @param Config $config
	 * @param string $key
	 * @param MediaWikiServices $services
	 * @return EntityConfig
	 */
	public static function factory( $config, $key, $services ) {
		$extension = $services->getService( 'BSExtensionFactory' )->getExtension(
			'BlueSpiceExtendedStatistics'
		);
		if ( !$extension ) {
			return null;
		}
		return new static( new Collection( $config ), $key );
	}

	/**
	 *
	 * @return array
	 */
	protected function get_PrimaryAttributeDefinitions() {
		return array_filter( $this->get_AttributeDefinitions(), function ( $e ) {
			return isset( $e[Schema::PRIMARY] ) && $e[Schema::PRIMARY] === true;
		} );
	}

	/**
	 *
	 * @return string
	 */
	protected function get_TypeMessageKey() {
		return 'bs-pageassignments-collection-type-assignedpages';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_StoreClass() {
		return $this->getConfig()->get( 'StoreClass' );
	}

	/**
	 *
	 * @return array
	 */
	protected function get_VarMessageKeys() {
		return array_merge( $this->getConfig()->get( 'VarMessageKeys' ), [
			Entity::ATTR_NAMESPACE_NAME => 'bs-pageassignments-collection-var-namespacename',
			Entity::ATTR_ASSIGNED_PAGES => 'bs-pageassignments-collection-var-assignedpages',
			Entity::ATTR_UNASSIGNED_PAGES => 'bs-pageassignments-collection-var-unassignedpages',
		] );
	}

	/**
	 *
	 * @return string[]
	 */
	protected function get_Modules() {
		return array_merge( $this->getConfig()->get( 'Modules' ), [
			'ext.bluespice.pageassignments.collection.assignedpages',
		] );
	}

	/**
	 *
	 * @return string
	 */
	protected function get_EntityClass() {
		return "\\BlueSpice\\PageAssignments\\Entity\\Collection\\AssignedPages";
	}

	/**
	 *
	 * @return array
	 */
	protected function get_AttributeDefinitions() {
		$attributes = array_merge( $this->getConfig()->get( 'AttributeDefinitions' ), [
			Entity::ATTR_NAMESPACE_NAME => [
				Schema::FILTERABLE => true,
				Schema::SORTABLE => true,
				Schema::TYPE => FieldType::STRING,
				Schema::INDEXABLE => true,
				Schema::STORABLE => true,
			],
			Entity::ATTR_ASSIGNED_PAGES => [
				Schema::FILTERABLE => true,
				Schema::SORTABLE => true,
				Schema::TYPE => FieldType::INT,
				Schema::INDEXABLE => true,
				Schema::STORABLE => true,
				Schema::PRIMARY => true,
			],
			Entity::ATTR_ASSIGNED_PAGES_AGGREGATED => [
				Schema::FILTERABLE => true,
				Schema::SORTABLE => true,
				Schema::TYPE => FieldType::INT,
				Schema::INDEXABLE => true,
				Schema::STORABLE => true,
			],
			Entity::ATTR_UNASSIGNED_PAGES => [
				Schema::FILTERABLE => true,
				Schema::SORTABLE => true,
				Schema::TYPE => FieldType::INT,
				Schema::INDEXABLE => true,
				Schema::STORABLE => true,
				Schema::PRIMARY => true,
			],
			Entity::ATTR_UNASSIGNED_PAGES_AGGREGATED => [
				Schema::FILTERABLE => true,
				Schema::SORTABLE => true,
				Schema::TYPE => FieldType::INT,
				Schema::INDEXABLE => true,
				Schema::STORABLE => true,
			],
		] );
		return $attributes;
	}

}
