<?php

use BlueSpice\Tests\BSApiTasksTestBase;

/*
 * Test BlueSpice SmartList API Endpoints
 */

/**
 * @group BlueSpiceSmartList
 * @group BlueSpice
 * @group API
 * @group Database
 * @group medium
 */
class BSApiTasksSmartListTest extends BSApiTasksTestBase {

	/**
	 *
	 * @return string
	 */
	protected function getModuleName() {
		return "bs-smartlist-tasks";
	}

	/**
	 *
	 * @covers \BSApiTasksSmartList::task_getMostActivePortlet
	 * @return array
	 */
	public function testGetMostActivePortlet() {
		$data = $this->executeTask(
		  'getMostActivePortlet', [
			'portletConfig' => [ json_encode( [] ) ]
		  ]
		);

		$this->assertTrue( $data->success );

		return $data;
	}

	/**
	 *
	 * @covers \BSApiTasksSmartList::task_getMostEditedPages
	 * @return array
	 */
	public function testGetMostEditedPages() {
		$data = $this->executeTask(
		  'getMostEditedPages', [
			'portletConfig' => [ json_encode( [] ) ]
		  ]
		);

		$this->assertTrue( $data->success );

		return $data;
	}

	/**
	 *
	 * @covers \BSApiTasksSmartList::task_gettMostVisitedPages
	 * @return array
	 */
	public function testGetMostVisitedPages() {
		$data = $this->executeTask(
		  'getMostVisitedPages', [
			'portletConfig' => [ json_encode( [] ) ]
		  ]
		);

		$this->assertTrue( $data->success );

		return $data;
	}

	/**
	 *
	 * @covers \BSApiTasksSmartList::task_getYourEditsPortlet
	 * @return array
	 */
	public function testGetYourEditsPortlet() {
		$data = $this->executeTask(
		  'getYourEditsPortlet', [
			'portletConfig' => [ json_encode( [] ) ]
		  ]
		);

		$this->assertTrue( $data->success );

		return $data;
	}

}
