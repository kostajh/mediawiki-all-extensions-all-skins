<?php
namespace BlueSpice\Calumma\Components;

class Content extends \Skins\Chameleon\Components\Structure {

	/**
	 * The resulting HTML
	 * @return string
	 */
	public function getHtml() {
		$html = \Html::openElement( 'div', [ 'class' => 'content-wrapper' ] );
		$html .= parent::getHtml();
		$html .= \Html::closeElement( 'div' );

		return $html;
	}
}
