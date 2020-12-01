<?php

namespace BlueSpice\Expiry\Hook\SkinTemplateNavigationUniversal;

use BlueSpice\Hook\SkinTemplateNavigationUniversal;

class AddExpiryCreateEntry extends SkinTemplateNavigationUniversal {
	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		$title = $this->sktemplate->getTitle();

		if ( !$this->sktemplate->getUser()->isLoggedIn() ) {
			return true;
		}
		if ( !$title->exists() || $title->isSpecialPage() ) {
			return true;
		}
		$isAllowed = $this->getServices()->getPermissionManager()->userCan(
			'expirearticle',
			$this->sktemplate->getUser(),
			$title
		);
		if ( !$isAllowed ) {
			return true;
		}

		return false;
	}

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$this->links['actions']['expiryCreate'] = [
			"class" => '',
			"text" => $this->msg( 'bs-expiry-menu-entry-create' )->plain(),
			"href" => "#",
			"bs-group" => "hidden"
		];
		return true;
	}

}
