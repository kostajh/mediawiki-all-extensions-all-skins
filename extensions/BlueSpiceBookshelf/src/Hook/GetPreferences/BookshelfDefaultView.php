<?php

namespace BlueSpice\Bookshelf\Hook\GetPreferences;

use BlueSpice\Hook\GetPreferences;

/**
 * Adds the user setting bs-bookshelf-defaultview
 */
class BookshelfDefaultView extends GetPreferences {

	protected function doProcess() {
		$this->preferences['bs-bookshelf-defaultview'] = [
			'type' => 'radio',
			'label-message' => 'bs-bookshelf-prof-defaultview',
			'section' => 'rendering/bookshelf',
			'options' => [
				$this->msg( 'bs-bookshelf-prof-defaultview-grid' )->plain()
				=> 'gridviewpanel',
				$this->msg( 'bs-bookshelf-prof-defaultview-images' )->plain()
				=> 'dataviewpanel'
			]
		];

		return true;
	}
}
