<?php

namespace BlueSpice\PageAssignments\Hook\SkinTemplateNavigationUniversal;

use BlueSpice\Hook\SkinTemplateNavigationUniversal;

class AddPageAssignmentsEntry extends SkinTemplateNavigationUniversal {
	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		if ( !$this->sktemplate->getRequest()->getVal( 'action', 'view' ) !== 'view' ) {
			return true;
		}
		if ( !$this->getServices()->getPermissionManager()
			->userCan(
				'pageassignments',
				$this->sktemplate->getUser(),
				$this->sktemplate->getTitle()
			)
		) {
			return true;
		}
		return false;
	}

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$this->links['actions']['pageassignments'] = [
			'text' => $this->msg( 'bs-pageassignments-menu-label' )->text(),
			'href' => '#',
			'class' => false,
			'id' => 'ca-pageassignments',
			'bs-group' => 'hidden'
		];
		return true;
	}

}
