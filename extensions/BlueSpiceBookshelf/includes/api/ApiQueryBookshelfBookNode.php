<?php

class ApiQueryBookshelfBookNode extends ApiQueryBase {

	/**
	 *
	 * @param string $query
	 * @param string $moduleName
	 */
	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName, 'bsbsbn' );
	}

	/**
	 *
	 * @throws Exception
	 */
	public function execute() {
		$aParams = $this->extractRequestParams( false );

		// TODO: Also allow query by wiki page title instead of 'book+path'?
		// $pageSet = $this->getPageSet();
		// $pageSet->getGoodTitles();
		throw new Exception( 'Not yet implemented' );
	}

	/**
	 *
	 * @return array
	 */
	protected function getAllowedParams() {
		return [
			'prop' => [
				ApiBase::PARAM_ISMULTI => true,
				ApiBase::PARAM_DFLT => 'prev|current|next',
				ApiBase::PARAM_TYPE => [
					'prev',
					'current',
					'next',
					// 'nextsibling',
					// 'prevsibling',
				]
			],
			'path' => [
				ApiBase::PARAM_TYPE => 'string'
			],
			'book' => [
				ApiBase::PARAM_TYPE => 'string'
			],
		];
	}
}
