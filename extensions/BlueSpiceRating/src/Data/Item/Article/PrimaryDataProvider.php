<?php

namespace BlueSpice\Rating\Data\Item\Article;

use BlueSpice\Data\Filter;
use BlueSpice\Data\Filter\Numeric;
use BlueSpice\Data\Filter\StringValue;
use BlueSpice\Data\FilterFinder;
use BlueSpice\Data\ReaderParams;
use BlueSpice\Rating\RatingItem;
use MediaWiki\MediaWikiServices;

class PrimaryDataProvider extends \BlueSpice\Rating\Data\Item\PrimaryDataProvider {

	/**
	 *
	 * @param ReaderParams $params
	 * @return Record[]
	 */
	public function makeData( $params ) {
		$this->data = [];

		$fields = [
			'rat_ref',
			'rat_reftype',
			"ROUND(AVG( rat_value ),1) AS average",
			'COUNT(rat_value) as totalcount',
			'page_id',
			'page_title',
			'page_namespace',
		];

		$res = $this->db->select(
			[ 'bs_rating', 'page' ],
			$fields,
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
	 * @param ReaderParams $params
	 * @return array
	 */
	protected function makePreFilterConds( $params ) {
		$conds = [
			'rat_reftype' => 'article',
			'page_id = rat_ref',
		];
		$schema = new Schema();
		$fields = array_values( $schema->getFilterableFields() );
		$filterFinder = new FilterFinder( $params->getFilter() );
		foreach ( $fields as $fieldName ) {
			$filter = $filterFinder->findByField( $fieldName );
			if ( !$filter instanceof Filter ) {
				continue;
			}
			if ( $fieldName === Record::AVERAGE ) {
				continue;
			}
			if ( $fieldName === Record::TOTALCOUNT ) {
				continue;
			}
			if ( $filter->getField() === 'page_namespace' ) {
				$filter->setApplied();
				$nsIdxes = [];
				foreach ( $filter->getValue() as $value ) {
					$nsIdxes[] = \BsNamespaceHelper::getNamespaceIndex( $value );
				}
				if ( !empty( $nsIdxes ) ) {
					$conds[$fieldName] = $nsIdxes;
				}
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
	 * @param ReaderParams $params
	 * @return array
	 */
	protected function makePreOptionConds( $params ) {
		$conds = [
			'GROUP BY' => 'rat_reftype, rat_ref, rat_subtype',
		];

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
	 * @param RatingItem $rating
	 * @return array
	 */
	protected function extractDataFromRow( $row, $rating ) {
		return array_merge( parent::extractDataFromRow( $row, $rating ), [
			Record::AVERAGE => $row->{Record::AVERAGE},
			Record::TOTALCOUNT => $row->{Record::TOTALCOUNT},
			Record::PAGENAMESPACE => $row->{Record::PAGENAMESPACE},
			Record::PAGETITLE => $row->{Record::PAGETITLE},
		] );
	}

	/**
	 *
	 * @param \stdClass $row
	 * @return RatingItem
	 */
	protected function makeRatingItem( $row ) {
		$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'bsg' );
		$namespaces = $config->get( 'RatingArticleEnabledNamespaces' );
		$ns = $row->{Record::PAGENAMESPACE};
		if ( !in_array( $ns, $namespaces ) ) {
			return null;
		}
		return parent::makeRatingItem( $row );
	}
}
