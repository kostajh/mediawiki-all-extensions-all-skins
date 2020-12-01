<?php

namespace BlueSpice\Bookshelf\Panel;

use BlueSpice\Calumma\IActiveStateProvider;
use BlueSpice\Calumma\Panel\PanelContainer;
use Message;
use SkinTemplate;

class BookNav extends PanelContainer implements IActiveStateProvider {

	/**
	 * @param SkinTemplate $skintemplate
	 */
	public function __construct( $skintemplate ) {
		parent::__construct( $skintemplate );
	}

	/**
	 *
	 * @return array
	 */
	protected function makePanels() {
		return [
			'general-books' => new GeneralBooksFlyout( $this->skintemplate ),
			'chapter-navigation' => new ChapterNavigation( $this->skintemplate )
		];
	}

	/**
	 *
	 * @return Message
	 */
	public function getTitleMessage() {
		return wfMessage( 'bs-bookshelf-specialpage-title' );
	}

	/**
	 *
	 * @return string
	 */
	public function getHtmlId() {
		return 'bs-nav-section-bs-bookshelfui';
	}

	/**
	 *
	 * @return bool
	 */
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
