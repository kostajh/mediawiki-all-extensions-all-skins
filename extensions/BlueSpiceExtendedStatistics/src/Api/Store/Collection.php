<?php

namespace BlueSpice\ExtendedStatistics\Api\Store;

use BlueSpice\Api\Store;
use BlueSpice\ExtendedStatistics\Data\Entity\Collection\Store as CollectionStore;
use BlueSpice\ExtendedStatistics\Data\ReaderParams;
use FormatJson;

class Collection extends Store {

	/**
	 *
	 * @return CollectionStore
	 */
	protected function makeDataStore() {
		return new CollectionStore();
	}

	/**
	 * Using the settings to determine the value for the given parameter
	 *
	 * @param string $paramName Parameter name
	 * @param array|mixed $paramSettings Default value or an array of settings
	 *  using PARAM_* constants.
	 * @param bool $parseLimit Whether to parse and validate 'limit' parameters
	 * @return mixed Parameter value
	 */
	protected function getParameterFromSettings( $paramName, $paramSettings, $parseLimit ) {
		$value = parent::getParameterFromSettings( $paramName, $paramSettings, $parseLimit );
		// Unfortunately there is no way to register custom types for parameters
		if ( $paramName === 'aggregate' ) {
			$value = FormatJson::decode( $value );
			if ( empty( $value ) ) {
				return [];
			}
		}
		return $value;
	}

	/**
	 *
	 * @return ReaderParams
	 */
	protected function getReaderParams() {
		return new ReaderParams( [
			'query' => $this->getParameter( 'query', null ),
			'start' => $this->getParameter( 'start', null ),
			'limit' => $this->getParameter( 'limit', null ),
			'filter' => $this->getParameter( 'filter', null ),
			'sort' => $this->getParameter( 'sort', null ),
			'aggregate' => $this->getParameter( 'aggregate', null ),
		] );
	}

	/**
	 * Called by ApiMain
	 * @return array
	 */
	public function getAllowedParams() {
		return array_merge( parent::getAllowedParams(), [
			'aggregate' => [
				static::PARAM_TYPE => 'string',
				static::PARAM_REQUIRED => false,
				static::PARAM_DFLT => '[]',
				static::PARAM_HELP_MSG => 'apihelpbs-extendedstatistics-collection-store-param-aggregate',
			],
		] );
	}
}
