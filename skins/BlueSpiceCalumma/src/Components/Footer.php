<?php
namespace BlueSpice\Calumma\Components;

class Footer extends \Skins\Chameleon\Components\Structure {

	/**
	 * The resulting HTML
	 * @return string
	 */
	public function getHtml() {
		$html = \Html::openElement( 'footer', [ 'class' => 'main-footer' ] );
		$html .= parent::getHtml();
		$html .= \Html::closeElement( 'footer' );

		return $html;
	}
}
