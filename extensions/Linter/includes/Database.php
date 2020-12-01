<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 */

namespace MediaWiki\Linter;

use FormatJson;
use MediaWiki\Logger\LoggerFactory;

/**
 * Database logic
 */
class Database {

	/**
	 * Maximum number of errors to save per category,
	 * for a page, the rest are just dropped
	 */
	public const MAX_PER_CAT = 20;

	/**
	 * @var int
	 */
	private $pageId;

	/**
	 * @var CategoryManager
	 */
	private $categoryManager;

	/**
	 * @param int $pageId
	 */
	public function __construct( $pageId ) {
		$this->pageId = $pageId;
		$this->categoryManager = new CategoryManager();
	}

	/**
	 * Get a specific LintError by id
	 *
	 * @param int $id linter_id
	 * @return bool|LintError
	 */
	public function getFromId( $id ) {
		$row = wfGetDB( DB_REPLICA )->selectRow(
			'linter',
			[ 'linter_cat', 'linter_params', 'linter_start', 'linter_end' ],
			[ 'linter_id' => $id, 'linter_page' => $this->pageId ],
			__METHOD__
		);

		if ( $row ) {
			$row->linter_id = $id;
			return $this->makeLintError( $row );
		} else {
			return false;
		}
	}

	/**
	 * Turn a database row into a LintError object
	 *
	 * @param \stdClass $row
	 * @return LintError|bool false on error
	 */
	public static function makeLintError( $row ) {
		try {
			$name = ( new CategoryManager() )->getCategoryName( $row->linter_cat );
		} catch ( MissingCategoryException $e ) {
			LoggerFactory::getInstance( 'Linter' )->error(
				'Could not find name for id: {linter_cat}',
				[ 'linter_cat' => $row->linter_cat ]
			);
			return false;
		}
		return new LintError(
			$name,
			[ (int)$row->linter_start, (int)$row->linter_end ],
			$row->linter_params,
			$row->linter_cat,
			(int)$row->linter_id
		);
	}

	/**
	 * Get all the lint errors for a page
	 *
	 * @return LintError[]
	 */
	public function getForPage() {
		$rows = wfGetDB( DB_REPLICA )->select(
			'linter',
			[
				'linter_id', 'linter_cat', 'linter_start',
				'linter_end', 'linter_params'
			],
			[ 'linter_page' => $this->pageId ],
			__METHOD__
		);
		$result = [];
		foreach ( $rows as $row ) {
			$error = $this->makeLintError( $row );
			if ( !$error ) {
				continue;
			}
			$result[$error->id()] = $error;
		}

		return $result;
	}

	/**
	 * Convert a LintError object into an array for
	 * inserting/querying in the database
	 *
	 * @param LintError $error
	 * @return array
	 */
	private function serializeError( LintError $error ) {
		return [
			'linter_page' => $this->pageId,
			'linter_cat' => $this->categoryManager->getCategoryId( $error->category, $error->catId ),
			'linter_params' => FormatJson::encode( $error->params, false, FormatJson::ALL_OK ),
			'linter_start' => $error->location[0],
			'linter_end' => $error->location[1],
		];
	}

	/**
	 * @param LintError[] $errors
	 * @return array
	 */
	private function countByCat( array $errors ) {
		$count = [];
		foreach ( $errors as $error ) {
			if ( !isset( $count[$error->category] ) ) {
				$count[$error->category] = 1;
			} else {
				$count[$error->category] += 1;
			}
		}

		return $count;
	}

	/**
	 * Save the specified lint errors in the
	 * database
	 *
	 * @param LintError[] $errors
	 * @return array [ 'deleted' => [ cat => count ], 'added' => [ cat => count ] ]
	 */
	public function setForPage( $errors ) {
		$previous = $this->getForPage();
		$dbw = wfGetDB( DB_MASTER );
		if ( !$previous && !$errors ) {
			return [ 'deleted' => [], 'added' => [] ];
		} elseif ( !$previous && $errors ) {
			$toInsert = array_values( $errors );
			$toDelete = [];
		} elseif ( $previous && !$errors ) {
			$dbw->delete(
				'linter',
				[ 'linter_page' => $this->pageId ],
				__METHOD__
			);
			return [ 'deleted' => $this->countByCat( $previous ), 'added' => [] ];
		} else {
			$toInsert = [];
			$toDelete = $previous;
			// Diff previous and errors
			foreach ( $errors as $error ) {
				$uniqueId = $error->id();
				if ( isset( $previous[$uniqueId] ) ) {
					unset( $toDelete[$uniqueId] );
				} else {
					$toInsert[] = $error;
				}
			}
		}

		if ( $toDelete ) {
			$ids = [];
			foreach ( $toDelete as $lintError ) {
				if ( $lintError->lintId ) {
					$ids[] = $lintError->lintId;
				}
			}
			$dbw->delete(
				'linter',
				[ 'linter_id' => $ids ],
				__METHOD__
			);
		}

		if ( $toInsert ) {
			// Insert into db, ignoring any duplicate key errors
			// since they're the same lint error
			$dbw->insert(
				'linter',
				array_map( [ $this, 'serializeError' ], $toInsert ),
				__METHOD__,
				[ 'IGNORE' ]
			);
		}

		return [
			'deleted' => $this->countByCat( $toDelete ),
			'added' => $this->countByCat( $toInsert ),
		];
	}

	/**
	 * @return int[]
	 */
	public function getTotalsForPage() {
		return $this->getTotalsAccurate( [ 'linter_page' => $this->pageId ] );
	}

	/**
	 * Get an estimate of how many rows are there for the
	 * specified category with EXPLAIN SELECT COUNT(*).
	 * If the category actually has no rows, then 0 will
	 * be returned.
	 *
	 * @param int $catId
	 *
	 * @return int
	 */
	private function getTotalsEstimate( $catId ) {
		$dbr = wfGetDB( DB_REPLICA );
		// First see if there are no rows, since the distinction
		// between 0 and 1 is important. And estimateRowCount seems
		// to never return 0.
		$rows = $dbr->selectRowCount(
			'linter',
			'*',
			[ 'linter_cat' => $catId ],
			__METHOD__,
			[ 'LIMIT' => 1 ]
		);
		if ( $rows === 0 ) {
			return 0;
		}

		// Now we can just estimate
		return $dbr->estimateRowCount(
			'linter',
			'*',
			[ 'linter_cat' => $catId ],
			__METHOD__
		);
	}

	/**
	 * This uses COUNT(*), which is accurate, but can be significantly
	 * slower depending upon how many rows are in the database.
	 *
	 * @param array $conds
	 *
	 * @return int[]
	 */
	private function getTotalsAccurate( $conds = [] ) {
		$rows = wfGetDB( DB_REPLICA )->select(
			'linter',
			[ 'linter_cat', 'COUNT(*) AS count' ],
			$conds,
			__METHOD__,
			[ 'GROUP BY' => 'linter_cat' ]
		);

		// Initialize zero values
		$ret = array_fill_keys( $this->categoryManager->getVisibleCategories(), 0 );
		foreach ( $rows as $row ) {
			try {
				$catName = $this->categoryManager->getCategoryName( $row->linter_cat );
			} catch ( MissingCategoryException $e ) {
				continue;
			}
			$ret[$catName] = (int)$row->count;
		}

		return $ret;
	}

	/**
	 * @return int[]
	 */
	public function getTotals() {
		$ret = [];
		foreach ( $this->categoryManager->getVisibleCategories() as $cat ) {
			$id = $this->categoryManager->getCategoryId( $cat );
			$ret[$cat] = $this->getTotalsEstimate( $id );
		}

		return $ret;
	}

}
