<?php

namespace BlueSpice\VisualEditorConnector;

interface IPreProcessor {

	/**
	 * @param string $pageName
	 * @param string $wikitext
	 * @return string
	 */
	public function process( $pageName, $wikitext );
}
