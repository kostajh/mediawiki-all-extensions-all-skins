<?php

namespace BlueSpice\Calumma\Components;

use BlueSpice\Calumma\IActiveStateProvider;
use BlueSpice\Calumma\PanelFactory;
use BlueSpice\Calumma\Structure\TabPanelStructure;
use BlueSpice\SkinData;
use Skins\Chameleon\IdRegistry;

class SiteNavTabs extends TabPanelStructure {

	protected $activeState = false;

	/**
	 *
	 * @return array
	 */
	protected function getSubcomponentsData() {
		$activeTabId = $this->getActiveTabId();

		$panelFactory = new PanelFactory(
			$this->getSkinTemplate()->get( SkinData::SITE_NAV ),
			$this->getSkinTemplate()
		);

		$panels = $panelFactory->makePanels();

		foreach ( $panels as $panel ) {
			if ( $panel instanceof IActiveStateProvider ) {
				if ( $panel->isActive() ) {
					$activeTabId = $panel->getHtmlId();
					$this->activeState = true;
				}
			}
		}

		$subComponentsData = [];
		foreach ( $panels as $panel ) {
			if ( !$panel->shouldRender( $this->getSkin()->getContext() ) ) {
				continue;
			}

			$tabId = $panel->getHtmlId();
			$subComponentsData[] = [
				'id' => $tabId,
				'active' => $tabId === $activeTabId,
				'title' => $panel->getTitleMessage(),
				'body' => $panel->getBody(),
				'class' => $panel->getContainerClasses()
			];
		}

		return $subComponentsData;
	}

	/**
	 * The HTML ID for thie component
	 * @return string
	 */
	public function getHtmlId() {
		if ( $this->htmlId === null ) {
			$this->htmlId = IdRegistry::getRegistry()->getId( 'bs-sitenavtabs' );
		}
		return $this->htmlId;
	}

	/**
	 * Is this element active
	 * @return bool
	 */
	public function isActive() {
		return $this->activeState;
	}
}
