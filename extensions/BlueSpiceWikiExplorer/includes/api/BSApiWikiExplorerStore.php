<?php

use MediaWiki\MediaWikiServices;

/**
 * This class serves as a backend for the wiki explorer store.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit https://bluespice.com
 *
 * @author     Patric Wirth
 * @package    BluespiceWikiExplorer
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 *
 * Example request parameters of an ExtJS store
 */
class BSApiWikiExplorerStore extends BSApiWikiPageStore {

	/**
	 *
	 * @param Instance $oInstance
	 * @param Query $sQuery
	 * @param array $aFilter
	 * @param array &$aTables
	 * @param array &$aFields
	 * @param array &$aConditions
	 * @param array &$aOptions
	 * @param array &$aJoinOptions
	 * @param array &$aData
	 * @return bool
	 */
	public static function onBeforeQuery( $oInstance, $sQuery, $aFilter, &$aTables, &$aFields,
		&$aConditions, &$aOptions, &$aJoinOptions, &$aData ) {
		MediaWikiServices::getInstance()->getHookContainer()->run(
			'WikiExplorer::queryPagesWithFilter',
			[
				$aFilter,
				&$aTables,
				&$aFields,
				&$aConditions,
				&$aJoinOptions
			]
		);

		$aTables = array_unique( $aTables );
		$aFields = array_unique( $aFields );
		$aConditions = array_unique( $aConditions );
		$aOptions = array_unique( $aOptions );
		$aJoinOptions = array_unique( $aJoinOptions, SORT_REGULAR );

		return true;
	}

	/**
	 *
	 * @param array &$aColumns
	 */
	public static function onGetColumnDefinitions( &$aColumns ) {
		$aRenderer = [
			'page_assignments' => 'WikiExplorerLecayRender_page_assignments',
			'flaggedrevs_state' => 'WikiExplorerLecayRender_flaggedrevs_state',
			'flaggedrevs_date' => 'WikiExplorerLecayRender_flaggedrevs_date',
			'flaggedrevs_is_new_available' => 'WikiExplorerLecayRender_flaggedrevs_is_new_available',
		];
		foreach ( $aColumns as $iKey => $aColumn ) {
			if ( !isset( $aRenderer[$aColumn['dataIndex']] ) ) {
				continue;
			}
			$aColumns[$iKey]['render'] = $aRenderer[$aColumn['dataIndex']];
			if ( $aColumn['dataIndex'] == 'flaggedrevs_date' ) {
				$aColumns[$iKey]['filter'] = [
					'type' => 'date',
					'dataFormat' => 'Ymd',
				];
			}
		}
	}

	/**
	 *
	 * @param array $aData
	 * @param array $aMetaData
	 * @return type
	 */
	public function returnData( $aData, $aMetaData = [] ) {
		return parent::returnData( $aData, $this->getMetaData() );
	}

	/**
	 *
	 * @param Row $row
	 * @return type
	 */
	public function makeDataSet( $row ) {
		set_time_limit( 120 );
		$row = parent::makeDataSet( $row );
		if ( !$row ) {
			return $row;
		}
		$row->page_created = Title::newFromRow( $row )->getEarliestRevTime();
		$row->page_categories = [];
		$row->page_links = [];
		$row->page_linked_files = [];
		return $row;
	}

