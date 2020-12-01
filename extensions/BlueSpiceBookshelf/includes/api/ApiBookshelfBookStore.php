<?php

class ApiBookshelfBookStore extends BSApiExtJSStoreBase {

	/**
	 *
	 * @var string
	 */
	protected $root = 'children';

	/**
	 *
	 * @param string $sQuery
	 * @return stdClass[]
	 */
	public function makeData( $sQuery = '' ) {
		$aParams = $this->extractRequestParams( false );
		$aResult = [];

		try{
			$oPHP = PageHierarchyProvider::getInstanceFor( $aParams['book'] );
			$oTree = $oPHP->getExtendedTOCJSON();
			if ( $aParams['node'] && $aParams['node'] !== 'root' ) {
				$oTree = $this->findNodeByPath( $oTree, explode( '.', $aParams['node'] ) );
			}

			if ( isset( $oTree->children ) ) {
				foreach ( $oTree->children as $oChild ) {
					if ( !empty( $oChild->children ) ) {
						$oChild->leaf = false;
						$oChild->expanded = false;
						$oChild->loaded = false;
					} else {
						$oChild->leaf = true;
					}
					unset( $oChild->children );
					$aResult[] = $oChild;
				}
			}

			$this->getServices()->getHookContainer()->run( 'BSBookshelfBookStoreMakeData', [
				&$aResult
			] );
		} catch ( Exception $ex ) {

		}

		return $aResult;
	}

	/**
	 *
	 * @param stdClass[] $aProcessedData
	 * @return stdClass[]
	 */
	public function sortData( $aProcessedData ) {
		// Otherwise there is a strange default sorting
		return $aProcessedData;
		// TODO: Implement reasonable sorting for tree
	}

	/**
	 *
	 * @return array
	 */
	public function getAllowedParams() {
		return parent::getAllowedParams() + [
			'node' => [
				ApiBase::PARAM_TYPE => 'string'
			],
			'book' => [
				ApiBase::PARAM_TYPE => 'string'
			],
		];
	}

	private function findNodeByPath( $oNode, $aPath ) {
		$iIndex = (int)array_shift( $aPath ) - 1;
		if ( isset( $oNode->children ) ) {
			$oCurrentNode = $oNode->children[$iIndex];
			if ( empty( $aPath ) ) {
				return $oCurrentNode;
			} else {
				return $this->findNodeByPath( $oCurrentNode, $aPath );
			}
		}
	}
}
