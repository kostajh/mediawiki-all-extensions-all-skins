<?php

namespace BlueSpice\ReadConfirmation\Privacy;

use BlueSpice\Privacy\IPrivacyHandler;
use BlueSpice\Privacy\Module\Transparency;
use MediaWiki\MediaWikiServices;

class Handler implements IPrivacyHandler {
	protected $db;
	protected $language;

	/**
	 *
	 * @param \Database $db
	 */
	public function __construct( \Database $db ) {
		$this->db = $db;
		$this->language = \RequestContext::getMain()->getLanguage();
	}

	/**
	 *
	 * @param string $oldUsername
	 * @param string $newUsername
	 * @return \Status
	 */
	public function anonymize( $oldUsername, $newUsername ) {
		return \Status::newGood();
	}

	/**
	 *
	 * @param \User $userToDelete
	 * @param \User $deletedUser
	 * @return \Status
	 */
	public function delete( \User $userToDelete, \User $deletedUser ) {
		$this->db->update(
			'bs_readconfirmation',
			[ 'rc_user_id' => $deletedUser->getId() ],
			[ 'rc_user_id' => $userToDelete->getId() ]
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
			'bs_readconfirmation',
			'*',
			[ 'rc_user_id' => $user->getId() ]
		);

		$data = [];
		foreach ( $res as $row ) {
			$lookup = MediaWikiServices::getInstance()->getRevisionLookup();
			$rev = $lookup->getRevisionById( $row->rc_rev_id );
			if ( !$rev ) {
				continue;
			}
			$title = \Title::newFromID( $rev->getPageId() );
			if ( !$title ) {
				continue;
			}

			$timestamp = $this->language->userTimeAndDate(
				$row->rc_timestamp,
				$user
			);

			$data[] = wfMessage(
				'bs-readconfirmation-privacy-transparency-working-rc',
				$title->getPrefixedText(),
				$rev->getId(),
				$timestamp
			)->plain();
		}

		return \Status::newGood( [
			Transparency::DATA_TYPE_WORKING => $data
		] );
	}
}
