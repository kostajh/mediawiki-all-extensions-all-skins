<?php

namespace BlueSpice\ExtendedStatistics\Data;

use UnexpectedValueException;

class AggregateFactory {
	/**
	 *
	 * @return array
	 */
	public static function getTypeMap() {
		return [
			'string' => "\\BlueSpice\\ExtendedStatistics\\Data\\Aggregate\\StringValue",
			'date' => "\\BlueSpice\\ExtendedStatistics\\Data\\Aggregate\\Date",
			'daily' => "\\BlueSpice\\ExtendedStatistics\\Data\\Aggregate\\Date\\Daily",
			'weekly' => "\\BlueSpice\\ExtendedStatistics\\Data\\Aggregate\\Date\\Weekly",
			'monthly' => "\\BlueSpice\\ExtendedStatistics\\Data\\Aggregate\\Date\\Monthly",
			'yearly' => "\\BlueSpice\\ExtendedStatistics\\Data\\Aggregate\\Date\\Yearly",
			'boolean' => "\\BlueSpice\\ExtendedStatistics\\Data\\Aggregate\\Boolean",
			'numeric' => "\\BlueSpice\\ExtendedStatistics\\Data\\Aggregate\\Numeric",
		];
	}

	/**
	 *
	 * @param array $aggregate
	 * @return Aggregate
	 * @throws UnexpectedValueException
	 */
	public static function newFromArray( $aggregate ) {
		$typeMap = static::getTypeMap();
		if ( isset( $typeMap[$aggregate[Aggregate::KEY_TYPE]] ) ) {
			return new $typeMap[$aggregate[Aggregate::KEY_TYPE]]( $aggregate );
		} else {
			throw new UnexpectedValueException(
				"No aggregate class for '{$aggregate[Filter::KEY_TYPE]}' available!"
			);
		}
	}
}
