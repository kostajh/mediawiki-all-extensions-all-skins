<?php

namespace BlueSpice\Social\Content;

use BlueSpice\Content\EntityHandler as EntityHanderBase;

class EntityHandler extends EntityHanderBase {

	/**
	 *
	 * @param string $modelId
	 */
	public function __construct( $modelId = CONTENT_MODEL_BSSOCIAL ) {
		parent::__construct( $modelId );
	}

	/**
	 * @return string
	 */
	protected function getContentClass() {
		return "BlueSpice\\Social\\Content\\Entity";
	}
}
