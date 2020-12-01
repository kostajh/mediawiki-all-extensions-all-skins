<?php

namespace BlueSpice\Calumma\Components;

use BlueSpice\Calumma\TemplateComponent;

class CollapsibleGroup extends TemplateComponent {

	protected $defs = [];

	/**
	 *
	 * @param array $defs
	 */
	public function __construct( $defs ) {
		$this->defs = $defs;
	}

	/**
	 *
	 * @return string
	 */
	protected function getTemplatePathName() {
		return 'Calumma.Components.CollapsibleGroup';
	}

	/**
	 *
	 * @return array
	 */
	protected function getTemplateArgs() {
		$args = parent::getTemplateArgs();
		$args['id'] = $this->getGroupId();
		$args['title'] = $this->getGroupTitle();
		$args['content'] = $this->getGroupContent();
		$args['collapse'] = $this->getGroupCollapseState();
		return $args;
	}

	/**
	 *
	 * @return string
	 */
	protected function getGroupId() {
		return $this->defs['id'];
	}

	/**
	 *
	 * @return string
	 */
	protected function getGroupTitle() {
		return $this->defs['title'];
	}

	/**
	 *
	 * @return string
	 */
	protected function getGroupContent() {
		return $this->defs['content'];
	}

	/**
	 *
	 * @return bool
	 */
	public function getGroupCollapseState() {
		if ( isset( $this->defs['collapse'] ) ) {
			return $this->defs['collapse'];
		}

		return false;
	}

}
