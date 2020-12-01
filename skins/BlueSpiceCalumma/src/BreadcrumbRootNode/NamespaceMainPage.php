<?php

namespace BlueSpice\Calumma\BreadcrumbRootNode;

use BlueSpice\Calumma\BreadcrumbRootNodeBase;
use Message;
use Title;

class NamespaceMainPage extends BreadcrumbRootNodeBase {

	/**
	 *
	 * @param Title $title
	 * @return string
	 */
	public function getHtml( $title ) {
		if ( $title->isSpecialPage() ) {
			return '';
		}

		$namespaceKey = $title->getNamespace();

		if ( ( $namespaceKey === NS_MAIN ) || ( $namespaceKey === NS_TALK ) || ( $namespaceKey >= 3000 ) ) {
			$namespaceMainPage = Title::makeTitle(
				$title->getNamespace(),
				Title::newMainPage()->getText()
			);

			$nsText = $this->getLocalizedNamespaceText( $namespaceKey );
			if ( $title->getNamespace() === NS_MAIN ) {
				$nsText = Message::newFromKey( 'bs-ns_main' )->text();
			}

			return $this->linkRenderer->makeLink(
				$namespaceMainPage,
				$nsText,
				[
					'class' => 'btn',
					'role' => 'button'
				]
			);
		}

		return '';
	}
}
