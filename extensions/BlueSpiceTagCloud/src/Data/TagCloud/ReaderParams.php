<?php

namespace BlueSpice\TagCloud\Data\TagCloud;

class ReaderParams extends \BlueSpice\Data\ReaderParams {

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
		$this->setIfAvailable( $this->limit, $params, 'count' );
	}

}
