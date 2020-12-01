<?php

use BlueSpice\Tests\BSApiTasksTestBase;

/*
 * Test BlueSpiceDashboards API Endpoints
 */

/**
 * @group BlueSpiceDashboards
 * @group BlueSpice
 * @group API
 * @group Database
 * @group medium
 */
class BSApiDashboardTasksTest extends BSApiTasksTestBase {

	/**
	 *
	 * @return array
	 */
	public function getTokens() {
		return $this->getTokenList( self::$users[ 'sysop' ] );
	}

	/**
	 *
	 * @return string
	 */
	protected function getModuleName() {
		return 'bs-dashboards-tasks';
	}

	/**
	 * @covers \BSApiDashboardTasks::task_saveAdminDashboardConfig
	 * @return array
	 */
	public function testSaveAdminDashboardConfig() {
		// json_encode is needed here, according to
		// BSApiDashboardTasks::task_saveUserDashboardConfig:27 (json_decode( $aPortletConfig );)
		$data = $this->executeTask(
			'saveAdminDashboardConfig', [
				'portletConfig' => [ json_encode( [ "someKey" => "someValue", "isFalse" => "true" ] ) ]
			]
		);

		$this->assertTrue( $data->success );

		return $data;
	}

	/**
	 * @covers \BSApiDashboardTasks::task_saveUserDashboardConfig
	 * @return array
	 */
	public function testSaveUserDashboardConfig() {
		$data = $this->executeTask(
			'saveUserDashboardConfig',
			[
				'portletConfig' => [ json_encode( [ "someKey" => "someValue", "isFalse" => "true" ] ) ]
			]
		);

		$this->assertTrue( $data->success );

		return $data;
	}

}
