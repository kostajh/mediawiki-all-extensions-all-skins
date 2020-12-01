<?php

/*
 * Test BSApiInsertFileLicenseStore API Endpoints
 */

/**
 * @group BlueSpiceDashboards
 * @group BlueSpice
 * @group API
 * @group Database
 * @group medium
 */
class BSApiInsertFileLicenseStoreTest extends ApiTestCase {

	/**
	 * @covers \BSApiInsertFileLicenseStore::execute
	 * @return array
	 */
	public function testMakeData() {
		$data = $this->doApiRequest( [
			'action' => 'bs-insertfile-license-store'
		] );

		$this->assertArrayHasKey( 'total', $data[0] );
		$this->assertArrayHasKey( 'results', $data[0] );

		return $data;
	}
}
