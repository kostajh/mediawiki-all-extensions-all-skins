<?php

namespace BlueSpice\Calumma\Panel;

use BlueSpice\SkinData;
use Skins\Chameleon\IdRegistry;

class Edit extends StandardSkinDataLinkList {

	/**
	 *
	 * @return \Message
	 */
	public function getTitleMessage() {
		return new \Message( 'bs-sitetools-manage-page' );
	}

	/**
	 *
	 * @param string $linkKey
	 * @return bool
	 */
	protected function skipLink( $linkKey ) {
		$blacklist = $this->skintemplate->data[SkinData::EDIT_MENU_BLACKLIST];
		return in_array( $linkKey, $blacklist );
	}

	/**
	 *
	 * @return array
	 */
	protected function getStandardSkinDataLinkListDefinition() {
		$contentNavigation = $this->skintemplate->get( 'content_navigation' );
		$editmenu = $this->skintemplate->get( SkinData::EDIT_MENU );

		$combinedDefs = $contentNavigation['actions'] + $editmenu;
		return $combinedDefs;
	}

	/**
	 *
	 * @var string
	 */
	protected $htmlId = null;

	/**
	 * The HTML ID for thie component
	 * @return string
	 */
	public function getHtmlId() {
		if ( $this->htmlId === null ) {
			$this->htmlId = IdRegistry::getRegistry()->getId( 'bs-manage-page-panel' );
		}
		return $this->htmlId;
	}
}
