<?php

namespace BlueSpice\NamespaceCSS\Hook\BeforePageDisplay;

use BlueSpice\NamespaceCSS\Helper;

class AddResources extends \BlueSpice\Hook\BeforePageDisplay {
	protected function skipProcessing() {
		$title = Helper::buildTitleFromNamespaceIndex(
			$this->out->getTitle()->getNamespace()
		);
		if ( !$title || !$title->exists() ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$title = Helper::buildTitleFromNamespaceIndex(
			$this->out->getTitle()->getNamespace()
		);
		$this->out->addStyle( $title->getLocalUrl( [
			'action' => 'raw',
			'ctype' => 'text/css'
		] ) );
		return true;
	}
}
