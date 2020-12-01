<?php

namespace BlueSpice\Calumma\Components;

use Skins\Chameleon\Components\Component;

class LanguageSelector extends Component {

	/**
	 * The resulting HTML
	 * @return string
	 */
	public function getHtml() {
		$currentLanguage = $this->getSkin()->getLanguage()->getCode();
		$otherLanguages = $this->getSkinTemplate()->get( 'language_urls' );

		$html = \Html::openElement(
				'div',
				[
					'class' => 'bs-language-selector dropdown'
				]
			);

		if ( empty( $otherLanguages ) ) {
			$html .= \Html::closeElement( 'div' );
			return $html;
		}

		$lang = explode( '-', $currentLanguage );
		$class = ' bs-language-selector-icon-' . $lang[0] . ' bs-language-selector-icon ';

		$html .= \Html::openElement(
				'a',
				[
					'class' => 'bs-language-selector-current-language dropdown-toggle',
					'data-toggle' => 'dropdown',
					'aria-haspopup' => 'true',
					'aria-expanded' => 'false'
				]
			);

		$html .= \Html::element(
				'i',
				[
					'class' => $class
				]
			);
		$html .= \Html::closeElement( 'a' );

		$html .= \Html::openElement( 'div', [ 'class' => 'dropdown-menu' ] );
		$html .= \Html::openElement( 'ul' );

		foreach ( $otherLanguages as $language ) {
			$html .= \Html::openElement( 'li' );

			$class = $language['class'] . ' ' . $language['link-class'];

			$html .= \Html::openElement(
				'a',
				[
					'class' => $class,
					'title' => $language['title'],
					'lang' => $language['lang'],
					'href' => $language['href'],
					'hreflang' => $language['hreflang'],
				]
			);

			$lang = explode( '-', $language['lang'] );
			$class = ' bs-language-selector-icon-' . $lang[0] . ' bs-language-selector-icon ';

			$html .= \Html::element(
					'i',
					[
						'class' => $class
					]
				);
			$html .= \Html::element(
					'span',
					[
						'class' => 'bs-language-selector-text'
					],
					$language['href']
				);
			$html .= \Html::closeElement( 'a' );
			$html .= \Html::closeElement( 'li' );
		}

		$html .= \Html::closeElement( 'ul' );
		$html .= \Html::closeElement( 'div' );

		$html .= \Html::closeElement( 'div' );

		return $html;
	}
}
