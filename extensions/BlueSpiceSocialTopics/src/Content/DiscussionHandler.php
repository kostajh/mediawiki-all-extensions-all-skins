<?php

namespace BlueSpice\Social\Topics\Content;

class DiscussionHandler extends \WikitextContentHandler {

	/**
	 *
	 * @param string $modelId
	 */
	public function __construct( $modelId = CONTENT_MODEL_BSSOCIALDISCUSSION ) {
		parent::__construct( $modelId );
	}

	/**
	 * @return string
	 */
	public function getContentClass() {
		return "\\BlueSpice\\Social\\Topics\\Content\\Discussion";
	}
}
