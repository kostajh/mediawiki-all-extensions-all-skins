<?php

use BlueSpice\Api\Response\Standard;

class ApiBookshelfManage extends BSApiTasksBase {

	/**
	 *
	 * @var string[]
	 */
	protected $aTasks = [
		'deleteBook'
	];

	/**
	 *
	 * @return array
	 */
	protected function getRequiredTaskPermissions() {
		return [
			'deleteBook' => [ 'edit' ]
		];
	}

	/**
	 *
	 * @return string
	 */
	public function getDescription() {
		return 'Allows management of books inside the wiki';
	}

	/**
	 *
	 * @param \stdClass $aTaskData
	 * @param array $aParams
	 * @return Standard
	 */
	public function task_deleteBook( $aTaskData, $aParams ) {
		$oResult = $this->makeStandardReturn();

		$oTitle = Title::newFromId( $aTaskData->book_page_id );
		if ( !( $oTitle instanceof Title ) ) {
			$oResult->message = $oResult->errors['pageid'] =
				wfMessage( 'bs-bookshelfui-bookmanager-deletion-error-pageid' )->text();
			return $oResult;
		}

		if ( !\MediaWiki\MediaWikiServices::getInstance()
			->getPermissionManager()
			->userCan( 'delete', $this->getUser(), $oTitle )
		) {
			$oResult->message = $oResult->errors['permission'] =
				wfMessage( 'bs-bookshelfui-bookmanager-deletion-error-permission' )->text();
			return $oResult;
		}

		$oPage = WikiPage::factory( $oTitle );
		$error = '';
		$oResult->success = $oPage->doDeleteArticleReal(
			wfMessage( 'bs-bookshelfui-bookmanager-deletion-reason' )->text(),
			$this->getUser(),
			false,
			null,
			$error
		)->isOK();

		if ( $oResult->success == false ) {
			$oResult->message =
				wfMessage( 'bs-bookshelfui-bookmanager-deletion-error-unkown' )->text();
			$oResult->errors['saving'] = $error;
			$dbw = wfGetDB( DB_MASTER );
			wfDebugLog(
				'BS::Bookshelf',
				'SpecialBookshelfBookManager::ajaxDeleteBook: ' . $dbw->lastQuery()
			);
		}

		return $oResult;
	}
}
