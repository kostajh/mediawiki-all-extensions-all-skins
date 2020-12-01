<?php

class ApiBookshelfMassAddPageStore extends BSApiExtJSStoreBase {

	/**
	 *
	 * @param string $sQuery
	 * @return array
	 */
	protected function makeData( $sQuery = '' ) {
		$params = $this->extractRequestParams();

		$root = $params[ 'root' ];
		$type = $params[ 'type' ];

		$massAppPageProvider = BlueSpice\Bookshelf\MassAdd\PageProvider::getInstance();
		$massAppPageProvider->setType( $type );
		$massAppPageProvider->setRoot( $root );

		$pages = $massAppPageProvider->getData();

		return $pages;
	}

	/**
	 *
	 * @return array
	 */
	public function getAllowedParams() {
		return array_merge(
			parent::getAllowedParams(),
			[
				'root' => [
					ApiBase::PARAM_TYPE => 'string',
					ApiBase::PARAM_REQUIRED => true
				],
				'type' => [
					ApiBase::PARAM_TYPE => 'string',
					ApiBase::PARAM_REQUIRED => true
				],
				'limit' => [
					ApiBase::PARAM_TYPE => 'integer',
					ApiBase::PARAM_REQUIRED => false,
					ApiBase::PARAM_DFLT => 9999
				]
			]
		);
	}

	/**
	 *
	 * @return array
	 */
	public function getParamDescription() {
		return array_merge(
			parent::getParamDescription(),
			[
				'root' => 'Root value based on which to return pages',
				'type' => 'Type of source for mass add',
				'limit' => 'Number of results to return'
			]
		);
	}
}
