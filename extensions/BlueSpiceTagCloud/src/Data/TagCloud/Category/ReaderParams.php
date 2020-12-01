<?php

namespace BlueSpice\TagCloud\Data\TagCloud\Category;

use BlueSpice\Data\Filter;
use BlueSpice\Data\Filter\ListValue;
use BlueSpice\TagCloud\Data\TagCloud\Record;

class ReaderParams extends \BlueSpice\TagCloud\Data\TagCloud\ReaderParams {

	/**
	 * For paging
	 * @var int
	 */
	protected $limit = 40;

	/**
	 *
	 * @param array $params
	 */
	public function __construct( $params = [] ) {
		parent::__construct( $params );

		if ( !empty( $params['exclude'] ) ) {

			$excludes = [];
			foreach ( explode( ',', $params['exclude'] ) as $exclude ) {
				$exclude = str_replace( ' ', '_', trim( $exclude ) );
				$excludes[] = $exclude;
			}
			$this->filter = Filter::newCollectionFromArray( [ (object)[
				ListValue::KEY_PROPERTY => Record::NAME,
				Filter::KEY_TYPE => 'list',
				ListValue::KEY_COMPARISON => ListValue::COMPARISON_NOT_CONTAINS,
				ListValue::KEY_VALUE => $excludes
			] ] );
		}
	}
}
