<?php

namespace BlueSpice\Rating\Data;

use BlueSpice\Data\Filter;
use BlueSpice\Data\FilterFinder;
use BlueSpice\Data\IPrimaryDataProvider;

class PrimaryDataProvider implements IPrimaryDataProvider {

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
	 * @param \Wikimedia\Rdbms\IDatabase $db
	 */
	public function __construct( $db ) {
		$this->db = $db;
	}

	/**
	 *
	 * @param \BlueSpice\Data\ReaderParams $params
	 * @return Records[]
	 */
	public function makeData( $params ) {
		$this->data = [];

		$res = $this->db->select(
			'bs_rating',
			'*',
			$this->makePreFilterConds( $params ),
			__METHOD__,
			$this->makePreOptionConds( $params )
		);
		foreach ( $res as $row ) {
			$this->appendRowToData( $row );
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
			$conds[$fieldName] = $filter->getValue();
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
	 * @param \stdClass $row
	 */
	protected function appendRowToData( $row ) {
		$this->data[] = new Record( (object)[
			Record::ID => $row->{Record::ID},
			Record::REFTYPE => $row->{Record::REFTYPE},
			Record::REF => $row->{Record::REF},
			Record::USERID => $row->{Record::USERID},
			Record::USERIP => $row->{Record::USERIP},
			Record::VALUE => $row->{Record::VALUE},
			Record::CREATED => $row->{Record::CREATED},
			Record::TOUCHED => $row->{Record::TOUCHED},
			Record::SUBTYPE => $row->{Record::SUBTYPE},
			Record::CONTEXT => $row->{Record::CONTEXT},
		] );
	}
}
