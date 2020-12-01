<?php

namespace BlueSpice\Bookshelf\Hook\SkinTemplateNavigationUniversal;

use BlueSpice\Hook\SkinTemplateNavigationUniversal;

class AddAddToBookEntry extends SkinTemplateNavigationUniversal {
	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		$title = $this->sktemplate->getTitle();
		if ( !$title->exists() ) {
			return true;
		}
		if ( !$this->getServices()
			->getPermissionManager()
			->userCan( 'edit', $this->sktemplate->getUser(), $title )
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
		$this->links['actions']['bookshelf-add-to-book'] = [
			'text' => $this->msg( 'bs-bookshelf-add-to-book-label' )->text(),
			'href' => '#',
			'class' => false,
			'id' => 'ca-bookshelf-add-to-book'
		];
		$this->sktemplate->getOutput()->addModules( 'ext.bluespice.bookshelf.addToBook' );
		return true;
	}

}
