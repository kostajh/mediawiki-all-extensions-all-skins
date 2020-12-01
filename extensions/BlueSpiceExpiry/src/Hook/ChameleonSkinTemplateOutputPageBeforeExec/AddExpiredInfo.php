<?php

namespace BlueSpice\Expiry\Hook\ChameleonSkinTemplateOutputPageBeforeExec;

use BlueSpice\Calumma\Hook\ChameleonSkinTemplateOutputPageBeforeExec;
use BlueSpice\Expiry\Panel\Flyout;
use BlueSpice\SkinData;

class AddExpiredInfo extends ChameleonSkinTemplateOutputPageBeforeExec {

	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		$title = $this->skin->getTitle();
		if ( $title instanceof \Title == false || $title->exists() === false ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$fname = __METHOD__;
		$this->mergeSkinDataArray(
			SkinData::PAGE_DOCUMENTS_PANEL,
			[
				'expiry' => [
					'position' => 30,
					'callback' => function ( $sktemplate ) use ( $fname ) {
						$currentPageId = $sktemplate->getSkin()->getTitle()->getArticleId();
						// TODO: Caching? But when to invalidate?
						$dbr = wfGetDB( DB_REPLICA );
						$row = $dbr->selectRow(
							'bs_expiry',
							'exp_date',
							[ 'exp_page_id' => $currentPageId ],
							$fname,
							[ 'ORDER BY' => 'exp_date DESC' ]
						);

						$expiryTS = '';
						if ( $row !== false ) {
							$expiryTS = wfTimestamp( TS_MW, strtotime( $row->exp_date ) );
						}

						return new Flyout( $sktemplate, $expiryTS );
					}
				]
			]
		);

		$this->appendSkinDataArray( SkinData::EDIT_MENU_BLACKLIST, 'expiryCreate' );
	}
}