	/**
	 * Extends rows with metadata: Categories, page links and image links
	 * @param array $aData
	 * @return array
	 */
	public function extendRows( $aData ) {
		$aPageIds = [];
		foreach ( $aData as $oRow ) {
			$aPageIds[$oRow->page_id] = $oRow;
		}

		// Categories
		$oCatRes = $this->getDB( DB_REPLICA )->select(
			'categorylinks',
			[ 'cl_from', 'cl_to' ],
			[ 'cl_from' => array_keys( $aPageIds ) ]
		);
		foreach ( $oCatRes as $oCatRow ) {
			$aPageIds[$oCatRow->cl_from]->page_categories[] = $oCatRow->cl_to;
		}

		// Page links
		$oPageRes = $this->getDB( DB_REPLICA )->select(
			'pagelinks',
			[ 'pl_from', 'pl_title', 'pl_namespace' ],
			[ 'pl_from' => array_keys( $aPageIds ) ]
		);
		foreach ( $oPageRes as $oPageRow ) {
			$sNS = '';
			if ( !empty( $oPageRow->pl_namespace ) ) {
				$sNS = BsNamespaceHelper::getNamespaceName(
					$oPageRow->pl_namespace
				);
				$sNS .= ':';
			}
			$aPageIds[$oPageRow->pl_from]->page_links[]
				= "$sNS$oPageRow->pl_title";
		}

		// Image links
		$oImageRes = $this->getDB( DB_REPLICA )->select(
			'imagelinks',
			[ 'il_from', 'il_to' ],
			[ 'il_from' => array_keys( $aPageIds ) ]
		);
		foreach ( $oImageRes as $oImageRow ) {
			$aPageIds[$oImageRow->il_from]->page_linked_files[] = $oImageRow->il_to;
		}

		// return only the values to reset the array counter or get notices
		return array_values( $aPageIds );
	}

	/**
	 * Get the ExtJS grid config, if not already loaded. TODO: Change this!
	 * @return array
	 */
	public function getMetaData() {
		global $wgRequest;
		$metaLoaded = $wgRequest->getVal( 'metaLoaded', 'false' );
		// Handle booleans from ExtJs
		if ( !$metaLoaded || $metaLoaded == 'false' ) {
			return BlueSpice\WikiExplorer\Extension::getMetadata(
				0,
				0,
				'',
				''
			);
		}
		return [];
	}

	/**
	 *
	 * @param Query $sQuery
	 * @param array $aFilter
	 * @return array
	 */
	public function makeTables( $sQuery, $aFilter ) {
		$query = MediaWikiServices::getInstance()->getRevisionStore()->getQueryInfo();
		$query['tables'][] = 'page';
		return $query['tables'];
	}

	/**
	 *
	 * @param Query $sQuery
	 * @param array $aFilter
	 * @return array
	 */
	public function makeFields( $sQuery, $aFilter ) {
		$query = MediaWikiServices::getInstance()->getRevisionStore()->getQueryInfo();
		return array_merge( parent::makeFields( $sQuery, $aFilter ), $query['fields'], [
			'page_is_redirect',
			'page_is_new',
			'page_touched',
			'page_len',
			'page_latest',
		] );
	}

	/**
	 *
	 * @param Query $sQuery
	 * @param array $aFilter
	 * @return array
	 */
	public function makeConditions( $sQuery, $aFilter ) {
		return [ 'page_content_model' => [ '', 'wikitext' ] ];
	}

	/**
	 *
	 * @param Query $sQuery
	 * @param array $aFilter
	 * @return array
	 */
	public function makeOptions( $sQuery, $aFilter ) {
		return [
			'GROUP BY' => 'page_id',
		];
	}

	/**
	 *
	 * @param Query $sQuery
	 * @param array $aFilter
	 * @return array
	 */
	public function makeJoinOptions( $sQuery, $aFilter ) {
		$query = MediaWikiServices::getInstance()->getRevisionStore()->getQueryInfo();
		$query['joins']['revision'] = [ 'LEFT JOIN', 'page_latest = rev_id' ];
		return $query['joins'];
	}

	/**
	 * Filter, sort and trim the result according to the call parameters and
	 * apply security trimming
	 * @param array $aData
	 * @return array
	 */
	public function postProcessData( $aData ) {
		$res = $this->getServices()->getHookContainer()->run(
			'BSApiExtJSStoreBaseBeforePostProcessData',
			[
				$this,
				&$aData
			]
		);
		if ( !$res ) {
			return $aData;
		}

		$aProcessedData = [];

		// First, apply filter
		$aProcessedData = array_filter(
			$aData,
			[ $this, 'filterCallback' ]
		);
		$this->getServices()->getHookContainer()->run( 'BSApiExtJSStoreBaseAfterFilterData', [
			$this,
			&$aProcessedData
		] );

		// Next, apply sort
		$aProcessedData = $this->sortData( $aProcessedData );

		// Before we trim, we save the count
		$this->iFinalDataSetCount = count( $aProcessedData );

		// Last, do trimming
		$aProcessedData = $this->trimData( $aProcessedData );

		return $aProcessedData;
	}

