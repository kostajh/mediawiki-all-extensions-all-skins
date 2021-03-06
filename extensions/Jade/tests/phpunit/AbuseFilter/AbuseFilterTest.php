<?php
/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace Jade\Tests\AbuseFilter;

use ApiTestCase;
use ExtensionRegistry;
use Jade\Tests\TestStorageHelper;

/**
 * Check that AbuseFilter integration works in judgment JSON.
 *
 * @group AbuseFilter
 * @group Jade
 * @group Database
 * @group medium
 *
 * @covers \AbuseFilter
 */
class AbuseFilterTest extends ApiTestCase {

	protected function setUp() : void {
		parent::setUp();

		if ( !ExtensionRegistry::getInstance()->isLoaded( 'Abuse Filter' ) ) {
			$this->markTestSkipped( 'Can only run test with AbuseFilter enabled' );
		}

		$this->tablesUsed = [
			'abuse_filter',
			'abuse_filter_history',
			'abuse_filter_log',
			'page',
		];
	}

	public function testCanFilterJudgment() {
		$this->markTestSkipped( 'fix' );
		list( $page, $revisionRecord ) = TestStorageHelper::createNewEntity(
			$this->getTestUser()->getUser()
		);

		$content = json_encode( [
			'judgments' => [ [
				'schema' => [
					'damaging' => false,
					'goodfaith' => true,
				],
				'preferred' => true,
				'notes' => 'Smash your T.V.!',
			] ],
		] );

		$judgmentResult = TestStorageHelper::makeEdit(
			$this->getTestUser()->getUser(),
			NS_JADE,
			"Diff/{$revisionRecord->getId()}",
			$content,
			'a summary'
		);

		$revisionStore = \MediaWiki\MediaWikiServices::getInstance()->getRevisionStore();
		$revisionRecord2 = $judgementResult['revision-record'];
		$rcId = $revisionStore->getRecentChange( $revisionRecord2 )->getAttribute( 'rc_id' );
		$result = $this->doApiRequest( [
			'action' => 'abusefiltercheckmatch',
			'filter' => 'added_lines irlike "\bT\.?V\.?\b"',
			'rcid' => $rcId,
		] );

		$this->assertTrue( $result[0]['abusefiltercheckmatch']['result'] );
	}

}
