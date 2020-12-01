<?php
namespace BlueSpice\Social\Profile\Content;

class ProfileHandler extends \WikiTextContentHandler {

	/**
	 *
	 * @param string $modelId
	 */
	public function __construct( $modelId = CONTENT_MODEL_BSSOCIALPROFILE ) {
		parent::__construct( $modelId );
	}

	/**
	 * @return string
	 */
	public function getContentClass() {
		return "\\BlueSpice\\Social\\Profile\\Content\\Profile";
	}
}
