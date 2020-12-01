<?php

namespace BlueSpice\PageAssignments\Data\Assignment;

use BlueSpice\Data\Filter;
use BlueSpice\Data\FilterFinder;
use BlueSpice\Data\IPrimaryDataProvider;
use BlueSpice\PageAssignments\Data\Record;
use BlueSpice\PageAssignments\Data\Schema;
use MediaWiki\MediaWikiServices;

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
	 * @param \BlueSpice\Data\ReaderParams $params
	 * @param \Wikimedia\Rdbms\IDatabase $db
	 */
	public function __construct( $params, $db ) {
		$this->db = $db;
	}

	/**
	 *
	 * @param \BlueSpice\Data\ReaderParams $params
	 * @return array
	 */
	public function makeData( $params ) {
		$this->data = [];

		$res = $this->db->select(
			'bs_pageassignments',
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
	 * @param \stdClass $row
	 */
	protected function appendRowToData( $row ) {
		$this->data[] = new Record( (object)[
			Record::PAGE_ID => $row->{Record::PAGE_ID},
			Record::ASSIGNEE_KEY => $row->{Record::ASSIGNEE_KEY},
			Record::ASSIGNEE_TYPE => $row->{Record::ASSIGNEE_TYPE},
			Record::POSITION => $row->{Record::POSITION},
		] );
	}

	/**
	 *
	 * @param \BlueSpice\Data\ReaderParams $params
	 * @return array
	 */
	protected function makePreFilterConds( $params ) {
		$factory = MediaWikiServices::getInstance()->getService(
			'BSPageAssignmentsAssignableFactory'
		);
		$conds = [
			Record::ASSIGNEE_TYPE => $factory->getRegisteredTypes(),
		];
		$schema = new Schema();
		$fields = array_values( $schema->getFilterableFields() );
		$filterFinder = new FilterFinder( $params->getFilter() );
		foreach ( $fields as $fieldName ) {
			$filter = $filterFinder->findByField( $fieldName );
			if ( !$filter instanceof Filter ) {
				continue;
			}
			if ( $fieldName === Record::TEXT ) {
				continue;
			}
			if ( $fieldName === Record::ANCHOR ) {
				continue;
			}
			if ( $fieldName === Record::ID ) {
				continue;
			}
			switch ( $filter->getComparison() ) {
				case Filter::COMPARISON_EQUALS:
					$conds[$fieldName] = $filter->getValue();
					$filter->setApplied();
					break;
				case Filter::COMPARISON_NOT_EQUALS:
					$conds[] = "{$filter->getValue()} != $fieldName";
					$filter->setApplied();
					break;
				case StringValue::COMPARISON_CONTAINS:
					$conds[] = "$fieldName " . $this->db->buildLike(
						$this->db->anyString(),
						$filter->getValue(),
						$this->db->anyString()
					);
					$filter->setApplied();
					break;
				case StringValue::COMPARISON_NOT_CONTAINS:
					$conds[] = "$fieldName NOT " . $this->db->buildLike(
						$this->db->anyString(),
						$filter->getValue(),
						$this->db->anyString()
					);
					$filter->setApplied();
					break;
				case StringValue::COMPARISON_STARTS_WITH:
					$conds[] = "$fieldName " . $this->db->buildLike(
						$filter->getValue(),
						$this->db->anyString()
					);
					$filter->setApplied();
					break;
				case StringValue::COMPARISON_ENDS_WITH:
					$conds[] = "$fieldName " . $this->db->buildLike(
						$this->db->anyString(),
						$filter->getValue()
					);
					$filter->setApplied();
					break;
				case Numeric::COMPARISON_GREATER_THAN:
					$conds[] = "{$filter->getValue()} > $fieldName";
					$filter->setApplied();
					break;
				case Numeric::COMPARISON_LOWER_THAN:
					$conds[] = "{$filter->getValue()} < $fieldName";
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
}
