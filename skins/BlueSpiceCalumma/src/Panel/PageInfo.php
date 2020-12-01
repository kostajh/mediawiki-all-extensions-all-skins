<?php

namespace BlueSpice\Calumma\Panel;

use BlueSpice\Calumma\PanelFactory;
use BlueSpice\SkinData;

class PageInfo extends PanelContainer {

	/**
	 *
	 * @return string
	 */
	public function getHtmlId() {
		return 'bs-pageinfo-panel';
	}

	/**
	 *
	 * @return \Message
	 */
	public function getTitleMessage() {
		return wfMessage( 'bs-sitetools-pageinfo-title' );
	}

	/**
	 *
	 * @return \BlueSpice\Calumma\IPanel[]
	 */
	protected function makePanels() {
		$panelFactory = new PanelFactory(
			$this->skintemplate->get( SkinData::PAGE_INFOS_PANEL ),
			$this->skintemplate
		);
		return $panelFactory->makePanels();
	}

	/**
	 *
	 * @param \IContextSource $context
	 * @return bool
	 */
	public function shouldRender( $context ) {
		return !empty( $this->skintemplate->get( SkinData::PAGE_INFOS_PANEL ) );
	}

}
