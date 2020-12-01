<?php

namespace BlueSpice\VisualEditorConnector;

use DOMDocument;

abstract class PostProcessor implements IPostProcessor {
	/**
	 * @return IPostProcessor
	 */
	public static function factory() {
		return new static();
	}

	/**
	 * @param strng $html
	 * @return DOMDocument|null if there was an error
	 */
	protected function getDOMDocument( $html = '' ) {
		$dom = new DOMDocument();
		if ( $dom->loadHTML( $html ) ) {
			return $dom;
		}
		return null;
	}
}
