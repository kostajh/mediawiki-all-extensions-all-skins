<?php

namespace PasswordlessLogin\adapter;

use PasswordlessLogin\model\Challenge;
use PasswordlessLogin\model\ChallengesRepository;
use User;
use Wikimedia\Rdbms\ILoadBalancer;
use Wikimedia\Rdbms\LoadBalancer;

class DatabaseChallengesRepository implements ChallengesRepository {
	const TABLE_NAME = 'passwordlesslogin_challenges';
	private $FETCH_FIELDS = [ 'challenge', 'challenge_user_id', 'success' ];
	private $loadBalancer;

	/**
	 * DatabaseChallengesRepository constructor.
	 * @param LoadBalancer $loadBalancer
	 */
	public function __construct( LoadBalancer $loadBalancer ) {
		$this->loadBalancer = $loadBalancer;
	}

	/**
	 * @inheritDoc
	 */
	public function save( Challenge $challenge ) {
		if ( $this->findByChallenge( $challenge->getChallenge() ) === null ) {
			$this->insertChallenge( $challenge );
		} else {
			$this->updateChallenge( $challenge );
		}
	}

	private function insertChallenge( Challenge $challenge ) {
		$dbw = $this->loadBalancer->getConnection( DB_MASTER );
		$dbw->insert( self::TABLE_NAME, [
			'challenge' => $challenge->getChallenge(),
			'challenge_user_id' => $challenge->getUserId(),
			'success' => (int)$challenge->getSuccess()
		] );
	}

	private function updateChallenge( Challenge $challenge ) {
		$dbw = $this->loadBalancer->getConnection( DB_MASTER );
		$dbw->update( self::TABLE_NAME, [
			'challenge_user_id' => $challenge->getUserId(),
			'success' => (int)$challenge->getSuccess(),
		], [
			'challenge' => $challenge->getChallenge(),
		] );
	}

	/**
	 * @inheritDoc
	 */
	public function findByChallenge( $challenge ) {
		$dbr = $this->loadBalancer->getConnection( DB_MASTER, [], false,
			ILoadBalancer::CONN_TRX_AUTOCOMMIT );
		$result =
			$dbr->select( self::TABLE_NAME, $this->FETCH_FIELDS, [ 'challenge' => $challenge ] );

		if ( $result->numRows() !== 1 ) {
			return null;
		}

		$challenge = $result->fetchObject();

		return new Challenge( (int)$challenge->challenge_user_id, $challenge->challenge,
			(bool)$challenge->success );
	}

	/**
	 * @inheritDoc
	 */
	public function findByUser( User $user ) {
		$dbr = $this->loadBalancer->getConnection( DB_MASTER );
		$result = $dbr->select( self::TABLE_NAME, $this->FETCH_FIELDS, [
			'challenge_user_id' => $user->getId(),
		] );

		if ( $result->numRows() !== 1 ) {
			return null;
		}

		$challenge = $result->fetchObject();

		return new Challenge( (int)$challenge->challenge_user_id, $challenge->challenge,
			(bool)$challenge->success );
	}

	/**
	 * @inheritDoc
	 */
	public function remove( User $user ) {
		$dbw = $this->loadBalancer->getConnection( DB_MASTER );
		$dbw->delete( self::TABLE_NAME, [
			'challenge_user_id' => $user->getId(),
		] );
	}
}
