<?php

namespace BlueSpice\Authors\Data\PageAuthors;

use BlueSpice\Data\NoWriterException;
use IContextSource;
use MediaWiki\MediaWikiServices;

class Store implements \BlueSpice\Data\IStore {

	/**
	 *
	 * @var IContextSource
	 */
	protected $context = null;

	/**
	 *
	 * @param IContextSource $context
	 */
	public function __construct( $context ) {
		$this->context = $context;
	}

	/**
	 *
	 * @return Reader
	 */
	public function getReader() {
		return new Reader(
			MediaWikiServices::getInstance()->getDBLoadBalancer(),
			$this->context,
			MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'bsg' )
		);
	}

	/**
	 *
	 * @throws NoWriterException
	 */
	public function getWriter() {
		throw new NoWriterException();
	}
}
