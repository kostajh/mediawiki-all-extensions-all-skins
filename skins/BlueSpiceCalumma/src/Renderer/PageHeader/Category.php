<?php

namespace BlueSpice\Calumma\Renderer\PageHeader;

use BlueSpice\Calumma\Renderer\PageHeader;
use BlueSpice\Renderer\Params;
use Config;
use Html;
use HtmlArmor;
use IContextSource;
use MediaWiki\Linker\LinkRenderer;
use QuickTemplate;
use Title;

class Category extends PageHeader {
	public const PARAM_CATEGORY_NAMES = 'categories';

	/**
	 * Constructor
	 * @param Config $config
	 * @param Params $params
	 * @param LinkRenderer|null $linkRenderer
	 * @param IContextSource|null $context
	 * @param string $name | ''
	 * @param QuickTemplate|null $skinTemplate
	 */
	protected function __construct( Config $config, Params $params,
		LinkRenderer $linkRenderer = null, IContextSource $context = null,
		$name = '', QuickTemplate $skinTemplate = null ) {
		parent::__construct( $config, $params, $linkRenderer, $context, $name, $skinTemplate );

		$this->args[static::PARAM_CATEGORY_NAMES] = $params->get(
			static::PARAM_CATEGORY_NAMES,
			[]
		);
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

		$html .= $this->makeCategorySectionOpener( $title );
		$html .= $this->makeCategoryLinks( $title );

		return $html;
	}

	/**
	 *
	 * @param Title $title
	 * @return string
	 */
	protected function makeCategorySectionOpener( Title $title ) {
		$html = '';

		$html .= Html::openElement(
				'div',
				[
					'id' => 'bs-category-container-add-category',
					'class' => 'bs-category-container-add-category'
				]
			);

		$html .= $this->makeIcon( $title );

		$html .= Html::closeElement( 'div' );

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
		foreach ( $this->args[static::PARAM_CATEGORY_NAMES] as $categoryName ) {
			$title = Title::makeTitle( NS_CATEGORY, $categoryName );
			if ( !$title ) {
				continue;
			}
			$categoryLinks[] = $this->linkRenderer->makeLink(
				$title,
				new HtmlArmor( $title->getText() ),
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

		$html .= Html::closeElement( 'div' );

		return $html;
	}

	/**
	 *
	 * @param Title $title
	 * @return string
	 */
	protected function makeIcon( $title ) {
		$html = Html::openElement(
				'span',
				[
					'class' => 'bs-category-add-category'
				]
			);

		$html .= Html::element( 'i' );

		$html .= Html::closeElement( 'span' );

		return $html;
	}
}
