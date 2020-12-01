<?php
namespace BlueSpice\VisualEditorConnector\Hook\BSPageTemplatesModifyTargetUrl;

use BlueSpice\PageTemplates\Hook\BSPageTemplatesModifyTargetUrl;

class UseVisualEditor extends BSPageTemplatesModifyTargetUrl {
	protected function skipProcessing() {
		$enabledNamespaces = $this->getConfig()->get( 'VisualEditorAvailableNamespaces' );
		$targetNamespace = $this->targetTitle->getNsText();
		$targetNamespaceIndex = $this->targetTitle->getNamespace();

		if ( isset( $enabledNamespaces[$targetNamespace] ) &&
			$enabledNamespaces[$targetNamespace] === true ) {
			return false;
		}
		if ( isset( $enabledNamespaces[$targetNamespaceIndex] ) &&
			$enabledNamespaces[$targetNamespaceIndex] === true ) {
			return false;
		}
		return true;
	}

	protected function doProcess() {
		$this->targetUrl = $this->targetTitle->getLinkURL(
			[
				'veaction' => 'edit',
				'preload' => $this->preloadTitle ? $this->preloadTitle->getPrefixedDBkey() : '',
				'redlink' => '1'
			]
		);
		return true;
	}
}
