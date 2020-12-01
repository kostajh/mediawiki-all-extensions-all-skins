<?php

namespace BlueSpice\Readers\Data\RevisionReaders;

use BlueSpice\Data\ReaderParams;
use IContextSource;
use MediaWiki\MediaWikiServices;
use RequestContext;

class Reader extends \BlueSpice\Data\DatabaseReader {

	/**
	 *
	 * @param ReaderParams $params
	 * @return PrimaryDataProvider
	 */
	protected function makePrimaryDataProvider( $params ) {
		return new PrimaryDataProvider( $this->db, $this->context );
	}

	/**
	 *
	 * @return \BlueSpice\Readers\Data\Schema
	 */
	public function getSchema() {
		return new \BlueSpice\Readers\Data\Schema();
	}

	/**
	 *
	 * @return SecondaryDataProvider
	 */
	public function makeSecondaryDataProvider() {
		if ( $this->context instanceof IContextSource ) {
			$language = $this->context->getLanguage();
		} else {
			$language = RequestContext::getMain()->getLanguage();
		}
		return new SecondaryDataProvider(
			$language,
			MediaWikiServices::getInstance()->getRevisionLookup()
		);
	}

}
