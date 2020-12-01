<?php

namespace BlueSpice\CategoryCheck\Hook\BeforePageDisplay;

use BlueSpice\Hook\BeforePageDisplay;

class AddModules extends BeforePageDisplay {

	protected $enabledNamespaces = [];

	protected function skipProcessing() {
		if ( $this->out->getTitle()->isSpecialPage() ) {
			return true;
		}

		// No JS, CSS and so on
		if ( !$this->out->getTitle()->isWikitextPage() ) {
			return true;
		}

		if ( $this->isNotEnabledForCurrentNamespace() ) {
			return true;
		}

		return false;
	}

	protected function doProcess() {
		$this->out->addJsConfigVars(
			'bsgCategoryCheckNamespaces',
			$this->enabledNamespaces
		);

		return true;
	}

	protected function isNotEnabledForCurrentNamespace() {
		$this->enabledNamespaces =
			$this->getConfig()->get( 'CategoryCheckNamespaces' );

		return !in_array(
			$this->out->getTitle()->getNamespace(),
			$this->enabledNamespaces
		);
	}
}
