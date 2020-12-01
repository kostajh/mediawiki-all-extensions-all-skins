<?php

use BlueSpice\Api\Response\Standard;

class BSApiDashboardWidgetsTasks extends BSApiTasksBase {

	/**
	 *
	 * @var array
	 */
	protected $aTasks = [
		'wikipage' => [
			'examples' => [
				[
					'wikiArticle' => 'Main_page'
				]
			],
			'params' => [
				'wikiArticle' => [
					'desc' => 'Valid title name',
					'type' => 'string',
					'required' => true
				]
			]
		]
	];

	/**
	 *
	 * @return array
	 */
	protected function getRequiredTaskPermissions() {
		return [
			'wikipage' => [ 'read' ]
		];
	}

	/**
	 *
	 * @param \stdClass $oTaskData
	 * @param array $aParams
	 * @return Standard
	 */
	public function task_wikipage( $oTaskData, $aParams ) {
		$oResponse = $this->makeStandardReturn();

		if ( !isset( $oTaskData->wikiArticle ) ) {
			$oResponse->success = true;
			$oResponse->payload = [ "html" => wfMessage( 'compare-invalid-title' )->plain() ];
			return $oResponse;
		}

		$oTitle = Title::newFromText( $oTaskData->wikiArticle );
		if ( !$oTitle ) {
			$oResponse->success = false;
			$oResponse->payload = [ "html" => wfMessage( 'compare-invalid-title' )->plain() ];
			return $oResponse;
		}

		if ( !\MediaWiki\MediaWikiServices::getInstance()
			->getPermissionManager()
			->userCan( 'read', $this->getUser(), $oTitle )
		) {
			$oResponse->success = false;
			$oResponse->payload = [ "html" => wfMessage( 'bs-permissionerror' )->plain() ];
			return $oResponse;
		}
		$oWikiPage = WikiPage::factory( $oTitle );
		if ( !$oWikiPage->getContent() ) {
			$oResponse->success = false;
			$oResponse->payload = [ "html" => wfMessage( 'compare-invalid-title' )->plain() ];
			return $oResponse;
		}

		$sHTML = $oWikiPage->getContent()->getParserOutput( $oTitle )->getText();

		$oResponse->success = true;
		$oResponse->payload = [ "html" => $sHTML ];
		return $oResponse;
	}

	/**
	 *
	 * @return bool
	 */
	public function needsToken() {
		return false;
	}

	/**
	 * Returns an array of allowed parameters
	 * @return array
	 */
	protected function getAllowedParams() {
		$paramList = parent::getAllowedParams();

		return array_merge(
			$paramList,
			[
				'_dc' => [
					ApiBase::PARAM_TYPE => 'string',
					ApiBase::PARAM_REQUIRED => false,
					// TODO: Description
					ApiBase::PARAM_HELP_MSG => 'apihelp-bs-dashboard-task-param-dc',
				]
			]
		);
	}
}
