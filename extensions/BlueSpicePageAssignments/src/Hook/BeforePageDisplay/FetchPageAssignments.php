<?php

namespace BlueSpice\PageAssignments\Hook\BeforePageDisplay;

use BlueSpice\PageAssignments\IAssignment;
use BlueSpice\PageAssignments\Renderer\Assignment;

class FetchPageAssignments extends \BlueSpice\Hook\BeforePageDisplay {

	protected function skipProcessing() {
		if ( $this->out->getTitle()->getArticleID() < 1 ) {
			return true;
		}

		$factory = $this->getServices()->getService(
			'BSPageAssignmentsAssignmentFactory'
		);
		if ( !$factory->newFromTargetTitle( $this->out->getTitle() ) ) {
			return true;
		}
		$assignments = $factory->newFromTargetTitle(
			$this->out->getTitle()
		)->getAssignments();
		if ( count( $assignments ) < 1 ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$factory = $this->getServices()->getService(
			'BSPageAssignmentsAssignmentFactory'
		);
		$target = $factory->newFromTargetTitle( $this->out->getTitle() );

		$assignments = [];
		foreach ( $target->getAssignments() as $assignment ) {
			if ( !isset( $assignments[$assignment->getType()] ) ) {
				$assignments[$assignment->getType()] = [];
			}
			$assignments[$assignment->getType()][] = $this->makeEntry(
				$assignment
			);
		}

		$this->out->addJsConfigVars( [
			'bsgPageAssignmentsSitetools' => $assignments
		] );

		return true;
	}

	/**
	 *
	 * @param IAssignment $assignment
	 * @return \stdClass
	 */
	protected function makeEntry( IAssignment $assignment ) {
		$stdClass = $assignment->toStdClass();
		$stdClass->html = '';
		$factory = $this->getServices()->getService(
			'BSPageAssignmentsAssignableFactory'
		);
		$assignable = $factory->factory( $assignment->getType() );
		$renderer = $this->getServices()->getService( 'BSRendererFactory' )->get(
			$assignable->getRendererKey(),
			new \BlueSpice\Renderer\Params( [
				Assignment::PARAM_ASSIGNMENT => $assignment
			] )
		);
		$stdClass->html = $renderer->render();
		return $stdClass;
	}
}
