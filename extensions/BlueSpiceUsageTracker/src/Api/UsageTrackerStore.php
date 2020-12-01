<?php
namespace BS\UsageTracker\Api;

use MediaWiki\MediaWikiServices;

class UsageTrackerStore extends \BSApiExtJSStoreBase {

	/**
	 * @param string $sQuery
	 * @return array
	 */
	protected function makeData( $sQuery = '' ) {
		$aData = [];
		$extension = MediaWikiServices::getInstance()
			->getService( 'BSExtensionFactory' )
			->getExtension( 'BlueSpiceUsageTracker' );
		$aRes = $extension->getUsageDataFromDB();
		foreach ( $aRes as $oCollectorResult ) {
			$aData[] = $this->makeDataRow( $oCollectorResult );
		}
		return $aData;
	}

	/**
	 *
	 * @param \BS\UsageTracker\CollectorResult $oCollectorResult
	 * @return \stdClass
	 */
	protected function makeDataRow( \BS\UsageTracker\CollectorResult $oCollectorResult ) {
		return (object)array_merge(
			(array)$oCollectorResult,
			[
				'description' => $oCollectorResult->getDescription(),
				'updateDate' => $this->getLanguage()->timeanddate(
					$oCollectorResult->getUpdateDate(),
					true
				),
			]
		);
	}

}
