<?php

namespace BlueSpice\PageAssignments\Data\Assignable;

use BlueSpice\PageAssignments\Data\Record;
use MediaWiki\MediaWikiServices;

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

		$title = $this->context->getTitle();
		if ( !$title ) {
			throw new \MWException( "Missing assignable title" );
		}
		if ( $title->getArticleID() < 1 ) {
			throw new \MWException(
				"Not an assignable title: '{$title->getFullText()}'"
			);
		}
		$assignableFactory = MediaWikiServices::getInstance()->getService(
			'BSPageAssignmentsAssignableFactory'
		);

		$activatedTypes = $this->context->getConfig()->get(
			'PageAssignmentsActivatedTypes'
		);

		foreach ( $assignableFactory->getRegisteredTypes() as $type ) {
			if ( !in_array( $type, $activatedTypes ) ) {
				continue;
			}
			$assignable = $assignableFactory->factory(
				$type,
				$this->context
			);
			$recordSet = $assignable->getStore()->getReader()->read( $params );
			foreach ( $recordSet->getRecords() as $record ) {
				$this->appendRowToData( $record );
			}
		}

		return $this->data;
	}

	/**
	 *
	 * @param Record $record
	 */
	protected function appendRowToData( Record $record ) {
		$this->data[] = $record;
	}
}
