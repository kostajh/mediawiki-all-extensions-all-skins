<?php

namespace BlueSpice\Social\Tags\Hook\BSSocialEntityOutputRenderBeforeContent;

use BlueSpice\Social\Hook\BSSocialEntityOutputRenderBeforeContent;
use Html;
use Title;

class AddTagInfo extends BSSocialEntityOutputRenderBeforeContent {

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$data = $this->oEntityOutput->getEntity()->getFullData();

		if ( empty( $data['tags'] ) ) {
			return true;
		}

		$this->sOut .= Html::openElement( 'span', [
			'class' => 'bs-social-beforecontent-tags',
		] );

		foreach ( $data['tags'] as $tag ) {
			$title = Title::newFromText( $tag );
			if ( !$title ) {
				continue;
			}

			$this->sOut .= Html::element(
				'a',
				[ 'href' => $title->getLocalURL() ],
				"#$tag"
			);
		}
		$this->sOut .= Html::closeElement( 'span' );
		return true;
	}
}
