<?php

namespace BlueSpice\SaferEdit;

use BlueSpice\ExtensionAttributeBasedRegistry;
use IContextSource;
use Status;
use Title;
use User;
use Wikimedia\Rdbms\IDatabase;

class SaferEditManager {
	/**
	 * @var IDatabase
	 */
	protected $db;

	/**
	 * @var IContextSource
	 */
	protected $context;

	/**
	 * @var ExtensionAttributeBasedRegistry
	 */
	protected $checkerRegistry;

	/**
	 * @param IDatabase $db
	 * @param IContextSource $context
	 * @param ExtensionAttributeBasedRegistry $checkerRegistry
	 */
	public function __construct( $db, $context, $checkerRegistry ) {
		$this->context = $context;
		$this->db = $db;
		$this->checkerRegistry = $checkerRegistry;
	}

	/**
	 * @param User $user
	 * @param Title $title
	 * @param int $section
	 * @return Status
	 */
	public function saveUserEditing( User $user, Title $title, $section = -1 ) {
		if ( !\MediaWiki\MediaWikiServices::getInstance()
			->getPermissionManager()
			->userCan( 'edit', $user, $title )
		) {
			return Status::newFatal( "User cannot edit the page" );
		}

		$table = 'bs_saferedit';
		$fields = [
			"se_timestamp" => wfTimestamp( TS_MW, time() )
		];
		$conditions = [
			"se_user_name" => $user->getName(),
			"se_page_title" => $title->getDBkey(),
			"se_page_namespace" => $title->getNamespace(),
			"se_edit_section" => $section,
		];
		// needed for update reason
		$options = [
			'ORDER BY' => 'se_id DESC',
			'LIMIT' => 1,
		];

		$row = $this->db->selectRow(
			$table,
			[ 'se_id' ],
			$conditions,
			__METHOD__,
			$options
		);
		if ( $row ) {
			$title->invalidateCache();
			$updateOk = $this->db->update(
				$table,
				$fields,
				[ "se_id" => $row->se_id ]
			);
			if ( $updateOk ) {
				return Status::newGood();
			}
		} else {
			$title->invalidateCache();
			$insertOk = $this->db->insert( $table, $conditions + $fields );
			if ( $insertOk ) {
				return Status::newGood();
			}
		}

		return Status::newFatal( "Failed to save editing information" );
	}

	/**
	 * Actually delete all stored saves for a user
	 *
	 * @param User $user User that edited a page
	 * @param Title $title
	 * @return Status
	 */
	public function doClearSaferEdit( User $user, Title $title ) {
		$deleteOk = $this->db->delete(
			'bs_saferedit',
			[
				"se_user_name" => $user->getName(),
				"se_page_title" => $title->getDBkey(),
				"se_page_namespace" => $title->getNamespace(),
			]
		);

		if ( $deleteOk ) {
			$title->invalidateCache();
			return Status::newGood();
		}

		return Status::newFatal( "Deletion failed" );
	}

	/**
	 * @param string $func Function to be executed
	 * @param mixed &$result
	 */
	public function askEnvironmentalCheckers( $func, &$result ) {
		foreach ( $this->checkerRegistry->getAllKeys() as $key ) {
			$callable = $this->checkerRegistry->getValue( $key );
			if ( !is_callable( $callable ) ) {
				continue;
			}
			$provider = call_user_func( $callable, $this->context );
			if ( !$provider instanceof IEnvironmentChecker ) {
				continue;
			}
			if ( !$provider->$func( $result ) ) {
				return;
			}
		}
	}
}
