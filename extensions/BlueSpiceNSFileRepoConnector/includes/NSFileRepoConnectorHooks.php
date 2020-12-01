<?php

class NSFileRepoConnectorHooks {

	public static function setup() {
		if ( isset( $GLOBALS['wgWebDAVNamespaceCollections'] ) ) {
			$GLOBALS['wgWebDAVNamespaceCollections'][NS_MEDIA]
				= 'WebDAVNSFRNamespacesCollection';
		}

		global $wgResourceModules;

		if ( isset( $wgResourceModules[ 'ext.bluespice.extendedFilelist' ] ) ) {
			$wgResourceModules[ 'ext.bluespice.extendedFilelist' ][ 'dependencies' ][]
				= 'ext.bluespice.NSFRC.insertFile';
		}
	}

	/**
	 * Exposes basic permissions of this user to the client side.
	 * This should not be used for _all_ permissions!
	 * TODO: Maybe move to BSF
	 * @param array &$vars
	 * @param OutputPage $out
	 * @return bool
	 */
	public static function onMakeGlobalVariablesScript( &$vars, $out ) {
		$oNamespaceList = new NSFileRepo\NamespaceList(
			$out->getUser(),
			new GlobalVarConfig( 'egNSFileRepo' ),
			$out->getLanguage()
		);

		$vars['bsgNSBasePermissions'] = [
			'read' => array_keys( $oNamespaceList->getReadable() ),
			'edit' => array_keys( $oNamespaceList->getEditable() )
		];

		return true;
	}

	private static $aCheckedNs = [];
	private static $aNoReadNs = [];

	/**
	 * Hook handler for BSApiExtJSStoreBaseBeforePostProcessData.
	 * Check permissions and add additional nsfr data.
	 * @param BSApiFileBackendStore $oInstance
	 * @param array &$aData
	 * @return bool
	 */
	public static function onBSApiExtJSStoreBaseBeforePostProcessData( $oInstance, &$aData ) {
		if ( !$oInstance instanceof BSApiFileBackendStore ) {
			return true;
		}
		foreach ( $aData as $iKey => $oDataSet ) {
			$oDataSet->file_nsfr_repo = '';
			$oDataSet->file_nsfr_repo_idx = 0;
			$aTitleParts = explode( ':', $oDataSet->file_name, 2 );
			if ( count( $aTitleParts ) === 1 ) {
				continue;
			}

			$oTitle = Title::newFromText( $oDataSet->file_name );
			if ( $oTitle->getNamespace() === 0 ) {
				continue;
			}

			if ( !in_array( $oTitle->getNamespace(), self::$aCheckedNs ) ) {
				self::$aCheckedNs[] = $oTitle->getNamespace();
				if ( !\MediaWiki\MediaWikiServices::getInstance()
					->getPermissionManager()
					->userCan( 'read', $oInstance->getUser(), $oTitle )
				) {
					self::$aNoReadNs[] = $oTitle->getNamespace();
				}
			}
			$oDataSet->file_display_text = $aTitleParts[1];
			$oDataSet->file_nsfr_repo = $oTitle->getNsText();
			$oDataSet->file_nsfr_repo_idx = $oTitle->getNamespace();
		}
		return true;
	}

	/**
	 * Hook handler for BSApiExtJSStoreBaseAfterFilterData.
	 * Filter nsfr files without read permissions and apply file_nefr_repo_idx
	 * filter.
	 * @param BSApiFileBackendStore $oInstance
	 * @param array &$aProcessedData
	 * @return bool
	 */
	public static function onBSApiExtJSStoreBaseAfterFilterData( $oInstance, &$aProcessedData ) {
		if ( !$oInstance instanceof BSApiFileBackendStore ) {
			return true;
		}

		$aValue = [];
		$aFilter = $oInstance->getParameter( 'filter' );
		foreach ( $aFilter as $oFilter ) {
			if ( $oFilter->type != 'file_nsfr_repo_idx' ) {
				continue;
			}
			$aValue = $oFilter->value;
		}
		$aNoReadNs = self::$aNoReadNs;

		$aProcessedData = array_filter( $aProcessedData, function ( $oE ) use( $aValue, $aNoReadNs ) {
			// Filter all namespaces without read permission
			// TODO: Probably add a warning to the result set
			// (permission-denied: for <ns>:)
			if ( in_array( $oE->file_nsfr_repo_idx, $aNoReadNs ) ) {
				return false;
			}
			return empty( $aValue )
				? true
				: in_array( $oE->file_nsfr_repo_idx, $aValue );
		} );

		return true;
	}

