<?php

namespace BlueSpice\Calumma;

use BlueSpice\Calumma\Panel\BasePanel;
use RawMessage;

abstract class SkinDataPanel extends BasePanel {

	/**
	 *
	 * @var string
	 */
	protected $id = '';

	/**
	 *
	 * @var array
	 */
	protected $definition = [];

	/**
	 *
	 * @param string $id
	 * @param array $definition
	 */
	public function __construct( $id, $definition ) {
		$this->id = $id;
		$this->definition = $definition;
	}

	/**
	 *
	 * @return string
	 */
	public function getHtmlId() {
		return $this->id;
	}

	/**
	 *
	 * @return \Message
	 */
	public function getTitleMessage() {
		return new RawMessage( $this->definition['label'] );
	}

	/**
	 * Is this panel considered empty / has no content
	 * @return bool
	 */
	public function isEmpty() {
		return false;
	}

}
