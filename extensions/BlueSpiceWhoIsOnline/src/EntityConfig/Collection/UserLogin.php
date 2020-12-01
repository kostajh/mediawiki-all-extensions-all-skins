<?php

namespace BlueSpice\WhoIsOnline\EntityConfig\Collection;

use BlueSpice\Data\FieldType;
use BlueSpice\EntityConfig;
use BlueSpice\ExtendedStatistics\Data\Entity\Collection\Schema;
use BlueSpice\ExtendedStatistics\EntityConfig\Collection;
use BlueSpice\WhoIsOnline\Entity\Collection\UserLogin as Entity;
use Config;
use MediaWiki\MediaWikiServices;

class UserLogin extends EntityConfig {

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
		return 'bs-whoisonline-collection-type-userlogin';
	}

	/**
	 *
	 * @return array
	 */
	protected function get_VarMessageKeys() {
		return array_merge( $this->getConfig()->get( 'VarMessageKeys' ), [
			Entity::ATTR_LOGIN => 'bs-whoisonline-collection-var-login',
		] );
	}

	/**
	 *
	 * @return string[]
	 */
	protected function get_Modules() {
		return array_merge( $this->getConfig()->get( 'Modules' ), [
			'ext.bluespice.whoisonline.collection.userlogin',
		] );
	}

	/**
	 *
	 * @return string
	 */
	protected function get_EntityClass() {
		return "\\BlueSpice\\WhoIsOnline\\Entity\\Collection\\UserLogin";
	}

	/**
	 *
	 * @return array
	 */
	protected function get_AttributeDefinitions() {
		$attributes = $this->getConfig()->get( 'AttributeDefinitions' );
		$attributes[ Entity::ATTR_LOGIN ] = [
			Schema::FILTERABLE => true,
			Schema::SORTABLE => true,
			Schema::TYPE => FieldType::INT,
			Schema::INDEXABLE => true,
			Schema::STORABLE => true,
			Schema::PRIMARY => true
		];
		return $attributes;
	}

}
