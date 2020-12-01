<?php

class ParentPage {

	public static function addPropertyValue( SMW\SemanticData $aSemanticData, WikiPage $aWikiPage, SMW\DIProperty $aProperty ) {
		$oTitle = $aWikiPage->getTitle();
		$fTitle = $oTitle->getFullText();
		$sTitle = $oTitle->getSubpageText();
		$pTitle = trim( substr( $fTitle, 0, strlen( $fTitle ) - strlen( $sTitle ) ), "/" );
		$oPTitle = Title::newFromText( $pTitle );
		if ( $oPTitle !== null ) {
			$aSemanticData->addPropertyObjectValue(
			  $aProperty, SMW\DIWikiPage::newFromTitle( $oPTitle )
			);
		}
	}

}
