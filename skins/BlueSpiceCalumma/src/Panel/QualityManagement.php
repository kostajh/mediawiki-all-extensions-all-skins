<?php

namespace BlueSpice\Calumma\Panel;

use BlueSpice\Calumma\IActiveStateProvider;
use BlueSpice\Calumma\PanelFactory;
use BlueSpice\SkinData;

class QualityManagement extends PanelContainer implements IActiveStateProvider {

	/**
	 *
	 * @return bool
	 */
	public function isEmpty() {
		return false;
	}

	/**
	 *
	 * @return string
	 */
	public function getHtmlId() {
		return 'bs-qualitymanagement-panel';
	}

	/**
	 *
	 * @return \Message
	 */
	public function getTitleMessage() {
		return wfMessage( 'bs-sitetools-qualitymanagement-title' );
	}

	/**
	 *
	 * @return \BlueSpice\Calumma\IPanel[]
	 */
	protected function makePanels() {
		$defaultPanelDefs = [];

		$panelDefs = $this->skintemplate->get( SkinData::PAGE_DOCUMENTS_PANEL );

		$combinedPanelDefs = array_merge_recursive(
			$defaultPanelDefs,
			$panelDefs
		);

		$panelFactory = new PanelFactory(
			$combinedPanelDefs,
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
		$shouldRender = parent::shouldRender( $context );
		if ( $shouldRender === false ) {
			return false;
		}

		$action = $context->getRequest()->getText( 'action', 'view' );
		if ( $action !== 'view' ) {
			return false;
		}

		// Only render when at least one of the registered panels
		// actually renders.
		foreach ( $this->panels as $panel ) {
			if ( $panel->shouldRender( $context ) === true ) {
				return true;
			}
		}
		return false;
	}

	public function isActive() {
		$panels = $this->makePanels();
		foreach ( $panels as $panel ) {
			if ( ( $panel instanceof IActiveStateProvider ) ) {
				if ( $panel->isActive() ) {
					return true;
				}
			}
		}
		return false;
	}

}
