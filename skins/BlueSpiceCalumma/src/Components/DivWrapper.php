<?php
namespace BlueSpice\Calumma\Components;

class DivWrapper extends \Skins\Chameleon\Components\Structure {

	/**
	 * The resulting HTML
	 * @return string
	 */
	public function getHtml() {
		$class = $this->getDomElement()->getAttribute( 'class' );

		$html = \Html::openElement( 'div', [ 'class' => $class ] );
		$html .= parent::getHtml();
		$html .= \Html::closeElement( 'div' );

		return $html;
	}
}
