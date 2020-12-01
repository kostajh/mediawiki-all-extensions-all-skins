<?php

namespace BlueSpice\PageAssignments\Data\Page;

use BlueSpice\Data\Filter;
use BlueSpice\Data\Filter\StringValue;
use BlueSpice\Data\FilterFinder;
use BlueSpice\Data\Page\PrimaryDataProvider as PageDataProvider;
use BlueSpice\Data\ReaderParams;
use BlueSpice\PageAssignments\Assignment;
use BlueSpice\PageAssignments\Data\Record as AssignmentRecord;
use BsStringHelper;
use MediaWiki\MediaWikiServices;
use Title;

class PrimaryDataProvider extends PageDataProvider {

	/**
	 *
	 * @var Assignment[]
	 */
	protected $pageAssignments = null;

	/**
	 *
	 * @var ReaderParams
	 */
	protected $readerParams = null;

	/**
	 *
	 * @param ReaderParams $params
	 * @return Record[]
	 */
	public function makeData( $params ) {
		$this->readerParams = $params;
		return parent::makeData( $params );
	}

	/**
	 *
	 * @param \stdClass $row
	 * @return null
	 */
	protected function appendRowToData( \stdClass $row ) {
		$title = Title::newFromRow( $row );
		if ( !$title || !$this->userCanRead( $title ) ) {
			return;
		}
		$row->{Record::ASSIGNMENTS} = [];
		$row->{Record::PREFIXED_TEXT} = $title->getPrefixedText();
		$assignments = $this->getPageAssignments();
		$assignmentQuery = '';
		foreach ( $this->readerParams->getFilter() as $filter ) {
			if ( $filter->getField() !== Record::ASSIGNMENTS ) {
				continue;
			}
			if ( empty( $filter->getValue() ) ) {
				break;
			}
			$assignmentQuery = $filter->getValue();
		}
		if ( isset( $assignments[$row->{Record::ID}] ) ) {
			$assignData = [];
			if ( !empty( $assignmentQuery ) ) {
				$res = false;
				foreach ( $assignments[$row->{Record::ID}] as $assignment ) {
					$res = BsStringHelper::filter(
						StringValue::COMPARISON_CONTAINS,
						(string)$assignment->getText(),
						$assignmentQuery
					);
					if ( $res ) {
						break;
					}
				}
				if ( !$res ) {
					return;
				}
			}
			foreach ( $assignments[$row->{Record::ID}] as $assignment ) {
				$assignData[] = $assignment->toStdClass();
			}
			$row->{Record::ASSIGNMENTS} = $assignData;

		} elseif ( !empty( $assignmentQuery ) ) {
			return;
		}

		$fields = [ Record::ID, Record::NS, Record::TITLE, Record::IS_REDIRECT,
			Record::ID_NEW, Record::TOUCHED, Record::LATEST, Record::CONTENT_MODEL,
			Record::ASSIGNMENTS, Record::PREFIXED_TEXT ];
		$data = [];
		foreach ( $fields as $key ) {
			$data[ $key ] = $row->{$key};
		}
		$record = new Record( (object)$data );
		MediaWikiServices::getInstance()->getHookContainer()->run(
			'BSPageStoreDataProviderBeforeAppendRow',
			[
				$this,
				$record,
				$title,
			]
		);
		if ( !$record ) {
			return;
		}
		$this->data[] = $record;
	}

	/**
	 *
	 * @return Assignment[]
	 */
	protected function getPageAssignments() {
		if ( $this->pageAssignments !== null ) {
			return $this->pageAssignments;
		}
		$assignmentFactory = MediaWikiServices::getInstance()->getService(
			'BSPageAssignmentsAssignmentFactory'
		);
		$recordSet = $assignmentFactory->getStore()->getReader()->read(
			new ReaderParams( [
				ReaderParams::PARAM_LIMIT => ReaderParams::LIMIT_INFINITE
			] )
		);
		$this->pageAssignments = [];
		foreach ( $recordSet->getRecords() as $record ) {
			$id = $record->get( AssignmentRecord::PAGE_ID );
			$title = Title::newFromID( $id );
			if ( !$title || !$title->exists() ) {
				continue;
			}
			$this->pageAssignments[$id][] = $assignmentFactory->factory(
				$record->get( AssignmentRecord::ASSIGNEE_TYPE ),
				$record->get( AssignmentRecord::ASSIGNEE_KEY ),
				$title
			);
		}

		return $this->pageAssignments;
	}

	/**
	 *
	 * @return array
	 */
	protected function getDefaultConds() {
		return [ Record::CONTENT_MODEL => [ 'wikitext', '' ] ];
	}

	/**
	 *
	 * @param ReaderParams $params
	 * @return array
	 */
	protected function makePreFilterConds( ReaderParams $params ) {
		$conds = $this->getDefaultConds();
		$fields = array_values( $this->schema->getFilterableFields() );
		$filterFinder = new FilterFinder( $params->getFilter() );
		foreach ( $fields as $fieldName ) {
			if ( !isset( $this->schema[$fieldName] ) ) {
				continue;
			}
			$filters = $filterFinder->findAllFiltersByField( $fieldName );
			foreach ( $filters as $filter ) {
				if ( !$filter instanceof Filter ) {
					continue;
				}
				if ( $this->skipPreFilter( $filter ) ) {
					continue;
				}

				$this->appendPreFilterCond( $conds, $filter );
			}
		}
		return $conds;
	}

	/**
	 * @param Filter $filter
	 * @return bool
	 */
	protected function skipPreFilter( Filter $filter ) {
		if ( $filter->getField() === Record::ASSIGNMENTS ) {
			$filter->setApplied();
			return true;
		}
		if ( $filter->getField() === Record::PREFIXED_TEXT ) {
			return true;
		}
		return false;
	}

	/**
	 *
	 * @param ReaderParams $params
	 * @return array
	 */
	protected function makePreOptionConds( ReaderParams $params ) {
		$conds = $this->getDefaultOptions();

		foreach ( $params->getSort() as $sort ) {
			if ( $sort->getProperty() !== Record::ASSIGNMENTS ) {
				continue;
			}
			return $conds;
		}
		return parent::makePreOptionConds( $params );
	}

}
