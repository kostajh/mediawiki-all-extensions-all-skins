<?php
namespace BlueSpice\Calumma\Components;

class DataAfterContent extends \Skins\Chameleon\Components\Structure {

	/**
	 * The resulting HTML
	 * @return string
	 */
	public function getHtml() {
		$html = \Html::openElement( 'div', [ 'class' => 'bs-data-after-content' ] );
		$html .= parent::getHtml();

		$dataAfterContent = $this->getSkinTemplate()->get(
			\BlueSpice\SkinData::AFTER_CONTENT
		);

		if ( empty( $dataAfterContent ) ) {
			$html .= \Html::closeElement( 'div' );
			return $html;
		}

		foreach ( $dataAfterContent as $id => $data ) {
			$html .= \Html::openElement( 'div', [ 'class' => 'bs-data-after-content-' . $id ] );
			$html .= $data;
			$html .= \Html::closeElement( 'div' );
		}
		$html .= \Html::closeElement( 'div' );

		return $html;
	}
}
