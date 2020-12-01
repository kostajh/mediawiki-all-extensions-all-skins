<?php

namespace BlueSpice\Calumma\BreadcrumbRootNode;

use BlueSpice\Calumma\BreadcrumbRootNodeBase;
use Title;

class SpecialSpecialPages extends BreadcrumbRootNodeBase {

	/**
	 *
	 * @param Title $title
	 * @return string
	 */
	public function getHtml( $title ) {
		if ( !$title->isSpecialPage() ) {
			return '';
		}

		$nsText = $this->getLocalizedNamespaceText( NS_SPECIAL );

		$specialSpecialpages = \MediaWiki\MediaWikiServices::getInstance()
			->getSpecialPageFactory()
			->getTitleForAlias( 'Specialpages' );

		$specialBadtitle = \MediaWiki\MediaWikiServices::getInstance()
			->getSpecialPageFactory()
			->getTitleForAlias( 'Badtitle' );

		$specialLogin = \MediaWiki\MediaWikiServices::getInstance()
			->getSpecialPageFactory()
			->getTitleForAlias( 'Login' );

		if ( $title->equals( $specialSpecialpages )
			|| $title->equals( $specialBadtitle )
			|| $title->equals( $specialLogin ) ) {
			return '';
		}

		return $this->linkRenderer->makeLink(
			$specialSpecialpages,
			$nsText,
			[
				'class' => 'btn',
				'role' => 'button'
			]
		);
	}

}
