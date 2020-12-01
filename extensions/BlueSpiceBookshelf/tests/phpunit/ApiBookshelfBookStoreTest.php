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
 * @covers ApiBookshelfBookStore
 */
class ApiBookshelfBookStoreTest extends ApiTestCase {
	protected $tablesUsed = [ 'page' ];

	protected $dummyBookContent = <<<HERE
* [[A|a]]
** [[A/A|aa]]
*** [[A/A/A|aaa]]
*** [[A/A/B|aab]]
**** [[A/A/B/A|aaba]]
**** [[A/A/B/B|aabb]]
** [[A/B|ab]]
** [[A/C|ac]]
*** [[A/C/A|aca]]
*** [[A/C/B|acb]]
* [[B|b]]
** [[B/A|ba]]
*** [[B/A/A|baa]]
*** [[B/A/B|bab]]
*** [[B/A/C|bac]]
*** [[B/A/D|bad]]
*** [[B/A/E|bae]]
**** [[B/A/E/A|baea]]
*** [[B/A/F|baf]]
HERE;

	public function addDBData() {
		$this->insertPage( 'Book:Test', $this->dummyBookContent );
	}

	/**
	 * @param $path
	 * @param $expectedTotal
	 *
	 * @dataProvider provideNodePathsData
	 */
	public function testNodePaths( $path, $expectedTotal ) {
		$results = $this->doApiRequest( [
			'action' => 'bs-bookshelf-bookstore',
			'node' => $path,
			'book' => 'Book:Test'
		] );
		$response = $results[0];

		$this->assertSame(
			$expectedTotal,
			$response['total'],
			'Field "total" contains wrong value'
		);
	}

	public function provideNodePathsData() {
		return [
			[ '', 2 ],
			[ '1', 3 ],
			[ '1.1', 2 ],
			[ '1.1.2', 2 ],
			[ '2', 1 ],
			[ '2.1', 6 ],
		];
	}
}
