<?php
namespace BlueSpice\Calumma\Components;

use BlueSpice\Calumma\SkinDataFieldDefinition as SDFD;
use BlueSpice\Calumma\TemplateComponent;

class MobileMoreMenu extends TemplateComponent {

	/**
	 *
	 * @return string
	 */
	protected function getTemplatePathName() {
		return 'Calumma.Components.MobileMoreMenu';
	}

	/**
	 *
	 * @return array
	 */
	protected function getTemplateArgs() {
		$args = [];
		$skin = $this->getSkinTemplate()->getSkin();

		if ( $skin->getTitle()->isSpecialPage() ) {
			return [];
		}
		if ( $skin->getUser()->isAnon() ) {
			return [];
		}
		$args['show'] = parent::getTemplateArgs();
		$args['show']['title'] = "";
		$args['show']['links'] = $this->getSkinTemplate()->get( SDFD::MOBILE_MORE_MENU );

		if ( !\MediaWiki\MediaWikiServices::getInstance()
			->getPermissionManager()
			->userCan( 'edit', $skin->getUser(), $skin->getTitle() )
		) {
			$args['show']['isDisabled'] = true;
		}

		return $args;
	}
}
