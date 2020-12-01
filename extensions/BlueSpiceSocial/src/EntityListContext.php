<?php

namespace BlueSpice\Social;

use BlueSpice\Data\FieldType;
use BlueSpice\Data\Filter\Date;
use BlueSpice\Data\Filter\ListValue;
use BlueSpice\Data\Sort;
use BlueSpice\ExtensionAttributeBasedRegistry;
use BlueSpice\Social\Data\Entity\Schema;
use Config;
use IContextSource;
use MediaWiki\MediaWikiServices;
use User;

class EntityListContext extends \BlueSpice\Context implements IEntityListContext {
	const CONFIG_NAME_OUTPUT_TYPE = 'EntityListOutputType';
	const CONFIG_NAME_TYPE_ALLOWED = 'EntityListTypeAllowed';
	const CONFIG_NAME_TYPE_SELECTED = 'EntityListTypeSelected';
	const CONFIG_NAME_PRELOAD_TITLE = 'EntityListPreloadTitle';

	/**
	 *
	 * @var EntityConfig[]
	 */
	protected $entityConfigs = null;

	/**
	 *
	 * @var User
	 */
	protected $user = null;

	/**
	 *
	 * @var Entity
	 */
	protected $entity = null;

	/**
	 *
	 * @return EntityConfig[]
	 */
	protected function getEntityConfigs() {
		if ( $this->entityConfigs ) {
			return $this->entityConfigs;
		}
		$this->entityConfigs = [];
		$factory = $this->getEntityConfigFactory();
		$registry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceFoundationEntityRegistry'
		);
		foreach ( $registry->getAllKeys() as $type ) {
			$config = $factory->newFromType( $type );
			if ( !$config ) {
				continue;
			}
			if ( !$config instanceof EntityConfig ) {
				continue;
			}
			$this->entityConfigs[$type] = $config;
		}
		return $this->entityConfigs;
	}

	/**
	 *
	 * @param IContextSource $context
	 * @param Config $config
	 * @param User|null $user
	 * @param Entity|null $entity
	 */
	public function __construct( IContextSource $context, Config $config,
		User $user = null, Entity $entity = null ) {
		$this->context = $context;
		$this->config = $config;
		$this->user = $user;
		$this->entity = $entity;
	}

	/**
	 *
	 * @return Schema
	 */
	public function getSchema() {
		return new Schema();
	}

	/**
	 *
	 * @return \BlueSpice\EntityConfigFactory
	 */
	protected function getEntityConfigFactory() {
		return MediaWikiServices::getInstance()->getService( 'BSEntityConfigFactory' );
	}

	/**
	 *
	 * @return int
	 */
	public function getLimit() {
		return 20;
	}

	/**
	 *
	 * @return int
	 */
	public function getStart() {
		return 0;
	}

	/**
	 *
	 * @return array
	 */
	public function getSort() {
		return [ (object)[
			'property' => $this->getSortProperty(),
			'direction' => $this->getSortDirection()
		] ];
	}

	/**
	 *
	 * @return string
	 */
	protected function getSortProperty() {
		return Entity::ATTR_TIMESTAMP_TOUCHED;
	}

	/**
	 *
	 * @return string
	 */
	protected function getSortDirection() {
		return Sort::DESCENDING;
	}

	/**
	 *
	 * @return array
	 */
	public function getAllowedTypes() {
		$entityTypes = [];
		foreach ( $this->getEntityConfigs() as $type => $config ) {
			if ( $config->get( static::CONFIG_NAME_TYPE_ALLOWED ) ) {
				$entityTypes[] = $type;
			}
		}
		return $entityTypes;
	}

	/**
	 *
	 * @return \stdClass[]
	 */
	protected function getTypeFilter() {
		$allowedTypes = $this->getAllowedTypes();
		$allTypes = array_keys( $this->getEntityConfigs() );
		$selectedTypes = $this->getSelectedTypes();
		if ( empty( $selectedTypes ) ) {
			$selectedTypes = $allowedTypes;
		}

		$types = array_intersect( $allTypes, $allowedTypes, $selectedTypes );
		return (object)[
			ListValue::KEY_PROPERTY => Entity::ATTR_TYPE,
			ListValue::KEY_VALUE => array_values( $types ),
			ListValue::KEY_COMPARISON => ListValue::COMPARISON_CONTAINS,
			ListValue::KEY_TYPE => FieldType::LISTVALUE
		];
	}

	/**
	 *
	 * @return \stdClass[]
	 */
	protected function getTimestampCreatedFilter() {
		return (object)[
			ListValue::KEY_PROPERTY => Entity::ATTR_TIMESTAMP_CREATED,
			ListValue::KEY_VALUE => wfTimestampNow(),
			ListValue::KEY_COMPARISON => Date::COMPARISON_LOWER_THAN,
			ListValue::KEY_TYPE => FieldType::DATE
		];
	}

	/**
	 *
	 * @return array
	 */
	protected function getSelectedTypes() {
		$entityTypes = [];
		foreach ( $this->getEntityConfigs() as $type => $config ) {
			if ( $config->get( static::CONFIG_NAME_TYPE_SELECTED ) ) {
				$entityTypes[] = $type;
			}
		}
		return $entityTypes;
	}

	/**
	 *
	 * @return array
	 */
	public function getFilters() {
		$filters = [];
		$typeFilter = $this->getTypeFilter();
		if ( $typeFilter ) {
			$filters[] = $typeFilter;
		}
		$timestampCreatedFilter = $this->getTimestampCreatedFilter();
		if ( $timestampCreatedFilter ) {
			$filters[] = $timestampCreatedFilter;
		}

		return $filters;
	}

	/**
	 *
	 * @return array
	 */
	public function getEntityTypes() {
		$entityTypes = [];
		foreach ( $this->getEntityConfigs() as $type => $config ) {
			if ( $config->get( static::CONFIG_NAME_TYPE_ALLOWED ) ) {
				$entityTypes[$type] = $config->get(
					static::CONFIG_NAME_TYPE_ALLOWED
				);
				continue;
			}
			if ( $config->get( self::CONFIG_NAME_TYPE_ALLOWED ) ) {
				$entityTypes[$type] = $config->get(
					self::CONFIG_NAME_TYPE_ALLOWED
				);
			}
		}
		return $entityTypes;
	}

	/**
	 *
	 * @return array
	 */
	public function getAvailableFilterFields() {
		return $this->getSchema()->getFilterableFields();
	}

	/**
	 *
	 * @return array
	 */
	public function getAvailableSorterFields() {
		return $this->getSchema()->getSortableFields();
	}

	/**
	 *
	 * @return array
	 */
	public function getLockedOptionNames() {
		return [];
	}

	/**
	 *
	 * @return array
	 */
	public function getLockedFilterNames() {
		return [];
	}

	/**
	 *
	 * @return array
	 */
	public function getOutputTypes() {
		$outputTypes = [];
		foreach ( $this->getEntityConfigs() as $type => $config ) {
			if ( $config->get( static::CONFIG_NAME_OUTPUT_TYPE ) ) {
				$outputTypes[$type] = $config->get(
					static::CONFIG_NAME_OUTPUT_TYPE
				);
				continue;
			}
			if ( $config->get( self::CONFIG_NAME_OUTPUT_TYPE ) ) {
				$outputTypes[$type] = $config->get(
					self::CONFIG_NAME_OUTPUT_TYPE
				);
				continue;
			}
			$outputTypes[$type] = 'Default';
		}
		return $outputTypes;
	}

	/**
	 *
	 * @return array
	 */
	public function getPreloadTitles() {
		$preloadTitleTypes = [];
		foreach ( $this->getEntityConfigs() as $type => $config ) {
			if ( $config->get( static::CONFIG_NAME_PRELOAD_TITLE ) ) {
				$outputTypes[$type] = $config->get(
					static::CONFIG_NAME_PRELOAD_TITLE
				);
				continue;
			}
			if ( $config->get( self::CONFIG_NAME_PRELOAD_TITLE ) ) {
				$outputTypes[$type] = $config->get(
					self::CONFIG_NAME_PRELOAD_TITLE
				);
				continue;
			}
			$preloadTitleTypes[$type] = '';
		}
		return $preloadTitleTypes;
	}

	/**
	 *
	 * @return bool
	 */
	public function showEntityListMenu() {
		return true;
	}

	/**
	 *
	 * @return bool
	 */
	public function showEntitySpawner() {
		return true;
	}

	/**
	 *
	 * @return bool
	 */
	public function showEntityListMore() {
		return !$this->useEndlessScroll();
	}

	/**
	 *
	 * @return bool
	 */
	public function showHeadline() {
		return false;
	}

	/**
	 *
	 * @return bool
	 */
	public function useEndlessScroll() {
		return true;
	}

	/**
	 *
	 * @return bool
	 */
	public function useMoreScroll() {
		return true;
	}

	/**
	 *
	 * @return string
	 */
	public function getMoreLink() {
		return MediaWikiServices::getInstance()->getLinkRenderer()->makeKnownLink(
			\SpecialPage::getTitleFor( 'Timeline' ),
			new \HtmlArmor( $this->getMoreLinkMessage()->text() )
		);
	}

	/**
	 *
	 * @return \Message
	 */
	protected function getMoreLinkMessage() {
		return \Message::newFromKey( 'bs-social-entitylistmore-linklabel' );
	}

	/**
	 *
	 * @return User
	 */
	public function getUser() {
		if ( $this->user ) {
			return $this->user;
		}
		return parent::getUser();
	}

	/**
	 *
	 * @return Entity|null
	 */
	public function getParent() {
		return null;
	}

	/**
	 * Returns an array of raw entity data - either an existing one (param id
	 * required) or a new one (param type required). Make sure the preloaded
	 * entities are creatable/renderable
	 * @return \stdClass[]
	 */
	public function getPreloadedEntities() {
		return [];
	}

	/**
	 * Returns message key for the headline
	 * @return string
	 */
	public function getHeadlineMessageKey() {
		return 'timeline';
	}

	/**
	 * Should settings state be persisted
	 * @return bool
	 */
	public function getPersistSettings() {
		return false;
	}

	/**
	 * Returns the key for the renderer, that initialy is used
	 * @return string
	 */
	public function getRendererName() {
		return 'entitylist';
	}
}
