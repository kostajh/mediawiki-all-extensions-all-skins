<?php

namespace BlueSpice\Bookshelf;

use SkinTemplate;

class SidebarBooklist {

	protected $buffer = [];

	/**
	 *
	 * @param SkinTemplate $template
	 * @param \DOMElement|null $domElement
	 * @param int $indent
	 */
	public function __construct( $template, \DOMElement $domElement = null, $indent = 0 ) {
		$this->skinTemplate = $template;
	}

	/**
	 *
	 * @return \BaseTemplate
	 */
	public function getSkinTemplate() {
		return $this->skinTemplate;
	}

	/**
	 *
	 * @return string
	 */
	public function getHtml() {
		$this->fetchAllBooks();

		$cache = wfGetMainCache();
		$cache->getWithSetCallback(
			$cache->makeKey( __CLASS__, 'fetchBookHierarchies' ),
			3600,
			function () {
				$this->fetchBookHierarchies();
			}
		);

		$this->fetchBookHierarchies();
		$this->renderList();
		$this->wrapInPanel();

		return $this->flushBuffer();
	}

	/**
	 *
	 * @return string
	 */
	protected function flushBuffer() {
		$html = implode( "\n", $this->buffer );
		$this->buffer = [];
		return $html;
	}

	/**
	 *
	 * @var \Title[]
	 */
	protected $bookTitles = [];

	protected function fetchAllBooks() {
		// TODO: Use API or better DataStore in BS3
		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select( 'page', '*', [ 'page_namespace' => NS_BOOK ] );
		$this->bookTitles = \TitleArray::newFromResult( $res );
	}

	protected function renderList() {
		foreach ( $this->hierarchies as $bookTitle => $hierarchy ) {
			$extendedTOC = $hierarchy->getExtendedTOCArray();
			$firstChapterTitle = $this->getFirstChapterTitle( $extendedTOC );
			$bookMeta = $hierarchy->getBookMeta();

			$bookTitleText = \Title::newFromText( $bookTitle )->getText();
			if ( isset( $bookMeta['title'] ) ) {
				$bookTitleText = $bookMeta['title'];
			}

			$attribs = [];
			if ( $this->currentBook === $bookTitle ) {
				$attribs[ 'class' ] = 'active';
			}

			$this->buffer[] = \Html::rawElement(
				'li',
				[],
				\Linker::link( $firstChapterTitle, $bookTitleText, $attribs )
			);
		}
	}

	protected function wrapInPanel() {
		$list = $this->flushBuffer();

		$this->buffer[] = '<div class="bs-bookshelfui-book-list">';
		$this->buffer[] = \Html::element(
			'h5',
			[
				'class' => 'bs-bookshelfui-book-list-heading'
			],
			wfMessage( 'bs-bookshelf-grouping-template-type-ns_book' )->plain()
		);
		$this->buffer[] = \Html::rawElement(
			'div',
			[
				'class' => 'bs-bookshelfui-book-list-body bs-nav-links'
			],
			'<ul>' . $list . '</ul>'
		);
		$this->buffer[] = '</div>';
	}

	/**
	 *
	 * @var \PageHierarchyProvider[]
	 */
	protected $hierarchies = [];

	protected $currentBook = '';

	protected function fetchBookHierarchies() {
		$currentPageTitleText = $this->getSkinTemplate()->getSkin()->getTitle()
			->getPrefixedText();

		foreach ( $this->bookTitles as $title ) {
			try {
				$pageHierarchieProvider = \PageHierarchyProvider::getInstanceFor(
					$title->getPrefixedText()
				);
				$this->hierarchies[$title->getPrefixedText()] = $pageHierarchieProvider;

				if ( empty( $this->currentBook ) ) {
					$currentNumber = $pageHierarchieProvider->getNumberFor(
						$currentPageTitleText, true
					);

					if ( !empty( $currentNumber ) ) {
						$this->currentBook = $title->getPrefixedText();
					}
				}
			} catch ( \Exception $ex ) {
			}
		}
	}

	/**
	 *
	 * @param array $extendedTOC
	 * @return \Title|null
	 */
	protected function getFirstChapterTitle( $extendedTOC ) {
		foreach ( $extendedTOC as $entry ) {
			if ( $entry['bookshelf']['type'] === 'wikipage' ) {
				$nsId = $entry['bookshelf']['page_namespace'];
				$titleText = $entry['bookshelf']['page_title'];
				$title = \Title::makeTitle( $nsId, $titleText );

				return $title;
			}
		}
		return null;
	}

}
