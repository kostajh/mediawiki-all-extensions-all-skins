<?php

namespace BlueSpice\Social\Topics\Renderer\Entity;

use Html;

class Topic extends \BlueSpice\Social\Renderer\Entity\Text {

	/**
	 *
	 * @param mixed $val
	 * @return string
	 */
	protected function render_beforecontent( $val ) {
		$out = parent::render_beforecontent( $val );
		// TODO: get real discussion entity!
		$out .= Html::openElement( 'span', [
			'class' => 'bs-social-beforecontent-topic',
		] );
		$out .= Html::element(
			'a',
			[ 'href' => $this->getEntity()->getRelatedTitle()->getFullURL() ],
			$this->getEntity()->getRelatedTitle()->getFullText()
		);
		$out .= Html::closeElement( 'span' );
		return $out;
	}
}
