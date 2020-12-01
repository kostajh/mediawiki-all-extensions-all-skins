<?php
namespace BlueSpice\Calumma\Components;

class DataBeforeContent extends \Skins\Chameleon\Components\Structure {

	/**
	 * The resulting HTML
	 * @return string
	 */
	public function getHtml() {
		$html = \Html::openElement( 'div', [ 'class' => 'bs-data-before-content' ] );
		$html .= parent::getHtml();

		$dataBeforeContent = $this->getSkinTemplate()->get(
			\BlueSpice\SkinData::BEFORE_CONTENT,
			[]
		);

		foreach ( $dataBeforeContent as $id => $data ) {
			$html .= \Html::openElement( 'div', [ 'class' => 'bs-data-before-content-' . $id ] );
			$html .= $data;
			$html .= \Html::closeElement( 'div' );
		}

		$html .= \Html::closeElement( 'div' );

		return $html;
	}
}
