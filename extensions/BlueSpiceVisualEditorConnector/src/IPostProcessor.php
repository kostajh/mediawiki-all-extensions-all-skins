<?php

namespace BlueSpice\VisualEditorConnector;

interface IPostProcessor {

	/**
	 * @param string $pageName
	 * @param string $content
	 * @return string
	 */
	public function process( $pageName, $content );
}
