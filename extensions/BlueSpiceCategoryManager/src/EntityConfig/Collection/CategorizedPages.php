<?php

namespace BlueSpice\CategoryManager\EntityConfig\Collection;

use BlueSpice\CategoryManager\Entity\Collection\CategorizedPages as Entity;
use BlueSpice\Data\FieldType;
use BlueSpice\EntityConfig;
use BlueSpice\ExtendedStatistics\Data\Entity\Collection\Schema;
use BlueSpice\ExtendedStatistics\EntityConfig\Collection;
use Config;
use MediaWiki\MediaWikiServices;

class CategorizedPages extends EntityConfig {

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
	 * @return string
	 */
	protected function get_StoreClass() {
		return $this->getConfig()->get( 'StoreClass' );
	}

	/**
	 *
	 * @return string
	 */
	protected function get_TypeMessageKey() {
		return 'bs-categorymanager-collection-type-categorizedpages';
	}

	/**
	 *
	 * @return array
	 */
	protected function get_VarMessageKeys() {
		return array_merge( $this->getConfig()->get( 'VarMessageKeys' ), [
			Entity::ATTR_NAMESPACE_NAME => 'bs-categorymanager-collection-var-namespacename',
			Entity::ATTR_CATEGORIZED_PAGES => 'bs-categorymanager-collection-var-categorizedpages',
			Entity::ATTR_UNCATEGORIZED_PAGES => 'bs-categorymanager-collection-var-uncategorizedpages',
		] );
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
	 * @return string[]
	 */
	protected function get_Modules() {
		return array_merge( $this->getConfig()->get( 'Modules' ), [
			'ext.bluespice.categorymanager.collection.categorizedpages',
		] );
	}

	/**
	 *
	 * @return string
	 */
	protected function get_EntityClass() {
		return "\\BlueSpice\\CategoryManager\\Entity\\Collection\\CategorizedPages";
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
			Entity::ATTR_CATEGORIZED_PAGES => [
				Schema::FILTERABLE => true,
				Schema::SORTABLE => true,
				Schema::TYPE => FieldType::INT,
				Schema::INDEXABLE => true,
				Schema::STORABLE => true,
				Schema::PRIMARY => true,
			],
			Entity::ATTR_CATEGORIZED_PAGES_AGGREGATED => [
				Schema::FILTERABLE => true,
				Schema::SORTABLE => true,
				Schema::TYPE => FieldType::INT,
				Schema::INDEXABLE => true,
				Schema::STORABLE => true,
			],
			Entity::ATTR_UNCATEGORIZED_PAGES => [
				Schema::FILTERABLE => true,
				Schema::SORTABLE => true,
				Schema::TYPE => FieldType::INT,
				Schema::INDEXABLE => true,
				Schema::STORABLE => true,
				Schema::PRIMARY => true,
			],
			Entity::ATTR_UNCATEGORIZED_PAGES_AGGREGATED => [
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
