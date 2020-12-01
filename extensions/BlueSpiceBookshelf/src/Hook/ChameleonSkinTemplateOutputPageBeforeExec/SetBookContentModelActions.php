<?php

namespace BlueSpice\Bookshelf\Hook\ChameleonSkinTemplateOutputPageBeforeExec;

use BlueSpice\Calumma\Hook\ChameleonSkinTemplateOutputPageBeforeExec;
use BlueSpice\SkinData;
use Message;

class SetBookContentModelActions extends ChameleonSkinTemplateOutputPageBeforeExec {

	protected function skipProcessing() {
		return $this->skin->getTitle()->getContentModel() !== 'book';
	}

	protected function doProcess() {
		$this->template->data[SkinData::FEATURED_ACTIONS]['edit']['editbooksource'] = [
			'position' => '10',
			'id' => 'edit-book-source',
			'text' => $this->getText(),
			'title' => $this->getText(),
			'href' => $this->skin->getTitle()->getLocalURL( [
				'action' => 'editbooksource',
			] )
		];

		unset( $this->template->data[SkinData::FEATURED_ACTIONS]['edit']['new-section'] );
	}

	private function getText() {
		$exists = $this->skin->getTitle()->exists();
		if ( $exists ) {
			return Message::newFromKey( 'bs-bookshelf-action-editbooksource' )->plain();
		}
		return Message::newFromKey( 'bs-bookshelf-action-editbooksource-create' )->plain();
	}
}
