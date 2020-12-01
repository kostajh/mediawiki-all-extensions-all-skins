<?php

namespace BlueSpice\ExtendedStatistics\ExtendedSearch\MappingProvider;

use BlueSpice\Data\FieldType;
use BlueSpice\ExtendedStatistics\Data\Entity\Collection\Schema;
use BlueSpice\ExtendedStatistics\Data\Entity\Collection\Store;

class Collection extends \BS\ExtendedSearch\Source\MappingProvider\WikiPage {
	const PREFIX = 'collectiondata';
	const TYPE = 'type';

	const TEXT = 'text';
	const KEYWORD = 'keyword';
	const INTEGER = 'integer';
	const BOOLEAN = 'boolean';
	const DATE = 'date';
	const FLOAT = 'float';

	/**
	 *
	 * @return array
	 */
	public function getPropertyConfig() {
		$aPC = parent::getPropertyConfig();

		$store = new Store();
		$schema = $store->getReader( \RequestContext::getMain() )->getSchema();
		foreach ( $schema->getIndexableFields() as $sKey ) {
			if ( !$sKey ) {
				continue;
			}
			$fieldname = static::PREFIX . ".$sKey";
			$aPC[$fieldname] = $this->mapDataFieldTypeToSearchFieldType(
				$sKey,
				$schema[$sKey]
			);
		}

		return $aPC;
	}

	/**
	 *
	 * @param string $key
	 * @param array $definiton
	 * @return array
	 */
	protected function mapDataFieldTypeToSearchFieldType( $key, $definiton ) {
		$mapping = static::getValueTypeMapping();
		$type = static::TEXT;
		if ( isset( $mapping[$definiton[Schema::TYPE]] ) ) {
			$type = $mapping[$definiton[Schema::TYPE]];
		}

		$return = [
			static::TYPE => $type,
		];

		// Copy all text values to our "_all" field to speed up the search
		if ( $type == static::TEXT ) {
			$return['copy_to'] = [ 'congregated' ];
		}
		// We need header to be available for autocomplete
		if ( $key == 'header' ) {
			$return['copy_to'] = [ 'congregated', 'ac_ngram' ];
		}

		return $return;
	}

	/**
	 *
	 * @return array
	 */
	public static function getValueTypeMapping() {
		return [
			FieldType::STRING => static::KEYWORD,
			FieldType::INT => static::INTEGER,
			FieldType::BOOLEAN => static::BOOLEAN,
			FieldType::DATE => static::DATE,
			FieldType::FLOAT => static::FLOAT,
			FieldType::TEXT => static::TEXT,
			FieldType::LISTVALUE => static::KEYWORD,
		];
	}
}
