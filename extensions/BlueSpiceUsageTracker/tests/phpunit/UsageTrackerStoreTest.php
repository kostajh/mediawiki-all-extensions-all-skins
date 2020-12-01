<?php

use BlueSpice\Tests\BSApiExtJSStoreTestBase;

/**
 * @group medium
 * @group Api
 * @group Database
 * @group BlueSpice
 * @group BlueSpiceExtensions
 * @group BlueSpiceUsageTracker
 */
class UsageTrackerStoreTest extends BSApiExtJSStoreTestBase {
	protected $iFixtureTotal = 3;
	protected $tablesUsed = [ 'bs_usagetracker' ];

	protected function skipAssertTotal() {
		return true;
	}

	protected function getStoreSchema() {
		return [
			'count' => [
				'type' => 'string'
			],
			'descriptionKey' => [
				'type' => 'string'
			],
			'identifier' => [
				'type' => 'string'
			],
			'type' => [
				'type' => 'string'
			],
			'description' => [
				'type' => 'string'
			],
			'updateDate' => [
				'type' => 'string'
			]
		];
	}

	protected function createStoreFixtureData() {
	}

	/**
	 *
	 * @return int
	 */
	public function addDBData() {
		$aFixtureData = [ [
			'ut_identifier' => 'dummy',
			'ut_count' => 2,
			'ut_type' => 'BS\UsageTracker\Collectors\Property',
			'ut_timestamp' => wfTimestampNow()
		], [
			'ut_identifier' => 'dummy2',
			'ut_count' => 4,
			'ut_type' => 'BS\UsageTracker\Collectors\Property',
			'ut_timestamp' => wfTimestampNow()
		], [
			'ut_identifier' => 'test',
			'ut_count' => 8,
			'ut_type' => 'BS\UsageTracker\Collectors\Property',
			'ut_timestamp' => wfTimestampNow()
		] ];

		$this->db->insert(
			'bs_usagetracker',
			$aFixtureData,
			__METHOD__
		);

		return 3;
	}

	/**
	 *
	 * @return string
	 */
	protected function getModuleName() {
		return 'bs-usagetracker-store';
	}

	/**
	 *
	 * @return array
	 */
	public function provideSingleFilterData() {
		return [
			'Filter by identifier' => [ 'string', 'eq', 'identifier', 'dummy', 1 ],
			'Filter by count' => [ 'string', 'eq', 'count', '2', 1 ]
		];
	}

	/**
	 *
	 * @return array
	 */
	public function provideMultipleFilterData() {
		return [
			'Filter by identifier and count' => [
				[
					[
						'type' => 'string',
						'comparison' => 'ct',
						'field' => 'identifier',
						'value' => 'dummy'
					],
					[
						'type' => 'string',
						'comparison' => 'eq',
						'field' => 'count',
						'value' => '2'
					]
				],
				1
			]
		];
	}

	/**
	 *
	 * @return array
	 */
	public function provideKeyItemData() {
		return [
			[ 'identifier', 'test' ],
			[ 'identifier', 'dummy' ],
			[ 'count', '8' ]
		];
	}
}
