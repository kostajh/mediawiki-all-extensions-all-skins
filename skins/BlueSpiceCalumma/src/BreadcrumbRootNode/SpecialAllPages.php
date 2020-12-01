<?php

namespace BlueSpice\Calumma\BreadcrumbRootNode;

use BlueSpice\Calumma\BreadcrumbRootNodeBase;
use Message;
use Title;

class SpecialAllPages extends BreadcrumbRootNodeBase {

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

		if ( $this->skipForNamespaces( $namespaceKey ) === true ) {
			return '';
		}

		$nsText = $this->getLocalizedNamespaceText( $namespaceKey );
		if ( $title->getNamespace() === NS_MAIN ) {
			$nsText = Message::newFromKey( 'bs-ns_main' );
		}

		$specialAllpages = \MediaWiki\MediaWikiServices::getInstance()
			->getSpecialPageFactory()
			->getTitleForAlias( 'Allpages' );

		return $this->linkRenderer->makeLink(
			$specialAllpages,
			$nsText,
			[
				'class' => 'btn',
				'role' => 'button'
			],
			[
				'namespace' => $namespaceKey
			]
		);
	}

	/**
	 * @param int $currentNamespaceKey
	 * @return bool
	 */
	private function skipForNamespaces( $currentNamespaceKey ) {
		$invalidNamespaces = [
				NS_MAIN, NS_TALK,
				NS_FILE, NS_FILE_TALK,
				NS_TEMPLATE, NS_TEMPLATE_TALK,
				NS_CATEGORY, NS_CATEGORY_TALK
			];

		if ( in_array( $currentNamespaceKey, $invalidNamespaces ) ) {
				return true;
		}

		return false;
	}
}
