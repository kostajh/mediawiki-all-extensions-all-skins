<?php

namespace BlueSpice\Calumma\Panel;

use BlueSpice\Calumma\PanelFactory;
use BlueSpice\SkinData;

class PageTools extends PanelContainer {

	/**
	 *
	 * @return string
	 */
	public function getHtmlId() {
		return 'bs-pagetools-panel';
	}

	/**
	 *
	 * @return \Message
	 */
	public function getTitleMessage() {
		return wfMessage( 'bs-sitetools-pagetools-title' );
	}

	/**
	 *
	 * @return \BlueSpice\Calumma\IPanel[]
	 */
	protected function makePanels() {
		$defaultPanelDefs = [
			// Disabled for BETA release, as long a this structure is a mess
			/*'contentactions' => [
				'position' => 20,
				'callback' => function( $sktemplate ) {
					return new ContentActions( $sktemplate );
				}
			],*/
			'edit' => [
				'position' => 20,
				'callback' => function ( $sktemplate ) {
					return new Edit( $sktemplate );
				}
			],
			'assoc-links' => [
				'position' => 30,
				'callback' => function ( $sktemplate ) {
					return new AssocLinks( $sktemplate );
				}
			],
			'export' => [
				'position' => 80,
				'callback' => function ( $sktemplate ) {
					return new Export( $sktemplate );
				}
			],
			'toolbox' => [
				'position' => 100,
				'callback' => function ( $sktemplate ) {
					return new Toolbox( $sktemplate );
				}
			],
			'views' => [
				'position' => 120,
				'callback' => function ( $sktemplate ) {
					return new Views( $sktemplate );
				}
			]
		];
		$panelDefs = $this->skintemplate->get( SkinData::PAGE_TOOLS_PANEL );

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

}
