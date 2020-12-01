<?php
$IP = dirname( dirname( dirname( __DIR__ ) ) );
require_once $IP . '/extensions/BlueSpiceFoundation/maintenance/BSMaintenance.php';

use BlueSpice\Data\ReaderParams;
use BlueSpice\PageAssignments\Data\Record;
use MediaWiki\MediaWikiServices;

class ManageAssignments extends BSMaintenance {

	public function __construct() {
		parent::__construct();

		$this->addOption( 'action', 'list|set', true, true );
		$this->addOption( 'pagelist', 'File with page titles', false, true );
		$this->addOption( 'assignments', 'File with assignments', false, true );
	}

	public function execute() {
		$action = $this->getOption( 'action' );
		if ( $action === 'list' ) {
			$this->listAssignments();
		}
		if ( $action === 'set' ) {
			$this->setAssignments();
		}
	}

	private function listAssignments() {
		$factory = $this->getFactory();
		$recordSet = $factory->getStore()->getReader()->read(
			new ReaderParams( [
				ReaderParams::PARAM_LIMIT => ReaderParams::LIMIT_INFINITE
			] )
		);
		$assignments = [];
		foreach ( $recordSet->getRecords() as $record ) {
			$id = $record->get( Record::PAGE_ID );

			$title = Title::newFromID( $id );
			if ( $title === null ) {
				$this->error( "Could not create Title for '$id'!" );
				continue;
			}

			$titleText = $title->getPrefixedDBkey();
			if ( !isset( $assignments[$titleText] ) ) {
				$assignments[$titleText] = [];
			}

			$assignments[$titleText][] =
				$record->get( Record::ASSIGNEE_TYPE ) . '/' . $record->get( Record::ASSIGNEE_KEY );
		}

		foreach ( $assignments as $pageName => $assignmentEntries ) {
			$this->output( "\n$pageName" );
			foreach ( $assignmentEntries as $assignmentEntry ) {
				$this->output( "\t--> $assignmentEntry" );
			}
		}
	}

	private function setAssignments() {
		$titles = $this->makeTitles();

		foreach ( $titles as $title ) {
			$this->output( "Processing '{$title->getPrefixedDBkey()}'..." );
			$target = $this->getFactory()->newFromTargetTitle( $title );
			if ( $target instanceof \BlueSpice\PageAssignments\ITarget == false ) {
				continue;
			}
			$assignments = $this->makeAssignments( $title );
			$status = $target->save( $assignments );
			if ( $status->isOK() ) {
				$this->output( '--> DONE.' );
			} else {
				$this->error( '--> ERROR: ' . $status->getMessage() );
			}
		}
	}

	/**
	 *
	 * @return \BlueSpice\PageAssignments\AssignmentFactory
	 */
	private function getFactory() {
		return MediaWikiServices::getInstance()->getService(
			'BSPageAssignmentsAssignmentFactory'
		);
	}

	/**
	 * @param Title $title
	 * @return \BlueSpice\PageAssignments\IAssignment
	 */
	private function makeAssignments( $title ) {
		$assignmentsDef = $this->getOption( 'assignments' );
		$content = file_get_contents( $assignmentsDef );
		$assignmentsList = FormatJson::decode( $content );
		$factory = $this->getFactory();
		$assignments = [];

		foreach ( $assignmentsList as $assignmentsId ) {
			$kvPair = explode( '/', $assignmentsId, 2 );
			$type = $kvPair[0];
			$key = $kvPair[1];
			$assignment = $factory->factory( $type, $key, $title );
			if ( $assignment instanceof \BlueSpice\PageAssignments\IAssignment === false ) {
				$this->error(
					"Could not create assignment for '$assignmentsId' "
						. "on '{$title->getPrefixedDBkey()}'!"
				);
				continue;
			}
			$assignments[] = $assignment;
		}

		return $assignments;
	}

	/**
	 * @return Title
	 */
	private function makeTitles() {
		$pagelist = $this->getOption( 'pagelist' );
		$content = file_get_contents( $pagelist );
		$lines = explode( "\n", $content );
		$titles = [];
		foreach ( $lines as $line ) {
			$trimmedLine = trim( $line );
			if ( empty( $trimmedLine ) ) {
				continue;
			}
			$title = Title::newFromText( $trimmedLine );
			if ( $title instanceof Title === false ) {
				$this->error( "Could not create valid title from '$trimmedLine'!" );
			}
			$titles[] = $title;
		}
		return $titles;
	}

}

$maintClass = 'ManageAssignments';
if ( defined( 'RUN_MAINTENANCE_IF_MAIN' ) ) {
	require_once RUN_MAINTENANCE_IF_MAIN;
} else {
	# Make this work on versions before 1.17
	require_once RUN_MAINTENANCE_IF_MAIN;
}
