<?php

namespace BlueSpice\SaferEdit\Hook\BsAdapterAjaxPingResult;

use BlueSpice\Hook\BsAdapterAjaxPingResult;
use BlueSpice\SaferEdit\SaferEditManager;
use Title;

class HandleSaferEditSave extends BsAdapterAjaxPingResult {

	/**
	 *
	 * @var Title
	 */
	protected $title = null;

	/**
	 * @var SaferEditManager
	 */
	protected $seManager;

	/**
	 * @inheritDoc
	 */
	public function __construct( $context, $config, $reference, $params, $articleId,
		$titleText, $namespaceIndex, $revisionId, &$singleResults ) {
		parent::__construct(
			$context,
			$config,
			$reference,
			$params,
			$articleId,
			$titleText,
			$namespaceIndex,
			$revisionId,
			$singleResults
		);

		$this->title = Title::newFromText( $this->titleText, $this->namespaceIndex );
		$this->seManager = $this->getServices()->getService( 'BSSaferEditManager' );
		$this->seManager->askEnvironmentalCheckers( 'getEditedTitle', $this->title );
	}

	protected function skipProcessing() {
		if ( $this->title === null ) {
			return true;
		}

		return $this->reference !== 'SaferEditSave';
	}

	protected function doProcess() {
		if ( !isset( $this->params[0]['bUnsavedChanges'] ) ) {
					return true;
		}
		if ( $this->params[0]['bUnsavedChanges'] !== true ) {
			return true;
		}

		$section = empty( $this->params[0]['section'] )
			? -1
			: $this->params[0]['section'];

		$status = $this->seManager->saveUserEditing(
			$this->getContext()->getUser(),
			$this->title,
			$section
		);

		$this->singleResults['success'] = $status->isOK();

		return true;
	}

}
