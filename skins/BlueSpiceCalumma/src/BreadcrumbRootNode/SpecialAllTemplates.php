<?php

namespace BlueSpice\Calumma\BreadcrumbRootNode;

use BlueSpice\Calumma\BreadcrumbRootNodeBase;
use Title;

class SpecialAllTemplates extends BreadcrumbRootNodeBase {

	/**
	 *
	 * @param Title $title
	 * @return string
	 */
	public function getHtml( $title ) {
		$namespaceKey = $title->getNamespace();

		if ( ( $namespaceKey === NS_TEMPLATE ) || ( $namespaceKey === NS_TEMPLATE_TALK ) ) {
			$nsText = $this->getLocalizedNamespaceText( $namespaceKey );

			$specialAllTemplates = \MediaWiki\MediaWikiServices::getInstance()
				->getSpecialPageFactory()
				->getTitleForAlias( 'Allpages' );

			return $this->linkRenderer->makeLink(
				$specialAllTemplates,
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

		return '';
	}
}
