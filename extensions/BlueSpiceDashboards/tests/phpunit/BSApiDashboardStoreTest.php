<?php

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
class BSApiDashboardStoreTest extends ApiTestCase {

	/**
	 * @covers \BSApiDashboardStore::execute
	 * @return array
	 */
	public function testMakeData() {
		$data = $this->doApiRequest( [
			'action' => 'bs-dashboards-store'
		] );

		$this->assertArrayHasKey( 'total', $data[0] );
		$this->assertArrayHasKey( 'results', $data[0] );

		// check if total is equal to results number
		$this->assertEquals( $data[0]['total'], count( $data[0]['results'] ) );

		// Ensure there is min one portlet data to test against
		$this->assertGreaterThan( 0, $data[0]['total'] );

		foreach ( $data[0]["results"] as $portlet ) {
			$this->assertArrayHasKey( 'type', $portlet );
			$this->assertArrayHasKey( 'config', $portlet );
			$this->assertArrayHasKey( 'title', $portlet["config"] );
			$this->assertArrayHasKey( 'title', $portlet );
			$this->assertArrayHasKey( 'description', $portlet );
			$this->assertArrayHasKey( 'groups', $portlet );
		}

		return $data;
	}
}
