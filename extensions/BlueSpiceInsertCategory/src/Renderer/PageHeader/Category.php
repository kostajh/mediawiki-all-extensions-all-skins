<?php

namespace BlueSpice\InsertCategory\Renderer\PageHeader;

use BlueSpice\Calumma\Renderer\PageHeader\Category as CategoryBase;
use BlueSpice\Renderer\Params;
use Config;
use Html;
use IContextSource;
use MediaWiki\Linker\LinkRenderer;
use Title;

class Category extends CategoryBase {

	/**
	 * Constructor
	 * @param Config $config
	 * @param Params $params
	 * @param LinkRenderer|null $linkRenderer
	 * @param IContextSource|null $context
	 * @param string $name | ''
	 */
	protected function __construct( Config $config, Params $params,
		LinkRenderer $linkRenderer = null, IContextSource $context = null,
		$name = '' ) {
		parent::__construct( $config, $params, $linkRenderer, $context, $name );
	}

	/**
	 *
	 * @return string
	 */
	public function render() {
		$html = '';

		$title = $this->getContext()->getTitle();

		if ( !$title || $title->isSpecialPage() ) {
			return $html;
		}

		$html .= parent::makeCategorySectionOpener( $title );
		$html .= $this->makeCategoryLinks( $title );

		return $html;
	}

	/**
	 *
	 * @param Title $title
	 * @return string
	 */
	protected function makeCategoryLinks( Title $title ) {
		$html = '';

		$categoryLinks = [];
		foreach ( $this->args[parent::PARAM_CATEGORY_NAMES] as $categoryName ) {
			$title = Title::makeTitle( NS_CATEGORY, $categoryName );
			if ( !$title ) {
				continue;
			}
			$categoryLinks[] = $this->linkRenderer->makeLink(
				$title,
				new \HtmlArmor( $title->getText() ),
				[ 'class' => 'pill' ]
			);
		}

		$html .= Html::openElement(
				'div',
				[
					'class' => 'bs-category-container-categories'
				]
			);

		$html .= implode( '', $categoryLinks );

		if ( empty( $categoryLinks ) ) {
			$html .= Html::element(
					'span',
					[
						'class' => 'bs-category-no-categories'
					],
					$this->msg( 'bs-calumma-category-no-categories' )->plain() . ' '
				);
		}

		$html .= $this->makeChangeLink( $this->getContext()->getTitle() );

		$html .= Html::closeElement( 'div' );

		return $html;
	}

	/**
	 *
	 * @param Title $title
	 * @return string
	 */
	private function makeChangeLink( Title $title ) {
		$html = Html::openElement(
				'div',
				[
					'class' => 'bs-insertcategory-category-container-editlink visible-xs visible-sm visible-md'
				]
			);

		if ( \MediaWiki\MediaWikiServices::getInstance()->getPermissionManager()
			->userCan( 'edit', $this->getContext()->getUser(), $title )
		) {
			$html .= Html::element(
					'a',
					[
						'class' => 'bs-category-add-category',
						'href' => '#'
					],
					$this->msg( 'bs-insertcategory-category-editor-explicit-categories-edit-label' )->plain()
				);
		}

		$html .= Html::closeElement( 'div' );

		return $html;
	}

	/**
	 *
	 * @param Title $title
	 * @return string
	 */
	protected function makeIcon( $title ) {
		if ( \MediaWiki\MediaWikiServices::getInstance()->getPermissionManager()
			->userCan( 'edit', $this->getContext()->getUser(), $title )
		) {
			$html = $this->makeLinkedIcon();
		} else {
			$html = parent::makeIcon( $title );
		}
		return $html;
	}

	/**
	 *
	 * @return string
	 */
	protected function makeLinkedIcon() {
		$html = Html::openElement(
				'a',
				[
					'class' => 'bs-category-add-category',
					'title' => $this->msg( 'bs-insertcategory-page-header-categories-edit-tooltip' )->plain(),
					'aria-label' => $this->msg( 'bs-insertcategory-page-header-categories-edit-tooltip' )->plain()
				]
			);

		$html .= Html::element( 'i' );

		$html .= Html::closeElement( 'a' );

		return $html;
	}
}
