<?php

namespace BlueSpice\PageAssignments\Privacy;

use BlueSpice\Privacy\IPrivacyHandler;
use BlueSpice\Privacy\Module\Transparency;

class Handler implements IPrivacyHandler {
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
			'bs_pageassignments',
			[ 'pa_assignee_key' => $newUsername ],
			[
				'pa_assignee_key' => $oldUsername,
				'pa_assignee_type' => 'user'
			]
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
		$this->db->delete(
			'bs_pageassignments',
			[ 'pa_assignee_key' => $userToDelete->getName() ]
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
		if ( !in_array( Transparency::DATA_TYPE_WORKING, $types ) ) {
			return \Status::newGood( [] );
		}
		$res = $this->db->select(
			'bs_pageassignments',
			[ 'pa_page_id' ],
			[ 'pa_assignee_key' => $user->getName() ]
		);

		$titles = [];
		foreach ( $res as $row ) {
			$title = \Title::newFromID( $row->pa_page_id );
			if ( $title instanceof \Title === false ) {
				continue;
			}
			$titles[] = $title->getPrefixedText();
		}

		if ( empty( $titles ) ) {
			return \Status::newGood( [] );
		}

		return \Status::newGood( [
			Transparency::DATA_TYPE_WORKING => [
				wfMessage(
					'bs-pageassignments-privacy-transparency-working-assignments',
					implode( ', ', $titles ),
					count( $titles )
				)->plain()
			]
		] );
	}
}
