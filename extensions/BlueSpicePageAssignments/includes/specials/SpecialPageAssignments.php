<?php

use BlueSpice\Special\ManagerBase;
use MediaWiki\MediaWikiServices;

class SpecialPageAssignments extends ManagerBase {
	/**
	 *
	 * @param string $name
	 * @param string $restriction
	 * @param bool $listed
	 * @param bool $function
	 * @param string $file
	 * @param bool $includable
	 */
	public function __construct( $name = '', $restriction = '', $listed = true,
		$function = false, $file = 'default', $includable = false ) {
		parent::__construct(
			'PageAssignments',
			$restriction,
			$listed,
			$function,
			$file,
			$includable
		);
	}

	/**
	 * @return string ID of the HTML element being added
	 */
	protected function getId() {
		return 'bs-pageassignments-overview';
	}

	/**
	 * @return array
	 */
	protected function getModules() {
		return [
			'ext.pageassignments.overview'
		];
	}

	/**
	 *
	 * @return array
	 */
	protected function getJSVars() {
		$aDeps = [];
		MediaWikiServices::getInstance()->getHookContainer()->run( 'BSPageAssignmentsOverview', [
			$this,
			&$aDeps
		] );
		return [
			'bsPageAssignmentsOverviewDeps' => $aDeps
		];
	}
}
