<?php

namespace BlueSpice\Bookshelf\Panel;

use BlueSpice\Bookshelf\ChapterPager;
use BlueSpice\Calumma\IActiveStateProvider;
use BlueSpice\Calumma\Panel\BasePanel;
use Message;
use PageHierarchyProvider;

class ChapterNavigationPager extends BasePanel implements IActiveStateProvider {

	protected $bookTitle;
	protected $previousTitle = null;
	protected $nextTitle = null;

	/**
	 *
	 * @return string
	 */
	public function getBody() {
		try {
			$this->phProvider = PageHierarchyProvider::getInstanceForArticle(
				$this->skintemplate->getSkin()->getTitle()->getPrefixedText()
			);
		} catch ( \Exception $ex ) {
			return '';
		}

		return $this->getHtml();
	}

	/**
	 *
	 * @return string
	 */
	public function getHtml() {
		$chapterPager = new ChapterPager( $this->skintemplate );

		return $chapterPager->getDefaultPagerHtml();
	}

	/**
	 *
	 * @return Message
	 */
	public function getTitleMessage() {
		return new \RawMessage( '' );
	}

	/**
	 *
	 * @return string
	 */
	public function getHtmlId() {
		return 'bs-bookshelfui-chapter-chapterpager';
	}

	/**
	 *
	 * @return bool
	 */
	public function isActive() {
		return false;
	}
}
