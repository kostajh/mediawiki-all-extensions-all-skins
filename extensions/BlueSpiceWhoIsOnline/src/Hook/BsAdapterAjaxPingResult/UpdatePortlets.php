<?php

namespace BlueSpice\WhoIsOnline\Hook\BsAdapterAjaxPingResult;

use BlueSpice\Renderer\Params;
use BlueSpice\WhoIsOnline\Renderer\UserList;
use BlueSpice\WhoIsOnline\Tracer;
use Title;

class UpdatePortlets extends \BlueSpice\Hook\BsAdapterAjaxPingResult {

	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		if ( $this->reference !== 'WhoIsOnline' ) {
			return true;
		}
		$title = Title::newFromText( $this->titleText, $this->namespaceIndex );
		if ( !$title || !\MediaWiki\MediaWikiServices::getInstance()
			->getPermissionManager()
			->userCan( 'read', $this->getContext()->getUser(), $title )
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
		$recordSet = $this->getTracer()->getTracedRecords();
		$this->singleResults['count'] = $recordSet->getTotal();

		$portlet = $this->getServices()->getService( 'BSRendererFactory' )->get(
			'whoisonline-userlist',
			new Params( [
				UserList::PARAM_RECORD_SET => $recordSet
			] ),
			$this->getContext()
		);
		$this->singleResults['portletItems'] = $portlet->render();

		$this->singleResults['success'] = true;
		return true;
	}

	/**
	 *
	 * @return Tracer
	 */
	protected function getTracer() {
		return $this->getServices()->getService( 'BSWhoIsOnlineTracer' );
	}
}
