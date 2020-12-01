<?php

// FIXME: This class structure made more sense in my head
// then it does when I wrote it all out. I already think it should be refactored.
class SofaDB {

	/** @var IDatabase */
	private $dbw;

	/**
	 * @param IDatabase|null $dbw
	 */
	public function __construct( $dbw = null ) {
		$this->dbw = $dbw ?: wfGetDB( DB_MASTER );
	}

	/**
	 * Set sofa_map entries for a page and remove previous entries
	 *
	 * @param int $pageId
	 * @param array $maps Array containing assoc array of 'key' and 'value'
	 */
	public function setForPage( $pageId, array $maps ) {
		if ( $pageId <= 0 ) {
			throw new LogicException( "page id is out of range" );
		}
		$dbw = $this->dbw;
		$cur = $dbw->select(
			[ 'sofa_map', 'sofa_schema' ],
			[ 'sm_id', 'sm_key', 'sm_value', 'sms_name' ],
			[ 'sm_page' => $pageId, 'sms_id=sm_schema' ],
			__METHOD__
		);
		// This could be made more efficient
		// Keep in mind, having duplicate entries with same key/value is allowed.
		$idsToDelete = [];
		foreach ( $cur as $row ) {
			foreach ( $maps as &$newmap ) {
				if (
					$newmap['key'] === $row->sm_key
					&& $newmap['value'] === $row->sm_value
					&& $newmap['schema'] === $row->sms_name
				) {
					unset( $newmap );
					continue 2;
				}
			}
			$idsToDelete = $row->sm_id;
		}
		$dbw->startAtomic( __METHOD__ );
		if ( $idsToDelete ) {
			$dbw->delete( 'sofa_map', [ 'sm_id' => $idsToDelete ], __METHOD__ );
		}
		$inserts = [];
		$sofaSchema = SofaSchema::singleton();
		foreach ( $maps as $newmap ) {
			$schemaId = $sofaSchema->getOrCreateSchemaId( $dbw, $newmap['schema'] );
			$inserts[] = [
				'sm_page' => $pageId,
				'sm_key' => $newmap['key'],
				'sm_value' => $newmap['value'],
				'sm_schema' => $schemaId,
			];
		}
		$dbw->insert( 'sofa_map', $inserts, __METHOD__ );
		$dbw->endAtomic( __METHOD__ );
	}

	public function getUpdates( Title $title, array $smaps ) {
		// Fixme figure out transaction stuff better here.
		// Should this be in its own transaction?
		// Should this be in an AutoCommitUpdate?
		// Does the structure of this class even make sense?
		// Also, later todo, make this possibly be a different db.
		$that = $this;
		return [
			new MWCallableUpdate(
				function () use ( $title, $smaps, $that ) {
					$pageId = $title->getArticleId( Title::READ_LATEST );
					if ( $pageId === 0 ) {
						// I guess page got deleted?
						return;
					}
					$that->setForPage( $pageId, $smaps );
				},
				__METHOD__,
				$this->dbw
			)
		];
	}

	/**
	 * Get an array of updates to cleanup entries from a soon to be deleted page
	 *
	 * @param int $pageId
	 * @return DeferrableUpdate[]
	 */
	public function getDeletionUpdates( $pageId ) {
		$sofaDb = $this;
		return [
			new MWCallableUpdate(
				function () use ( $pageId, $sofaDb ) {
					$sofaDb->delete( $pageId );
				},
				__METHOD__,
				$this->dbw
			)
		];
	}

	/**
	 * Delete all entries related to a page
	 *
	 * @param int $pageId Id of page to delete stuff for
	 */
	public function delete( $pageId ) {
		$this->dbw->delete(
			'sofa_map',
			[ 'sm_page' => $pageId ],
			__METHOD__
		);
	}

	/**
	 * Normalize and validate key names
	 *
	 * @param string $key
	 * @return null|string Null if invalid, otherwise the normalized key name
	 */
	public static function normalizeKey( $key ) {
		$key = trim( $key );
		if ( $key === '' ) {
			// In principle there's no reason not to allow the
			// empty string, but that seems like it'd be confusing.
			return null;
		}
		if ( strlen( $key ) > 767 ) {
			return null;
		}
		return $key;
	}

	/**
	 * Validate a value
	 *
	 * @note This does not check that the JSON is valid. It should be.
	 * @param string|null $value JSON or null
	 * @return bool
	 */
	public static function validateValue( $value ) {
		if ( !is_string( $value ) && $value !== null ) {
			return false;
		}
		if ( $value !== null && strlen( $value ) > 65535 ) {
			return false;
		}
		return true;
	}

}
