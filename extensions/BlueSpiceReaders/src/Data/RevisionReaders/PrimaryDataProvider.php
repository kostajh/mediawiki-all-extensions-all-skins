<?php

namespace BlueSpice\Readers\Data\RevisionReaders;

use BlueSpice\Data\Filter;
use BlueSpice\Data\FilterFinder;
use BlueSpice\Readers\Data\Record;
use BlueSpice\Readers\Data\Schema;

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
			'ORDER BY' => 'readers_ts ASC'
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
			$this->makePreFilterConds( $params ),
			__METHOD__,
			$this->makePreOptionConds( $params )
		);

		foreach ( $rows as $row ) {
			$record = new Record( $row );
			$this->appendRowToData( $record );
		}

		return $this->data;
	}

	/**
	 *
	 * @param \BlueSpice\Data\ReaderParams $params
	 * @return array
	 */
	protected function makePreFilterConds( $params ) {
		$conds = [];
		$schema = new Schema();
		$fields = array_values( $schema->getFilterableFields() );
		$filterFinder = new FilterFinder( $params->getFilter() );
		foreach ( $fields as $fieldName ) {
			$filter = $filterFinder->findByField( $fieldName );
			if ( !$filter instanceof Filter ) {
				continue;
			}
			switch ( $filter->getComparison() ) {
				case Filter\StringValue::COMPARISON_CONTAINS:
					$conds[] = "$fieldName " . $this->db->buildLike(
						$this->db->anyString(),
						$filter->getValue(),
						$this->db->anyString()
					);
					$filter->setApplied();
					break;
			}
		}
		return $conds;
	}

	/**
	 *
	 * @param \BlueSpice\Data\ReaderParams $params
	 * @return array
	 */
	protected function makePreOptionConds( $params ) {
		$conds = [];

		$schema = new Schema();
		$fields = array_values( $schema->getSortableFields() );

		foreach ( $params->getSort() as $sort ) {
			if ( !in_array( $sort->getProperty(), $fields ) ) {
				continue;
			}
			if ( !isset( $conds['ORDER BY'] ) ) {
				$conds['ORDER BY'] = "";
			} else {
				$conds['ORDER BY'] .= ",";
			}
			$conds['ORDER BY'] .=
				"{$sort->getProperty()} {$sort->getDirection()}";
		}
		return $conds;
	}

	/**
	 *
	 * @param Record $record
	 */
	protected function appendRowToData( Record $record ) {
		$this->data[] = $record;
	}
}
