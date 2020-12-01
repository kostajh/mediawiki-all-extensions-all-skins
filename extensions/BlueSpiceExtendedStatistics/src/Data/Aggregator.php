<?php

namespace BlueSpice\ExtendedStatistics\Data;

use BlueSpice\Data\Record;
use FormatJson;

class Aggregator {

	/**
	 *
	 * @var Aggregate[]
	 */
	protected $aggregates = null;

	/**
	 *
	 * @param Aggregate[] $aggregates
	 */
	public function __construct( $aggregates ) {
		$this->aggregates = $aggregates;
	}

	/**
	 *
	 * @param Record[] $dataSets
	 * @param array $unaggregateableProps
	 * @return Record[]
	 */
	public function aggregate( $dataSets, $unaggregateableProps = [] ) {
		if ( empty( $dataSets ) ) {
			return $dataSets;
		}
		$aggregated = [];
		foreach ( $this->aggregates as $aggregate ) {
			$property = $aggregate->getProperty();
			if ( in_array( $property, $unaggregateableProps ) ) {
				continue;
			}
			$direction = $this->getAggregateDirection( $aggregate );
			if ( $direction === Aggregate::DESCENDING ) {
				$dataSets = array_reverse( $dataSets );
				$aggregated = array_reverse( $aggregated );
			}
			foreach ( $dataSets as $dataSet ) {
				if ( $dataSet->get( $property, null ) === null ) {
					$aggregated[] = $dataSet;
					continue;
				}
				$applies = false;
				foreach ( $aggregated as $aggrDataSet ) {
					if ( $aggrDataSet->get( $property, null ) === null ) {
						continue;
					}
					$applies = $aggregate->applies(
						$dataSet->get( $property ),
						$aggrDataSet->get( $property )
					);
					if ( $applies ) {
						$aggregate->merge( $aggrDataSet, $dataSet );
						break;
					}
				}
				if ( !$applies ) {
					$aggregated[] = $dataSet;
				}
			}
			if ( $direction === Aggregate::DESCENDING ) {
				$dataSets = array_reverse( $dataSets );
				$aggregated = array_reverse( $aggregated );
			}
		}
		if ( empty( $aggregated ) ) {
			return $dataSets;
		}

		return array_values( $aggregated );
	}

	/**
	 * Returns the value a for a field a dataset is being aggregated by.
	 * May be overridden by subclass to allow custom aggregating
	 * @param Record $dataSet
	 * @param string $property
	 * @return string
	 */
	protected function getAggregateValue( $dataSet, $property ) {
		$value = $dataSet->get( $property );
		if ( is_array( $value ) ) {
			return $this->getAggregateValueFromList( $value, $dataSet, $property );
		}

		return $value;
	}

	/**
	 * Normalizes an array to a string value that can be used in aggregate logic.
	 * May be overridden by subclass to customize aggregating.
	 * Assumes that array entries can be casted to string.
	 * @param array $values
	 * @param Record $dataSet
	 * @param string $property
	 * @return string
	 */
	protected function getAggregateValueFromList( $values, $dataSet, $property ) {
		$combinedValue = '';
		foreach ( $values as $value ) {
			// PHP 7 workaround. In PHP 7 cast throws no exception. It's a fatal error so no way to catch
			if ( $this->canBeCastedToString( $value ) ) {
				$combinedValue .= (string)$value;
			} else {
				$combinedValue .= FormatJson::encode( $value );
			}
		}
		return $combinedValue;
	}

	/**
	 * Checks if a array or object ist castable to string.
	 *
	 * @param mixed $value
	 * @return bool
	 */
	protected function canBeCastedToString( $value ) {
		if ( !is_array( $value ) &&
			( !is_object( $value ) && settype( $value, 'string' ) !== false ) ||
			( is_object( $value ) && method_exists( $value, '__toString' ) ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @param Aggregate $aggregate
	 * @return int Constant value of SORT_ASC or SORT_DESC
	 */
	protected function getAggregateDirection( $aggregate ) {
		return $aggregate->getDirection();
	}

}
