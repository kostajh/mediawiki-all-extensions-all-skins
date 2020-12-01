<?php

namespace BlueSpice\Avatars\Privacy;

use BlueSpice\Avatars\Extension as Avatars;
use BlueSpice\Avatars\Generator;
use BlueSpice\Privacy\IPrivacyHandler;
use Database;
use Exception;
use MediaWiki\MediaWikiServices;
use Status;
use User;

class Handler implements IPrivacyHandler {
	protected $db;

	/**
	 * @param Database $db
	 */
	public function __construct( Database $db ) {
		$this->db = $db;
	}

	/**
	 * @param User $userToDelete
	 * @param User $deletedUser
	 * @return Status
	 */
	public function delete( User $userToDelete, User $deletedUser ) {
		Avatars::unsetUserImage( $userToDelete );
		return Status::newGood();
	}

	/**
	 * @param array $types Types of info users wants to retrieve
	 * @param string $format Requested output format
	 * @param User $user User to export data from
	 * @return Status
	 */
	public function exportData( array $types, $format, User $user ) {
		return Status::newGood();
	}

	/**
	 * @param string $oldUsername
	 * @param string $newUsername
	 * @return Status
	 */
	public function anonymize( $oldUsername, $newUsername ) {
		$user = User::newFromName( $oldUsername );
		$user->setName( $newUsername );

		$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'bsg' );
		$generator = new Generator( $config );
		try {
			$generator->generate( $user, [ Generator::PARAM_OVERWRITE => true ] );
		} catch ( Exception $ex ) {
			return Status::newFatal( $ex->getMessage() );
		}

		return Status::newGood();
	}
}
