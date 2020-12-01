<?php

namespace BlueSpice\PageAssignments\Hook\BeforePageDisplay;

class AddResources extends \BlueSpice\Hook\BeforePageDisplay {

	protected function doProcess() {
		$this->out->addModuleStyles( 'ext.pageassignments.styles' );

		if ( $this->out->getRequest()->getVal( 'action', 'view' ) !== 'view' ) {
			return true;
		}
		if ( $this->out->getTitle()->isSpecialPage() ) {
			return true;
		}

		$canAssign = false;
		$title = $this->getContext()->getTitle();
		if ( $title && $title->exists() &&
			\MediaWiki\MediaWikiServices::getInstance()
				->getPermissionManager()
				->userCan(
					'pageassignments',
					$this->getContext()->getUser(),
					$title
				)
		) {
			$canAssign = true;
		}
		$this->out->addJsConfigVars( 'bsgPageAssignmentsCanAssign', $canAssign );
		$this->out->addModules( 'ext.pageassignments.scripts' );
		return true;
	}

}
