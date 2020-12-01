<?php
namespace BlueSpice\Calumma\Components;

class Banner extends \Skins\Chameleon\Components\Structure {

	/**
	 * The resulting HTML
	 * @return string
	 */
	public function getHtml() {
		$html = \Html::openElement( 'div', [ 'class' => 'bs-banner' ] );
		$html .= parent::getHtml();
		$html .= \Html::closeElement( 'div' );

		return $html;
	}
}
