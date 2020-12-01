<?php

class ApiBookshelfPageCollectionStore extends BSApiExtJSStoreBase {

	/**
	 *
	 * @param string $sQuery
	 * @return array
	 */
	protected function makeData( $sQuery = '' ) {
		$aPages = [];
		$dbr = $this->getDB();

		$pageCollectionPrefix = wfMessage( 'bs-pagecollection-prefix' )->plain();
		$pageCollectionPrefix .= "/";

		$res = $dbr->select(
			'page',
			[ 'page_title' ],
			[
				"page_namespace" => NS_MEDIAWIKI,
				"page_title" . $dbr->buildLike( $pageCollectionPrefix . $sQuery, $dbr->anyString() )
			]
		);

		foreach ( $res as $row ) {
			$sPageTitle = str_replace( $pageCollectionPrefix, '', $row->page_title );

			$oPageData = new stdClass();
			$oPageData->pc_title = $sPageTitle;
			$aPages[ $sPageTitle ] = $oPageData;
		}
		ksort( $aPages );
		$aPages = array_values( $aPages );

		return $aPages;
	}
}
