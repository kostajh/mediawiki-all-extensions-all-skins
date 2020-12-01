<?php

namespace BlueSpice\Calumma\BreadcrumbRootNode;

use BlueSpice\Calumma\BreadcrumbRootNodeBase;
use Message;
use Title;

class NamespacePortalPage extends BreadcrumbRootNodeBase {

	/**
	 *
	 * @param Title $title
	 * @return string
	 */
	public function getHtml( $title ) {
		if ( $title->isSpecialPage() ) {
			return '';
		}

		$portalNamespace = $this->config->get(
			'BlueSpiceCalummaBreadcrumbNamespacePortalPageRootNodePortalNamespace'
		);

		$namespaceKey = $title->getNamespace();
		$nsText = $this->getLocalizedNamespaceText( $namespaceKey );
		if ( $title->getNamespace() === NS_MAIN ) {
			$nsText = Message::newFromKey( 'bs-ns_main' );
		}

		$namespacePortalPage = Title::makeTitle(
			$portalNamespace,
			$nsText
		);

		return $this->linkRenderer->makeLink(
			$namespacePortalPage,
			$nsText,
			[
				'class' => 'btn',
				'role' => 'button'
			]
		);
	}
}
