<?php
namespace BlueSpice\Calumma\Components;

class Header extends \Skins\Chameleon\Components\Structure {

	/**
	 * The resulting HTML
	 * @return string
	 */
	public function getHtml() {
		$html = \Html::openElement( 'header', [ 'class' => 'main-header' ] );
		$html .= parent::getHtml();
		$html .= \Html::closeElement( 'header' );
		return $html;
	}
}
