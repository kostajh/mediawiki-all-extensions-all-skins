<?php

namespace BlueSpice\NamespaceCSS\Data;

use BlueSpice\NamespaceCSS\Helper;

class PrimaryDataProvider implements \BlueSpice\Data\IPrimaryDataProvider {

	/**
	 *
	 * @var \BlueSpice\Data\Record[]
	 */
	protected $data = [];

	/**
	 *
	 * @var \Wikimedia\Rdbms\IDatabase
	 */
	protected $db = null;

	/**
	 *
	 * @var \IContextSource
	 */
	protected $context = null;

	/**
	 *
	 * @param \Wikimedia\Rdbms\IDatabase $db
	 * @param \IContextSource $context
	 */
	public function __construct( $db, $context ) {
		$this->db = $db;
		$this->context = $context;
	}

	/**
	 *
	 * @param \BlueSpice\Data\ReaderParams $params
	 * @return array
	 */
	public function makeData( $params ) {
		$this->data = [];

		foreach ( $this->context->getLanguage()->getNamespaces() as $idx => $ns ) {
			if ( \MWNamespace::isTalk( $idx ) ) {
				continue;
			}
			$namespace = Helper::buildNamespaceNameFromNamespaceIndex( $idx );
			if ( !$namespace ) {
				continue;
			}
			$title = Helper::buildTitleFromNamespaceIndex( $idx );
			if ( !$title ) {
				continue;
			}
			$row = (object)[
				Record::NAMESPACE_ID => $idx,
				Record::NAMESPACE_NAME => $namespace,
				Record::SOURCE_PAGE => $title->getFullText(),
			];
			$this->appendRowToData( $row );
		}

		return $this->data;
	}

	/**
	 *
	 * @param \stdClass $row
	 */
	protected function appendRowToData( $row ) {
		$this->data[] = new Record( $row );
	}
}
