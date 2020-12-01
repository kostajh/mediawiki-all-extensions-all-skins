<?php

namespace BlueSpice\Calumma\Components;

class PageLayout extends \Skins\Chameleon\Components\Structure {

	/**
	 * The resulting HTML
	 * @return string
	 */
	public function getHtml() {
		$html = \Html::openElement( 'div', [ 'id' => 'content' ] );
		$html .= parent::getHtml();
		$html .= \Html::closeElement( 'div' );

		return $html;
	}
}
