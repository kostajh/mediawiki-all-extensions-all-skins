<?php

namespace BlueSpice\Calumma\BreadcrumbRootNode;

use BlueSpice\Calumma\BreadcrumbRootNodeBase;
use Title;

class SpecialFilelist extends BreadcrumbRootNodeBase {

	/**
	 *
	 * @param Title $title
	 * @return string
	 */
	public function getHtml( $title ) {
		$namespaceKey = $title->getNamespace();

		if ( ( $namespaceKey === NS_FILE ) || ( $namespaceKey === NS_FILE_TALK ) ) {
			$nsText = $this->getLocalizedNamespaceText( $namespaceKey );

			$specialFilelist = \MediaWiki\MediaWikiServices::getInstance()
				->getSpecialPageFactory()
				->getTitleForAlias( 'Extendedfilelist' );

			if ( $specialFilelist === null ) {
				$specialFilelist = \MediaWiki\MediaWikiServices::getInstance()
				->getSpecialPageFactory()
				->getTitleForAlias( 'Filelist' );
			}

			return $this->linkRenderer->makeLink(
				$specialFilelist,
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