	/**
	 *
	 * @return array
	 */
	public function getAllowedParams() {
		return parent::getAllowedParams() + [
			'metaLoaded' => [
				ApiBase::PARAM_TYPE => 'boolean',
				ApiBase::PARAM_REQUIRED => false,
				ApiBase::PARAM_DFLT => false,
			],
		];
	}

	/**
	 *
	 * @return array
	 */
	public function getParamDescription() {
		return parent::getParamDescription() + [
			'metaLoaded' => 'have the MetaData already been loaded?',
		];
	}

	/**
	 * C&p of WikiExplorer::getData()
	 * @param string $sQuery
	 * @return array
	 */
	public function makeData( $sQuery = '' ) {
		// DEPRECATED! Legacy query building - Please do not use!
		global $wgHooks;
		$wgHooks['BSApiExtJSDBTableStoreBeforeQuery'][]
			= "BSApiWikiExplorerStore::onBeforeQuery";
		$wgHooks['WikiExplorer::getColumnDefinitions'][]
			= "BSApiWikiExplorerStore::onGetColumnDefinitions";

		set_time_limit( 120 );
		$aData = parent::makeData( $sQuery );
		if ( empty( $aData ) ) {
			return $aData;
		}

		// DEPRECATED! Legacy data set building - Please do not use! Get rid of
		// this as soon as possible!
		$aDeprecatedData = [];
		foreach ( $aData as $iKey => $oRow ) {
			$aDeprecatedData[$oRow->page_id] = (array)$oRow;
		}
		$this->getServices()->getHookContainer()->run( 'WikiExplorer::buildDataSets', [
			&$aDeprecatedData
		] );
		foreach ( $aDeprecatedData as $iKey => $aRow ) {
			$aDeprecatedData[$iKey] = (object)$aRow;
		}

		return $this->extendRows( $aDeprecatedData );
	}

	/**
	 *
	 * @param object $aDataSet
	 * @return bool
	 */
	public function filterCallback( $aDataSet ) {
		set_time_limit( 120 );
		$aFilter = $this->getParameter( 'filter' );
		$aArrayFilters = [
			'page_categories',
			'page_links',
			'page_linked_files',
		];
		foreach ( $aFilter as $oFilter ) {
			if ( !isset( $oFilter->field ) && isset( $oFilter->property ) ) {
				$oFilter->field = $oFilter->property;
			}
			if ( !isset( $oFilter->comparison ) && isset( $oFilter->operator ) ) {
				$oFilter->comparison = $oFilter->operator;
			}

			if ( empty( $oFilter->type ) ) {
				continue;
			}
			if ( !isset( $oFilter->field ) ) {
				if ( !isset( $oFilter->property ) ) {
					continue;
				}
				$oFilter->field = $oFilter->property;
			}

			// Check possible custom filters
			$bResult = true;
			$bReturn = $this->getServices()->getHookContainer()->run(
				'WikiExplorerStoreFilterCallbck',
				[
					$aDataSet,
					$oFilter,
					&$bResult,
				]
			);
			// Force return true when hook handler gets abborted.
			// Also return false result immediately
			if ( !$bReturn || !$bResult ) {
				return $bResult;
			}

			if ( in_array( $oFilter->field, $aArrayFilters ) ) {
				foreach ( $aDataSet->{$oFilter->field} as $sValue ) {
					$bRes = $this->filterString(
						$oFilter,
						(object)[ $oFilter->field => $sValue ]
					);
					if ( !$bRes ) {
						continue;
					}
					return true;
				}
				return false;
			}

			// Check namespace names instead of index numbers
			if ( $oFilter->field === 'page_namespace' ) {
				$sNs = BsNamespaceHelper::getNamespaceName(
					$aDataSet->{$oFilter->field}
				);
				$bRes = $this->filterString(
					$oFilter,
					(object)[ $oFilter->field => $sNs ]
				);
				if ( $bRes ) {
					return true;
				}
				// Do not return false jet. Also check namespace indexes as well
				return parent::filterCallback( $aDataSet );
			}
		}

		return parent::filterCallback( $aDataSet );
	}
}
