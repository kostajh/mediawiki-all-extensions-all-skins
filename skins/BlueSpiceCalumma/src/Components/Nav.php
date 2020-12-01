<?php
namespace BlueSpice\Calumma\Components;

class Nav extends \Skins\Chameleon\Components\Structure {

	/**
	 * The resulting HTML
	 * @return string
	 */
	public function getHtml() {
		$classes = $this->getDomElement()->getAttribute( 'class' );
		$classes .= ' navbar navbar-static-top ';

		$html = \Html::openElement( 'nav', [ 'class' => $classes ] );
		$html .= parent::getHtml();
		$html .= \Html::closeElement( 'nav' );

		return $html;
	}
}
