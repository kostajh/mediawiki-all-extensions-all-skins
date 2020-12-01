<?php

namespace BlueSpice\PageAssignments\Data\Assignable\Everyone;

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
	 * @var \BlueSpice\Data\ReaderParams
	 */
	protected $params = null;

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
		$this->context = $context;
		$this->db = $db;
	}

	/**
	 *
	 * @param \BlueSpice\Data\ReaderParams $params
	 * @return array
	 */
	public function makeData( $params ) {
		$this->params = $params;
		$this->data = [];

		$this->appendRowToData();

		return $this->data;
	}

	/**
	 *
	 * @return null
	 */
	protected function appendRowToData() {
		$assignmentFactory = MediaWikiServices::getInstance()->getService(
			'BSPageAssignmentsAssignmentFactory'
		);

		$assignment = $assignmentFactory->factory(
			'specialeveryone',
			'everyone',
			$this->context->getTitle()
		);
		if ( !$assignment instanceof \BlueSpice\PageAssignments\IAssignment ) {
			// :(
			return;
		}
		if ( $this->params->getQuery() !== '' ) {
			$bApply = \BsStringHelper::filter(
				\BsStringHelper::FILTER_CONTAINS,
				$assignment->getKey(),
				$this->params->getQuery()
			) || \BsStringHelper::filter(
				\BsStringHelper::FILTER_CONTAINS,
				$assignment->getText(),
				$this->params->getQuery()
			);
		}

		$this->data[] = $assignment->getRecord();
	}
}
