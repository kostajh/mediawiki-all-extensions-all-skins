<?php

namespace BlueSpice\Calumma\Panel;

class SiteNavigation extends PanelContainer {

	/**
	 *
	 * @return string
	 */
	public function getHtmlId() {
		return 'bs-sitenav-navigation';
	}

	/**
	 *
	 * @return \Message
	 */
	public function getTitleMessage() {
		return wfMessage( 'bs-sitenav-navigation-title' );
	}

	/**
	 *
	 * @return \BlueSpice\Calumma\IPanel[]
	 */
	protected function makePanels() {
		return [
			'mobileusercontainer' => new MobileUserContainer( $this->skintemplate ),
			'mediawikisidebar' => new MediaWikiSidebar( $this->skintemplate )
		];
	}

}
