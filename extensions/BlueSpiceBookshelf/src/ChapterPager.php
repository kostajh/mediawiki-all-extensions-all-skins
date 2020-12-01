<?php

namespace BlueSpice\Bookshelf;

use Html;
use PageHierarchyProvider;
use SkinTemplate;
use Title;

class ChapterPager {
	protected $bookTitle;
	protected $previousTitle = null;
	protected $currentTitle = null;
	protected $nextTitle = null;

	/**
	 *
	 * @param SkinTemplate $skinTemplate
	 */
	public function __construct( $skinTemplate ) {
		$this->skintemplate = $skinTemplate;
		$this->makePagerData();
	}

	private function makePagerData() {
		try {
			$this->phProvider = PageHierarchyProvider::getInstanceForArticle(
				$this->skintemplate->getSkin()->getTitle()->getPrefixedText()
			);
		} catch ( \Exception $ex ) {
			return '';
		}

		$tree = $this->phProvider->getExtendedTOCJSON();

		$this->bookTitle = $tree->bookshelf->page_title;
		$bookMeta = $this->phProvider->getBookMeta();
		if ( isset( $bookMeta['title'] ) ) {
			$this->bookTitle = $bookMeta['title'];
		}

		$title = $this->skintemplate->getSkin()->getTitle();

		$flatArray = $this->flatArray( (array)$tree->children );
		for ( $i = 0; $i < count( $flatArray ); $i++ ) {
			if ( $title->getArticleID() === $flatArray[$i]['articleId'] ) {
				$this->currentTitle = $flatArray[$i];
				if ( $i > 0 ) {
					$this->previousTitle = $flatArray[$i - 1];
				}
				if ( ( $i + 1 ) < count( $flatArray ) ) {
					$this->nextTitle = $flatArray[$i + 1];
				}
			}
		}
	}

	/**
	 *
	 * @param array $data
	 * @return array
	 */
	private function flatArray( $data ) {
		$items = [];
		for ( $i = 0; $i < count( $data ); $i++ ) {
			$item = (array)$data[$i];

			if ( array_key_exists( 'children', $item ) ) {
				$children = $this->flatArray( (array)$item['children'] );
				unset( $item['children'] );
				$items[] = array_merge( $items, $item );
				$items = array_merge( $items, $children );
			} else {
				$items[] = $item;
			}
		}
		return $items;
	}

	/**
	 *
	 * @return string
	 */
	public function  getBookTitle() {
		return $this->bookTitle;
	}

	/**
	 *
	 * @return array
	 */
	public function  getNextPageData() {
		return $this->nextTitle;
	}

	/**
	 *
	 * @return array
	 */
	public function getCurrentPageData() {
		return $this->currentTitle;
	}

	/**
	 *
	 * @return array
	 */
	public function getPreviousPageData() {
		return $this->previousTitle;
	}

	/**
	 *
	 * @return string
	 */
	public function getDefaultPagerHtml() {
		$html = Html::openElement( 'div', [ 'class' => 'bookshelfui-chapterpager-heading' ] );
		$html .= Html::element(
			'h4',
			[
				'class' => 'book-title'
			],
			$this->bookTitle
		);
		$html .= Html::closeElement( 'div' );

		$html .= Html::openElement( 'div', [ 'class' => 'bookshelfui-chapterpager' ] );

		$previousTitle = Title::newFromId( $this->previousTitle['articleId'] );
		if ( $previousTitle !== null ) {
			$class = '';
			$href = $previousTitle->getFullURL();
			$title = $this->previousTitle['text'];
		} else {
			$class = ' disabled';
			$href = '';
			$title = '';
		}
		$html .= Html::openElement(
				'a',
				[
					'class' => 'prev-chapter' . $class,
					'href' => $href,
					'title' => $title
				]
			);

		$html .= Html::element(
				'span',
				[],
				wfMessage( 'bs-bookshelfui-chapterpager-previous' )->plain()
			);
		$html .= Html::closeElement( 'a' );

		$nextTitle = Title::newFromId( $this->nextTitle['articleId'] );
		if ( $this->nextTitle !== null ) {
			$class = '';
			$href = $nextTitle->getFullURL();
			$title = $this->nextTitle['text'];
		} else {
			$href = '';
			$class = ' disabled';
			$title = '';
		}
		$html .= Html::openElement(
				'a',
				[
					'class' => 'next-chapter' . $class,
					'href' => $href,
					'title' => $title
				]
			);

		$html .= Html::element(
				'span',
				[],
				wfMessage( 'bs-bookshelfui-chapterpager-next' )->plain()
			);
		$html .= Html::closeElement( 'a' );

		$html .= Html::closeElement( 'div' );

		return $html;
	}

}
