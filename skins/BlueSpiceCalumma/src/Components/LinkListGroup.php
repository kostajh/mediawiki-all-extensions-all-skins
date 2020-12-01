<?php

namespace BlueSpice\Calumma\Components;

use BlueSpice\Calumma\TemplateComponent;

abstract class LinkListGroup extends TemplateComponent {

	/**
	 *
	 * @return string
	 */
	protected function getTemplatePathName() {
		return 'Calumma.Components.LinkListGroup';
	}

	/**
	 *
	 * @return array
	 */
	protected function getTemplateArgs() {
		$args = parent::getTemplateArgs();
		$args['links'] = $this->getLinkDefinitions();
		return $args;
	}

	/**
	 * return array
	 */
	abstract protected function getLinkDefinitions();
}
