<?php

namespace BlueSpice\CategoryManager\Data\Page;

use BlueSpice\Data\Filter;
use BlueSpice\Data\Filter\StringValue;
use BlueSpice\Data\FilterFinder;
use BlueSpice\Data\Page\PrimaryDataProvider as PageDataProvider;
use BlueSpice\Data\ReaderParams;
use BsStringHelper;
use MediaWiki\MediaWikiServices;
use Title;

class PrimaryDataProvider extends PageDataProvider {

	/**
	 *
	 * @var string[]
	 */
	protected $categories = null;

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
		$record = $this->getRecordFromTitle( $title );
		if ( !$record ) {
			return;
		}
		$categories = $this->getCategories();
		$record->set( Record::CATEGORIES, [] );
		$record->set( Record::PREFIXED_TEXT, $title->getPrefixedText() );
		$categoryQuery = '';
		foreach ( $this->readerParams->getFilter() as $filter ) {
			if ( $filter->getField() !== Record::CATEGORIES ) {
				continue;
			}
			if ( empty( $filter->getValue() ) ) {
				break;
			}
			$categoryQuery = $filter->getValue();
		}
		if ( isset( $categories[$record->get( Record::ID )] ) ) {
			if ( !empty( $categoryQuery ) ) {
				$res = false;
				foreach ( $categories[$record->get( Record::ID )] as $category ) {
					$res = BsStringHelper::filter(
						StringValue::COMPARISON_CONTAINS,
						$category,
						$categoryQuery
					);
					if ( $res ) {
						break;
					}
				}
				if ( !$res ) {
					return;
				}
			}
			$record->set(
				Record::CATEGORIES,
				$categories[$record->get( Record::ID )]
			);

		} elseif ( !empty( $categoryQuery ) ) {
			return;
		}

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
	 * @return array
	 */
	protected function getCategories() {
		if ( $this->categories !== null ) {
			return $this->categories;
		}
		$res = $this->db->select(
			'categorylinks',
			[ 'cl_from AS page_id', 'cl_to AS page_title, ' . NS_CATEGORY . ' AS page_namespace' ],
			[],
			__METHOD__
		);
		$this->categories = [];
		foreach ( $res as $row ) {
			if ( !isset( $this->categories[$row->page_id] ) ) {
				$this->categories[$row->page_id] = [];
			}
			$this->categories[$row->page_id][] = "$row->page_namespace:$row->page_title";
		}

		return $this->categories;
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
		if ( $filter->getField() === Record::CATEGORIES ) {
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
			if ( $sort->getProperty() !== Record::CATEGORIES ) {
				continue;
			}
			return $conds;
		}
		return parent::makePreOptionConds( $params );
	}

}
