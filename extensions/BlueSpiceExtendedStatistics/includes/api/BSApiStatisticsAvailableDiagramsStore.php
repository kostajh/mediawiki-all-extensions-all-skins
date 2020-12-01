<?php

use BlueSpice\ExtendedStatistics\DiagramFactory;

class BSApiStatisticsAvailableDiagramsStore extends BSApiExtJSStoreBase {
	/**
	 *
	 * @param string $sQuery
	 * @return \stdClass[]
	 */
	protected function makeData( $sQuery = '' ) {
		$aData = [];

		foreach ( $this->getFactory()->getDiagrams() as $oDiagram ) {
			$aFilterKeys = [];
			foreach ( $oDiagram->getFilters() as $key => $oFilter ) {
				$aFilterKeys[] = $key;
			}

			$oTemplate = new stdClass();
			$oTemplate->key = $oDiagram->getDiagramKey();
			$oTemplate->displaytitle = $oDiagram->getTitle();
			$oTemplate->listable = $oDiagram->isListable();
			$oTemplate->filters = $aFilterKeys;
			$oTemplate->isDefault = $oDiagram->getIsDefault();
			$aData[] = $oTemplate;
		}

		return $aData;
	}

	/**
	 *
	 * @return DiagramFactory
	 */
	protected function getFactory() {
		return $this->getServices()->getService( 'BSExtendedStatisticsDiagramFactory' );
	}
}
