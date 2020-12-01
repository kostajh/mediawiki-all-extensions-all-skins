<?php

namespace BlueSpice\Bookshelf\Panel;

use BlueSpice\Bookshelf\SidebarTreeNavigation;
use BlueSpice\Calumma\IActiveStateProvider;
use BlueSpice\Calumma\Panel\BasePanel;
use InvalidArgumentException;
use Message;
use PageHierarchyProvider;

class ChapterNavigation extends BasePanel implements IActiveStateProvider {
	/** @var PageHierarchyProvider|null */
	private $php;

	/**
	 * @inheritDoc
	 */
	public function __construct( $skintemplate ) {
		parent::__construct( $skintemplate );

		try {
			$this->php = PageHierarchyProvider::getInstanceForArticle(
				$this->skintemplate->getSkin()->getTitle()->getPrefixedText()
			);
		} catch ( InvalidArgumentException $ex ) {
			$this->php = null;
		}
	}

	/**
	 *
	 * @return string
	 */
	public function getBody() {
		$navigation = new SidebarTreeNavigation( $this->skintemplate );
		return $navigation->getHtml();
	}

	/**
	 *
	 * @return Message
	 */
	public function getTitleMessage() {
		if ( $this->php instanceof PageHierarchyProvider ) {
			return new \RawMessage( $this->php->getExtendedTOCJSON()->articleDisplayTitle );
		}
		return new \RawMessage( '' );
	}

	/**
	 *
	 * @return string
	 */
	public function getHtmlId() {
		return 'bs-bookshelfui-chapter-nav';
	}

	/**
	 *
	 * @return bool
	 */
	public function isActive() {
		return $this->php !== null;
	}

	/**
	 * @inheritDoc
	 */
	public function shouldRender( $context ) {
		return $this->isActive();
	}
}
