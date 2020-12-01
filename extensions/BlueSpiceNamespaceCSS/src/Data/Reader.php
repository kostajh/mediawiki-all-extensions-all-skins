<?php

namespace BlueSpice\NamespaceCSS\Data;

use MediaWiki\MediaWikiServices;

class Reader extends \BlueSpice\Data\DatabaseReader {

	/**
	 *
	 * @param \BlueSpice\Data\ReaderParams $params
	 * @return PrimaryDataProvider
	 */
	protected function makePrimaryDataProvider( $params ) {
		return new PrimaryDataProvider( $this->db, $this->context );
	}

	/**
	 *
	 * @return Schema
	 */
	public function getSchema() {
		return new Schema();
	}

	/**
	 *
	 * @return SecondaryDataProvider
	 */
	public function makeSecondaryDataProvider() {
		return new SecondaryDataProvider(
			MediaWikiServices::getInstance()->getLinkRenderer()
		);
	}

}
