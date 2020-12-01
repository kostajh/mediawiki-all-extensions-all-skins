<?php

namespace BlueSpice\InterWikiLinks\Hook\BeforePageDisplay;

use MediaWiki\MediaWikiServices;

class AddResources extends \BlueSpice\Hook\BeforePageDisplay {

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$interwikiLookup = MediaWikiServices::getInstance()->getInterwikiLookup();
		$interwikiLinks = [];
		foreach ( $interwikiLookup->getAllPrefixes() as $entry ) {
			$interwikiLinks[] = $entry['iw_prefix'];
		}
		$this->out->addJsConfigVars( 'BSInterWikiPrefixes', $interwikiLinks );

		$action = $this->out->getRequest()->getVal( 'action', 'view' );
		if ( !in_array( $action, [ 'edit', 'submit' ] ) ) {
			return true;
		}
		$this->out->addModules( 'bluespice.insertLink.interWikiLinks' );

		return true;
	}

}
