<?php

namespace BlueSpice\Bookshelf\Hook\ChameleonSkinTemplateOutputPageBeforeExec;

use BlueSpice\Calumma\Hook\ChameleonSkinTemplateOutputPageBeforeExec;

class AddToBookTool extends ChameleonSkinTemplateOutputPageBeforeExec {

	protected function skipProcessing() {
		if ( $this->skin->getTitle()->exists() === false ) {
			return true;
		}
		$pm = $this->getServices()->getPermissionManager();
		$user = $this->skin->getUser();
		$title = $this->skin->getTitle();
		if ( $pm->userCan( 'edit', $user, $title ) === false ) {
			return true;
		}
	}

	protected function doProcess() {
		$links['actions']['bookshelf-add-to-book'] = [
			'text' => wfMessage( 'bs-bookshelf-add-to-book-label' )->text(),
			'href' => '#',
			'class' => false,
			'id' => 'ca-bookshelf-add-to-book'
		];

		return true;
	}
}
