<?php

namespace BlueSpice\PageAssignments\Data\Assignable\User;

use BlueSpice\Data\User\Record;
use MediaWiki\MediaWikiServices;

class PrimaryDataProvider extends \BlueSpice\Data\User\PrimaryDataProvider {

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
		parent::__construct( $db );
	}

	/**
	 *
	 * @param \stdClass $row
	 * @return null
	 */
	protected function appendRowToData( $row ) {
		if ( $this->params->getQuery() !== '' ) {
			$bApply = \BsStringHelper::filter(
				\BsStringHelper::FILTER_CONTAINS,
				$row->{Record::USER_NAME},
				$this->params->getQuery()
			) || \BsStringHelper::filter(
				\BsStringHelper::FILTER_CONTAINS,
				$row->{Record::USER_REAL_NAME},
				$this->params->getQuery()
			);
			if ( !$bApply ) {
				return;
			}
		}

		$user = \User::newFromId( $row->{Record::ID} );
		if ( !$user ) {
			return;
		}

		if ( !MediaWikiServices::getInstance()
			->getPermissionManager()
			->userCan( 'pageassignable', $user, $this->context->getTitle() )
		) {
			return;
		}
		$assignmentFactory = MediaWikiServices::getInstance()->getService(
			'BSPageAssignmentsAssignmentFactory'
		);
		$assignment = $assignmentFactory->factory(
			'user',
			$row->{Record::USER_NAME},
			$this->context->getTitle()
		);
		if ( !$assignment instanceof \BlueSpice\PageAssignments\IAssignment ) {
			// :(
			return;
		}

		$this->data[] = $assignment->getRecord();
	}
}
