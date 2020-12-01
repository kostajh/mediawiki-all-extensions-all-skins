<?php

namespace BlueSpice\Rating\Privacy;

use BlueSpice\Privacy\IPrivacyHandler;

class Handler implements IPrivacyHandler {
	/**
	 *
	 * @var \Database
	 */
	protected $db;

	/**
	 *
	 * @param \Database $db
	 */
	public function __construct( \Database $db ) {
		$this->db = $db;
	}

	/**
	 *
	 * @param string $oldUsername
	 * @param string $newUsername
	 * @return \Status
	 */
	public function anonymize( $oldUsername, $newUsername ) {
		$this->db->update(
			'bs_rating',
			[ 'rat_userip' => $newUsername ],
			[ 'rat_userip' => $oldUsername ]
		);
		return \Status::newGood();
	}

	/**
	 *
	 * @param \User $userToDelete
	 * @param \User $deletedUser
	 * @return \Status
	 */
	public function delete( \User $userToDelete, \User $deletedUser ) {
		$this->anonymize( $userToDelete->getName(), $deletedUser->getName() );

		$this->db->update(
			'bs_rating',
			[ 'rat_userid' => $deletedUser->getId() ],
			[ 'rat_userid' => $userToDelete->getId() ]
		);

		return \Status::newGood();
	}

	/**
	 *
	 * @param array $types
	 * @param string $format
	 * @param \User $user
	 * @return \Status
	 */
	public function exportData( array $types, $format, \User $user ) {
		// Where is Rating used?
		return \Status::newGood( [] );
	}
}
