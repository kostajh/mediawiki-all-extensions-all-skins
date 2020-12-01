<?php

/**
 * @group Broken
 * @group Database
 * @group BlueSpice
 * @group BlueSpiceBookmaker
 * @group BlueSpiceBookshelf
 * @group API
 * @group medium
 *
 * @covers ApiBookshelfStore
 */
class ApiBookshelfStoreTest extends ApiTestCase {
	protected $tablesUsed = [ 'page' ];

	protected $dummyBooks = [
		'Book:Hallo Welt!' => '',
		'Book:ტყაოსანი შოთა რუსთაველი' => '',
		'Book:ካለኝ እንደአባቴ በቆመጠኝ።' => '',
		'Book:Конференция соберет широкий' => '',
		'Book:204 § ab dem Jahr 2034 Zahlen in 86 der Texte zur Pflicht werden' => '',
		'Book:テスト' => '',
		'Book:Tamil poetry of Subramaniya Bharathiyar: சுப்ரமணிய பாரதியார் (1882-1921) n' => '',
		'User:Apitestsysop/Books/Hallo Welt!' => '<bs:bookmeta />',
		'User:UTSysop/Books/Hallo Welt!' => '<bs:bookmeta />',
		'User:UTSysop/Books/Hello World! n' => '<bs:bookmeta />',
		'User:UTSysop/Books/ტყაოსანი შოთა რუსთაველი' => '<bs:bookmeta />',
		'User:UTSysop/Books/ካለኝ እንደአባቴ በቆመጠኝ።' => '<bs:bookmeta />',
		'User:UTSysop/Books/Конференция соберет широкий' => '<bs:bookmeta />',
	];

	/*
	 * Hint: phpunit test is running with user UTSysop -> only UTSysops personal books delivered
	 * by store!
	 */

	protected function createStoreDummyData() {
		foreach ( $this->dummyBooks as $dummyBook => $content ) {
			$this->insertPage( $dummyBook, $content );
		}
	}

	public function addDBData() {
		$this->createStoreDummyData();
	}

	/**
	 * @param $limit
	 * @param $offset
	 *
	 * @dataProvider providePagingData
	 */
	public function testPaging( $limit, $offset ) {
		$results = $this->doApiRequest( [
			'action' => 'bs-bookshelf-store',
			'limit' => $limit,
			'offset' => $offset
		] );
		$response = $results[0];

		$this->assertLessThanOrEqual(
			count( $this->dummyBooks ),
			'total',
			(object)$response,
			'Field "total" contains wrong value'
		);

		$this->assertLessThanOrEqual( $limit, count( $response['results'] ),
			'Number of results exceeds limit' );
	}

	public function providePagingData() {
		return [
			[ 2, 0 ],
			[ 2, 2 ],
			[ 2, 4 ],
			[ 4, 0 ],
			[ 4, 4 ],
			[ 4, 8 ]
		];
	}

	/**
	 * [
	 * 	{
	 * 		"type":"string",
	 * 		"comparison":"ct",
	 * 		"value":"some text ...",
	 * 		"field":"someField"
	 * 	}
	 * ]
	 *
	 * @param $type
	 * @param $field
	 * @param $value
	 * @param $comparison
	 * @param $expectedTotal
	 *
	 * @dataProvider provideSingleFilterData
	 */
	public function testSingleFilter( $type, $comparison, $field, $value, $expectedTotal ) {
		$results = $this->doApiRequest( [
			'action' => 'bs-bookshelf-store',
			'filter' => FormatJson::encode( [
				[
					'type' => $type,
					'comparison' => $comparison,
					'field' => $field,
					'value' => $value
				]
			] )
		] );

		$response = $results[0];

		$this->assertSame(
			$expectedTotal,
			$response['total'],
			'Field "total" contains wrong value'
		);
	}

	public function provideSingleFilterData() {
		return [
			[ 'string', 'ct', 'book_displaytext', 'Hal', 2 ],
		];
	}

	/**
	 * @param $filters
	 * @param $expectedTotal
	 *
	 * @dataProvider provideMultipleFilterData
	 */
	public function testMultipleFilter( $filters, $expectedTotal ) {
		$results = $this->doApiRequest( [
			'action' => 'bs-bookshelf-store',
			'filter' => FormatJson::encode( $filters )
		] );

		$response = $results[0];

		$this->assertSame(
			$expectedTotal,
			$reponse['total'],
			'Field "total" contains wrong value'
		);
	}

	public function provideMultipleFilterData() {
		return [
			[
				[
					[
						'type' => 'string',
						'comparison' => 'eq',
						'field' => 'book_type',
						'value' => 'user_book'
					],
					[
						'type' => 'string',
						'comparison' => 'ct',
						'field' => 'book_displaytext',
						'value' => 'H'
					]
				],
				2
			],
			[
				[
					[
						'type' => 'string',
						'comparison' => 'eq',
						'field' => 'book_type',
						'value' => 'ns_book'
					],
					[
						'type' => 'string',
						'comparison' => 'nct',
						'field' => 'book_displaytext',
						'value' => 'a'
					]
				],
				4
			],
			[
				[
					[
						'type' => 'numeric',
						'comparison' => 'eq',
						'field' => 'page_namespace',
						'value' => NS_BOOK
					],
					[
						'type' => 'string',
						'comparison' => 'ew',
						'field' => 'book_prefixedtext',
						'value' => 'n'
					]
				],
				2
			]
		];
	}
}
