<?php
/**
 * Renders the Statistics special page.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.com>
 *
 * @package    BlueSpice_Extensions
 * @subpackage Statistics
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

use BlueSpice\Special\ExtJSBase;
use MediaWiki\Shell\Shell;

/**
 * Statistics special page that renders the creation dialogue of statistics
 * @package BlueSpice_Extensions
 * @subpackage Statistics
 */
class SpecialExtendedStatistics extends ExtJSBase {

	/**
	 * Constructor of SpecialExtendedStatistics
	 */
	public function __construct() {
		parent::__construct( 'ExtendedStatistics', 'statistic-viewspecialpage', true );
	}

	/**
	 * Renders special page output.
	 * @param string $par Name of the article, who's review should be edited, or user
	 * whos review should be displayed.
	 * @return bool Allow other hooked methods to be executed. always true.
	 * @throws PermissionsError
	 */
	public function execute( $par ) {
		$this->checkPermissions();

		if ( !empty( $par ) ) {
			global $wgRequest;
			$sData = $wgRequest->getVal( 'data', '' );
			$sData = $this->extractFromDataProtocol( $sData );
			if ( !empty( $sData ) ) {
				switch ( $par ) {
					case 'export-png':
						return $this->exportPNG( $sData );
					case 'export-svg':
						return $this->exportSVG( $sData );
				}
			}
		}

		$this->getOutput()->setPageTitle( wfMessage( 'extendedstatistics' )->plain() );
		parent::execute( $par );

		return true;
	}

	private function exportPNG( $sData ) {
		$this->getOutput()->disable();

		global $wgRequest, $wgSVGConverter, $wgSVGConverters, $wgSVGConverterPath;
		if ( $wgSVGConverter == false || !isset( $wgSVGConverters[$wgSVGConverter] ) ) {
			echo wfMessage( 'bs-statistics-err-converter' )->plain();
			return false;
		}

		$sFileName = wfTimestampNow();
		$sFileExt = '.svg';

		$oStatus = BsFileSystemHelper::saveToCacheDirectory(
			$sFileName . $sFileExt,
			$sData,
			'Statistics'
		);
		if ( !$oStatus->isGood() ) {
			echo $oStatus->getMessage();
			return false;
		}

		$sCacheDir = $oStatus->getValue();

		$cmd = str_replace(
			[ '$path/', '$width', '$height', '$input', '$output' ],
			[ $wgSVGConverterPath ? Shell::escape( "$wgSVGConverterPath/" ) : "",
				intval( $wgRequest->getVal( 'width', 600 ) ),
				intval( $wgRequest->getVal( 'height', 400 ) ),
				Shell::escape( $sCacheDir . '/' . $sFileName . $sFileExt ),
				Shell::escape( $sCacheDir . '/' . $sFileName . '.png' )
			],
			$wgSVGConverters[$wgSVGConverter]
		) . " 2>&1";

		$err = wfShellExec( $cmd );
		unlink( $sCacheDir . '/' . $sFileName . $sFileExt );

		$sFileExt = '.png';
		if ( !file_exists( $sCacheDir . '/' . $sFileName . $sFileExt ) ) {
			echo $err;
			return false;
		}

		$this->getRequest()->response()->header( "Content-Type:image/png" );
		$this->getRequest()->response()->header(
			"Content-Disposition:attachment; filename={$sFileName}{$sFileExt}"
		);
		readfile( $sCacheDir . '/' . $sFileName . $sFileExt );
		unlink( $sCacheDir . '/' . $sFileName . $sFileExt );
		return true;
	}

	private function exportSVG( $sData ) {
		$this->getOutput()->disable();

		$sName = wfTimestampNow();
		$this->getRequest()->response()->header( "Content-Disposition:attachment; filename=$sName.svg" );
		echo $sData;

		return true;
	}

	/**
	 * In ExtJS 6 "Ext.chart.CartesianChart" has no 'save' method anymore. The
	 * new 'download' method sends the data in form of an url encoded
	 * data-protocol string
	 * E.g.: "data:image/svg+xml;utf8,%3C%3Fxml%20version%3D%221.0%22%20sta..."
	 * @param string $sData
	 * @return string
	 */
	protected function extractFromDataProtocol( $sData ) {
		$dataProtocolPrefix = "data:image/svg+xml;utf8,";
		$escapedDataProtocolPrefix = preg_quote( $dataProtocolPrefix );
		$urlEncodedSvg = preg_replace( "#^$escapedDataProtocolPrefix#", '', $sData );
		$svg = urldecode( $urlEncodedSvg );

		return $svg;
	}

	/**
	 * @return string ID of the HTML element being added
	 */
	protected function getId() {
		return 'bs-statistics-panel';
	}

	/**
	 * @return array
	 */
	protected function getModules() {
		return [ 'ext.bluespice.statistics' ];
	}

	/**
	 *
	 * @return array
	 */
	protected function getJSVars() {
		$bAllowPNGExport = false;
		// Temporarely disable PNG export, ticket #10472
		/*global $wgSVGConverter, $wgSVGConverters;
		if( $wgSVGConverter != false && isset($wgSVGConverters[$wgSVGConverter]) ) {
			$bAllowPNGExport = true;
		}*/
		return [
			'BsExtendedStatisticsAllowPNGExport' => $bAllowPNGExport
		];
	}
}
