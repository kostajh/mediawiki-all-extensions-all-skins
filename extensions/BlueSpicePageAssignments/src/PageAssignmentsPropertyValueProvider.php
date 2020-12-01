<?php

namespace BlueSpice\PageAssignments;

use BlueSpice\SMWConnector\PropertyValueProvider;
use MediaWiki\MediaWikiServices;
use SMW\DIWikiPage;
use User;

class PageAssignmentsPropertyValueProvider extends PropertyValueProvider {

	/**
	 *
	 * @var AssignmentFactory
	 */
	private $assignmentFactory = null;

	/**
	 *
	 * @param AssignmentFactory|null $assignmentFactory
	 */
	public function __construct( $assignmentFactory = null ) {
		$this->assignmentFactory = $assignmentFactory;
	}

	/**
	 *
	 * @param AssignmentFactory|null $factory
	 * @return \BlueSpice\SMWConnector\IPropertyValueProvider[]
	 */
	public static function factory( AssignmentFactory $factory = null ) {
		// We do not create the service for injection here, because it requires
		// an instance of LinkRenderer and will create it from the services.
		// The LinkRenderer requires the user object to be initialized which it
		// is not in context of the wgExtensionFunctions callbacks the registry
		// is running in. This leads to bad issues such as the user options can
		// not be saved anymore.
		// Services should used with caution before everything is initialized.
		// This needs a follow up change in the AssignmentFactory service some day,
		// which seperates the AssignmentFactory into a raw data factory which has an
		// assignment type renderer with all the advanced things like link
		// rendering and such

		return [ new self( $factory ) ];
	}

	/**
	 *
	 * @return string
	 */
	public function getAliasMessageKey() {
		return "bs-pageassignments-sesp-pageassignments-alias";
	}

	/**
	 *
	 * @return string
	 */
	public function getDescriptionMessageKey() {
		return "bs-pageassignments-sesp-pageassignments-desc";
	}

	/**
	 *
	 * @return string
	 */
	public function getId() {
		return '_PAGEASSIGN';
	}

	/**
	 *
	 * @return string
	 */
	public function getLabel() {
		return "PageAssignments";
	}

	/**
	 *
	 * @param \SESP\AppFactory $appFactory
	 * @param \SMW\DIProperty $property
	 * @param \SMW\SemanticData $semanticData
	 * @return null
	 */
	public function addAnnotation( $appFactory, $property, $semanticData ) {
		$title = $semanticData->getSubject()->getTitle();
		if ( $title === null ) {
			return null;
		}

		if ( !$this->assignmentFactory ) {
			$this->assignmentFactory = MediaWikiServices::getInstance()->getService(
				'BSPageAssignmentsAssignmentFactory'
			);
		}
		$target = $this->assignmentFactory->newFromTargetTitle( $title );
		if ( $target === false ) {
			return null;
		}

		$userIds = $target->getAssignedUserIDs();
		foreach ( $userIds as $userId ) {
			$user = User::newFromId( $userId );
			$dataItem = DIWikiPage::newFromTitle( $user->getUserPage() );
			$semanticData->addPropertyObjectValue( $property, $dataItem );
		}

		return null;
	}
}
