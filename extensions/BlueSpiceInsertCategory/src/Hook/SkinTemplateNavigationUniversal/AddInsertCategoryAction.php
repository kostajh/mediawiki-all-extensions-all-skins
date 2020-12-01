<?php

namespace BlueSpice\InsertCategory\Hook\SkinTemplateNavigationUniversal;

use BlueSpice\Hook\SkinTemplateNavigationUniversal;

class AddInsertCategoryAction extends SkinTemplateNavigationUniversal {

	protected function skipProcessing() {
		if ( $this->getContext()->getRequest()->getVal( 'action', 'view' ) != 'view' ) {
			return true;
		}
		if ( !$this->getServices()->getPermissionManager()
			->userCan(
				'edit',
				$this->sktemplate->getUser(),
				$this->sktemplate->getTitle()
			)
		) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$this->links['actions']['insert_category'] = [
			'text' => $this->msg( 'bs-insertcategory-insertcat' )->text(),
			'href' => '#',
			'class' => false,
			'id' => 'ca-insertcategory',
			'bs-group' => 'hidden'
		];
	}

}
