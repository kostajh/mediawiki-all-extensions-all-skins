<?php

namespace BlueSpice\SaferEdit\Hook\BsAdapterAjaxPingResult;

use BlueSpice\Hook\BsAdapterAjaxPingResult;
use BlueSpice\SaferEdit\EditWarningBuilder;
use Title;

class HandleSaferEditIsSomeoneEditing extends BsAdapterAjaxPingResult {

	/**
	 *
	 * @var Title
	 */
	protected $title = null;

	protected function skipProcessing() {
		if ( $this->reference !== 'SaferEditIsSomeoneEditing' ) {
			return true;
		}

		$this->title = Title::newFromText( $this->titleText );
		if ( $this->title === null ) {
			return true;
		}

		if ( !$this->title->exists() ) {
			return true;
		}

		return false;
	}

	protected function doProcess() {
		$this->singleResults['success'] = true;
		$this->singleResults['someoneEditingView'] = '';
		$this->singleResults['safereditView'] = '';

		$loadBalancer = $this->getServices()->getDBLoadBalancer();
		$config = $this->getConfig();
		$currentUser = $this->getContext()->getUser();
		$editWarningBuilder = new EditWarningBuilder(
			$loadBalancer,
			$config,
			$currentUser,
			$this->title
		);

		$this->singleResults['someoneEditingView']
			= $editWarningBuilder->getMessage();

		return true;
	}
}
