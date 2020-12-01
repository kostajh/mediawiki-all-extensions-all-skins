<?php

namespace BlueSpice\Calumma\BreadcrumbRootNode;

use BlueSpice\Calumma\BreadcrumbRootNodeBase;
use Title;

class SpecialCategories extends BreadcrumbRootNodeBase {

	/**
	 *
	 * @param Title $title
	 * @return string
	 */
	public function getHtml( $title ) {
		$namespaceKey = $title->getNamespace();

		if ( ( $namespaceKey === NS_CATEGORY ) || ( $namespaceKey === NS_CATEGORY_TALK ) ) {
			$nsText = $this->getLocalizedNamespaceText( $namespaceKey );

			$specialCategories = \MediaWiki\MediaWikiServices::getInstance()
				->getSpecialPageFactory()
				->getTitleForAlias( 'Categories' );

			return $this->linkRenderer->makeLink(
				$specialCategories,
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