	/**
	 * Insert correct namespace path if needed
	 * @param string &$sPath
	 * @param string &$sFilename
	 * @param Title $oTitle
	 * @return bool Always true to keep hook running
	 */
	public static function onWebDAVUrlProviderGetUrl( &$sPath, &$sFilename, $oTitle ) {
		$aFileParts = explode( ':', $oTitle->getText(), 2 );
		if ( count( $aFileParts ) === 1 ) {
			// NS_MAIN --> prepend '(Pages)'
			array_unshift(
				$aFileParts,
				wfMessage( 'nsfilerepo-nsmain' )->plain()
			);
		}

		$sFilename = implode( '/',  $aFileParts );

		return true;
	}

	/**
	 * Replaces colon with underscore for Win
	 * compatibility on prefixed files
	 * @param object $oSender
	 * @param DOMElement $oImageElement
	 * @param string &$sAbsoluteFileSystemPath
	 * @param string &$sFileName
	 * @param string $sType
	 * @return bool
	 */
	public static function onBSUEModulePDFFindFiles( $oSender, $oImageElement,
		&$sAbsoluteFileSystemPath, &$sFileName, $sType ) {
		if ( $sType !== 'images' ) {
			return true;
		}
		$sFileName = str_replace( ':', '_', $sFileName );
		$oImageElement->setAttribute( 'src', 'images/' . urlencode( $sFileName ) );

		return true;
	}

	/**
	 * Integrates NSFileRepo into
	 * BlueSpiceMaintenance/maintenance/BSImportFiles.php maintenance script
	 *
	 * @param BSImportFiles $oMaintenanceScript
	 * @param Title &$oTargetTitle
	 * @param FileRepo &$oRepo
	 * @param array $aParts
	 * @param string $sRoot
	 * @return bool
	 */
	public static function onBSImportFilesMakeTitle( $oMaintenanceScript, &$oTargetTitle,
		&$oRepo, $aParts, $sRoot ) {
		$iNsId = RequestContext::getMain()->getLanguage()->getNsIndex( $sRoot );
		if ( $iNsId === false || $iNsId === NS_MAIN ) {
			return true;
		}
		$sQuotedRoot = preg_quote( $sRoot );
		$sUnsuffixedFileName = preg_replace( "#^{$sQuotedRoot}_#", '', $oTargetTitle->getDBkey() );
		$oTargetTitle = Title::makeTitle( NS_FILE, $sRoot . ':' . $sUnsuffixedFileName );
		return true;
	}

	/**
	 * Integrates NSFileRepo into
	 * BlueSpiceMaintenance/maintenance/BSExportFiles.php maintenance script
	 *
	 * @param BSMaintenance $oMaintenanceScript
	 * @param File &$oFile
	 * @param string &$sDestPath
	 * @param string &$sSourcePath
	 * @return bool
	 */
	public static function onBSExportFilesBeforeSave( $oMaintenanceScript, &$oFile,
		&$sDestPath, &$sSourcePath ) {
		$sDestPath = str_replace( ':', '/',  $sDestPath );
		wfMkdirParents( dirname( $sDestPath ) );
		return true;
	}

	/**
	 * @param string &$filename
	 * @param string $url
	 * @return bool
	 */
	public static function onWebDAVGetFilenameFromUrl( &$filename, $url ) {
		$urlBits = explode( '/', $url );
		$file = array_pop( $urlBits );
		$namespace = array_pop( $urlBits );

		if ( $namespace === wfMessage( 'nsfilerepo-nsmain' )->plain() ) {
			$filename = $file;
		} else {
			$filename = $namespace . ':' . $file;
		}

		return true;
	}
}
