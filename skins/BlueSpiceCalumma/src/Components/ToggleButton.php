<?php
namespace BlueSpice\Calumma\Components;

class ToggleButton extends \Skins\Chameleon\Components\Structure {

	/**
	 * The resulting HTML
	 * @return string
	 */
	public function getHtml() {
		$data = $this->getDomElement()->getAttribute( 'data-toggle' );
		$class = $this->getDomElement()->getAttribute( 'class' );

		$html = \Html::openElement( 'a', [
				'href' => '#',
				'title' => wfMessage( 'bs-calumma-toggle-button-tooltip' )->plain(),
				'class' => ' calumma-toggle-button ' . $class,
				'data-toggle' => $data,
				'role' => 'button',
				'aria-label' => wfMessage( 'bs-calumma-toggle-button-tooltip' )->plain()
			] );

		$html .= \Html::openElement( 'i', [ 'class' => $data ] );
		$html .= \Html::closeElement( 'i' );

		$html .= \Html::closeElement( 'a' );

		return $html;
	}
}
