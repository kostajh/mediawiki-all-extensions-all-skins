<?php

namespace BlueSpice\ExtendedStatistics\Tag;

use BlueSpice\Tag\Handler;
use BsPageContentProvider;
use RequestContext;

class ProgressHandler extends Handler {

	/**
	 *
	 * @return string
	 */
	public function handle() {
		$this->parser->getOutput()->setProperty( 'bs-tag-statistics-progress', 1 );
		$iBaseCount = $this->processedArgs[Progress::ATTR_BASE_COUNT];
		$sBaseItem  = $this->processedArgs[Progress::ATTR_BASE_ITEM];
		$sFraction  = $this->processedArgs[Progress::ATTR_PROGRESS_ITEM];
		$iWidth     = $this->processedArgs[Progress::ATTR_WIDTH];

		// no Article when in cli mode
		if ( !RequestContext::getMain()->getTitle() ) {
			return '';
		}

		$sText = BsPageContentProvider::getInstance()->getContentFromTitle(
			RequestContext::getMain()->getTitle()
		);

		// substract 1 because one item is in the progressitem attribute
		$iFraction = substr_count( $sText, $sFraction ) - 1;

		if ( $sBaseItem ) {
			$iBase = substr_count( $sText, $sBaseItem ) - 1;
		} else {
			$iBase = $iBaseCount;
		}

		$fPercent = $iFraction / $iBase;

		$iWidthGreen = floor( $iWidth * $fPercent );
		$iWidthRemain = $iWidth - $iWidthGreen;

		$sPercent = sprintf( "%0.1f", $fPercent * 100 );

		$sOut = '<div style="background-color:green;border:2px solid #DDDDDD;width:'
			. $iWidthGreen
			. 'px;height:25px;float:left;color:#DDDDDD;text-align:center;border-right:'
			. '0px;text-weight:bold;vertical-align:middle;">'
			. $sPercent
			. '%</div>';
		$sOut .= '<div style="border:2px solid #DDDDDD;border-left:0px;width:'
			. $iWidthRemain
			. 'px;height:25px;float:left;"></div>';

		return $sOut;
	}
}
