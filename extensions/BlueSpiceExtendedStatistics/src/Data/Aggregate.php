<?php

namespace BlueSpice\ExtendedStatistics\Data;

use BlueSpice\Data\Record;

abstract class Aggregate implements IAggregate {
	const KEY_PROPERTY = 'property';
	const KEY_DIRECTION = 'direction';
	const KEY_TYPE = 'type';
	const KEY_TARGETS = 'targets';
	const KEY_MODE = 'mode';

	const ASCENDING = 'ASC';
	const DESCENDING = 'DESC';
	const MODE_OVERWRITE = 'overwrite';
	const MODE_CONCATINATE = 'concatinate';

	/**
	 *
	 * @var string
	 */
	protected $property = '';

	/**
	 *
	 * @var string
	 */
	protected $direction = '';

	/**
	 *
	 * @var string
	 */
	protected $type = '';

	/**
	 *
	 * @var array
	 */
	protected $targets = [];

	/**
	 *
	 * @var bool
	 */
	protected $applied = false;

	/**
	 *
	 * @param params $params
	 * @throws UnexpectedValueException
	 */
	public function __construct( array $params = [] ) {
		$this->type = $params[static::KEY_TYPE];
		if ( isset( $params[static::KEY_PROPERTY] ) ) {
			$this->property = $params[static::KEY_PROPERTY];
		}
		if ( !empty( $params[static::KEY_TARGETS] ) ) {
			$this->targets = $params[static::KEY_TARGETS];
		}
		if ( !is_array( $this->targets ) ) {
			$this->targets = [ $this->targets ];
		}
		$this->direction = static::ASCENDING;
		if ( isset( $params[static::KEY_DIRECTION] ) ) {
			$this->direction = $params[static::KEY_DIRECTION];
		}
		$this->direction = strtoupper( $this->direction );

		if ( !in_array( $this->direction, [ static::ASCENDING, static::DESCENDING ] ) ) {
			throw new UnexpectedValueException(
				"'{$this->direction}' is not an allowed value for argument \$direction"
			);
		}
		// TODO: mode
	}

	/**
	 *
	 * @return string
	 */
	public function getProperty() {
		return $this->property;
	}

	/**
	 *
	 * @return string One of Aggregate::ASCENDING or Aggregate::DESCENDING
	 */
	public function getDirection() {
		return $this->direction;
	}

	/**
	 *
	 * @return string valid FieldType
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 *
	 * @return array Target field names
	 */
	public function getTargets() {
		return $this->targets;
	}

	/**
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->getProperty() . ' ' . $this->getDirection();
	}

	/**
	 *
	 * @param mixed $value
	 * @param mixed $value2
	 * @return bool
	 */
	public function applies( $value, $value2 ) {
		if ( $this->applied ) {
			return false;
		}
		return $this->doesApply( $value, $value2 );
	}

	/**
	 *
	 * @param mixed $value
	 * @param mixed $value2
	 * @return bool
	 */
	abstract protected function doesApply( $value, $value2 );

	/**
	 *
	 * @param stdClass[]|array[] $aggregates
	 * @return Aggregate[]
	 */
	public static function newCollectionFromArray( $aggregates ) {
		$aggregateObjects = [];
		foreach ( $aggregates as $aggregate ) {
			if ( is_object( $aggregate ) ) {
				$aggregate = (array)$aggregate;
			}
			$aggregateObjects[] = static::makeAggregate( $aggregate );
		}
		return $aggregateObjects;
	}

	/**
	 * TODO: implement mode
	 * @param Record &$targetDataSet
	 * @param Record $dataSet
	 */
	public function merge( &$targetDataSet, $dataSet ) {
		foreach ( (array)$dataSet->getData() as $property => $value ) {
			if ( !$this->isTarget( $property ) ) {
				continue;
			}
			$targetValue = $targetDataSet->get( $property, null );
			if ( $targetValue === null || is_bool( $targetValue )
				|| !is_scalar( $targetValue ) ) {
				$targetDataSet->set( $property, $value );
				continue;
			}
			if ( is_numeric( $targetValue ) ) {
				$targetDataSet->set(
					$property,
					(int)$targetDataSet->get( $property ) + (int)$value
				);
				continue;
			}
			$targetDataSet->set(
				$property,
				$targetDataSet->get( $property ) . ", " . $value
			);
		}
	}

	/**
	 *
	 * @param string $propName
	 * @return bool
	 */
	protected function isTarget( $propName ) {
		return in_array( $propName, $this->getTargets() );
	}

	/**
	 *
	 * @param array $aggregate
	 * @return Aggregate
	 */
	protected static function makeAggregate( $aggregate ) {
		return AggregateFactory::newFromArray( $aggregate );
	}
}
