<?php

namespace BlueSpice\Rating\Data\Item\Article;

use BlueSpice\Data\SecondaryDataProvider as SecondaryDataProviderBase;
use BsNamespaceHelper;

class SecondaryDataProvider extends SecondaryDataProviderBase {

	public function __construct() {
	}

	/**
	 *
	 * @param Record &$dataSet
	 */
	protected function doExtend( &$dataSet ) {
		$dataSet->set(
			Record::PAGENAMESPACETEXT,
			$this->getNameSpaceText( $dataSet->get( Record::PAGENAMESPACE ) )
		);
	}

	/**
	 *
	 * @param int $nsId
	 * @return string
	 */
	private function getNameSpaceText( $nsId = 0 ) {
		return BsNamespaceHelper::getNamespaceName( $nsId );
	}
}
