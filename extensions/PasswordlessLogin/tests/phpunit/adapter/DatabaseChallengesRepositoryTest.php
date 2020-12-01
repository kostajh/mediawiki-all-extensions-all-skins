<?php

namespace PasswordlessLogin\adapter;

use MediaWiki\MediaWikiServices;
use MediaWikiTestCase;
use PasswordlessLogin\model\Challenge;
use User;

/**
 * @group Database
 */
class DatabaseChallengesRepositoryTest extends MediaWikiTestCase {
	/**
	 * @var DatabaseChallengesRepository
	 */
	private $repository;

	protected function setUp() : void {
		parent::setUp();
		$this->tablesUsed[] = 'passwordlesslogin_challenges';

		$this->repository =
			new DatabaseChallengesRepository( MediaWikiServices::getInstance()
				->getDBLoadBalancer() );
		$this->db->delete( 'passwordlesslogin_challenges', '*' );
	}

	/**
	 * @covers \PasswordlessLogin\adapter\DatabaseChallengesRepository::save
	 * @covers \PasswordlessLogin\adapter\DatabaseChallengesRepository::findByChallenge
	 */
	public function testPersistsData() {
		$user = User::newFromName( 'UTSysop' );
		$challenge = Challenge::forUser( $user );

		$this->repository->save( $challenge );
		$result = $this->repository->findByChallenge( $challenge->getChallenge() );

		$this->assertEquals( $challenge, $result );
	}

	/**
	 * @covers \PasswordlessLogin\adapter\DatabaseChallengesRepository::remove
	 */
	public function testRemovesEntries() {
		$user = User::newFromName( 'UTSysop' );
		$challenge = Challenge::forUser( $user );
		$this->repository->save( $challenge );

		$this->repository->remove( $user );

		$this->assertNull( $this->repository->findByChallenge( $challenge->getChallenge() ) );
	}

	/**
	 * @covers \PasswordlessLogin\adapter\DatabaseChallengesRepository::save
	 * @covers \PasswordlessLogin\adapter\DatabaseChallengesRepository::findByChallenge
	 */
	public function testUpdatesExistingEntry() {
		$challenge = Challenge::forUser( User::newFromName( 'UTSysop' ) );
		$this->repository->save( $challenge );
		$challenge->setSuccess( true );

		$this->repository->save( $challenge );
		$result = $this->repository->findByChallenge( $challenge->getChallenge() );

		$this->assertEquals( $challenge, $result );
	}
}
