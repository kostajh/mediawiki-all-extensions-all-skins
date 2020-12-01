<?php

namespace BlueSpice\Readers\Data\PageReaders;

use BlueSpice\Readers\Data\Record;

class PrimaryDataProvider implements \BlueSpice\Data\IPrimaryDataProvider {

	/**
	 *
	 * @var \BlueSpice\Data\Record[]
	 */
	protected $data = [];

	/**
	 *
	 * @var \Wikimedia\Rdbms\IDatabase
	 */
	protected $db = null;

	/**
	 *
	 * @var \IContextSource
	 */
	protected $context = null;

	/**
	 *
	 * @param \Wikimedia\Rdbms\IDatabase $db
	 * @param \IContextSource $context
	 */
	public function __construct( $db, $context ) {
		$this->db = $db;
		$this->context = $context;
	}

	/**
	 *
	 * @param \BlueSpice\Data\ReaderParams $params
	 * @return array
	 */
	public function makeData( $params ) {
		$this->data = [];

		$conds = [];
		$options = [
			'ORDER BY' => 'readers_ts ASC',
			'GROUP BY' => Record::USER_ID
		];

		$filters = $params->getFilter();
		foreach ( $filters as $filter ) {
			if ( $filter->getField() === Record::PAGE_ID && $filter->getComparison() === 'eq' ) {
				$pageId = $filter->getValue();
				$conds['readers_page_id'] = $pageId;
				break;
			}
		}

		$rows = $this->db->select(
			'bs_readers',
			'*',
			$conds,
			__METHOD__,
			$options
		);

		foreach ( $rows as $row ) {
			$record = new Record( $row );
			$this->appendRowToData( $record );
		}

		return $this->data;
	}

	/**
	 *
	 * @param Record $record
	 */
	protected function appendRowToData( Record $record ) {
		$this->data[] = $record;
	}
}
