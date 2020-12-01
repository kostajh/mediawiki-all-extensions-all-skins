<?php

namespace BlueSpice\Calumma\Components;

class SimpleLinkListGroup extends \BlueSpice\Calumma\Components\LinkListGroup {

	protected $linkDefs = [];

	/**
	 *
	 * @param array $linkdefs
	 */
	public function __construct( $linkdefs ) {
		$this->linkDefs = $linkdefs;
	}

	/**
	 *
	 * @return array
	 */
	protected function getLinkDefinitions() {
		return $this->linkDefs;
	}
}
