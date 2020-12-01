<?php
/**
 * Created by PhpStorm.
 * User: rvogel
 * Date: 09.12.2016
 * Time: 21:09
 */

namespace BS\PageVersion;

class RevisionCounter {

	const TYPE_MAJOR = 0;
	const TYPE_MINOR = 1;

	/**
	 * @var \Title
	 */
	protected $title = null;

	/**
	 * @var int[]
	 */
	protected $revisionIdTree = [];

	/**
	 * Array in form of [ 123 => TYPE_MINOR, 76 => TYPE_MAJOR, 75 => TYPE_MINOR,  ]
	 * @var int[]
	 */
	protected $revisionList = [];

	/**
	 * @var int
	 */
	protected $totalRevisionCount = 0;

	/**
	 * RevisionCounter constructor.
	 * @param \Title $title
	 */
	protected function __construct( $title ) {
		$this->title = $title;
		$this->load();
	}

	/**
	 * @var RevisionCounter[]
	 */
	protected static $instances = [];

	/**
	 * @param \Title $title
	 * @return self
	 */
	public static function instance( $title ) {
		if ( !isset( self::$instances[$title->getArticleID()] ) ) {
			self::$instances[$title->getArticleID()] = new self( $title );
		}

		return self::$instances[$title->getArticleID()];
	}

	/**
	 * @return int
	 */
	public function getRevisionCount() {
		return $this->totalRevisionCount;
	}

	/**
	 * @return int
	 */
	public function getMajorRevisionCount() {
		return count( array_keys( $this->revisionIdTree ) );
	}

	/**
	 *
	 * @param int $revId
	 * @return int
	 */
	public function getMajorRevisionCountFrom( $revId ) {
		$revIdFound = false;
		$count = 0;
		foreach ( $this->revisionList as $curRevId => $type ) {
			if ( $curRevId === $revId ) {
				$revIdFound = true;
			}

			if ( $revIdFound === true && $type === self::TYPE_MAJOR ) {
				$count++;
			}
		}

		return $count;
	}

	/**
	 *
	 * @param int $revId
	 * @return int
	 */
	public function getMinorRevisionCountFrom( $revId ) {
		$revIdFound = false;
		$count = 0;
		foreach ( $this->revisionList as $curRevId => $type ) {
			if ( $curRevId === $revId ) {
				$revIdFound = true;
			}

			if ( $revIdFound && $type === self::TYPE_MINOR ) {
				$count++;
			}

			if ( $revIdFound && $type === self::TYPE_MAJOR ) {
				break;
			}
		}

		return $count;
	}

	/**
	 * @param int $majorRevisionId
	 * @throws \UnexpectedValueException
	 * @return int
	 */
	public function getMinorRevisionCountSince( $majorRevisionId ) {
		if ( !isset( $this->revisionIdTree[$majorRevisionId] ) ) {
			// This is a workaround for when a new major page revision is being saved
			// In that case we need to reload the data
			$this->load();
			if ( !isset( $this->revisionIdTree[$majorRevisionId] ) ) {
				throw new \UnexpectedValueException( 'Not a major revision id: ' . $majorRevisionId );
			}
		}

		return count( $this->revisionIdTree[$majorRevisionId] );
	}

	protected $lastMajorRevId = 0;

	protected function load() {
		$this->revisionIdTree = [];

		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			'revision',
			[ 'rev_id', 'rev_minor_edit' ],
			[ 'rev_page' => $this->title->getArticleID() ]
		);
		$this->totalRevisionCount = $res->numRows();

		foreach ( $res as $row ) {
			$revId = (int)$row->rev_id;
			$revMinorEdit = (int)$row->rev_minor_edit;
			if ( $revMinorEdit === 0 ) {
				$this->revisionIdTree[$revId] = [];
				$this->lastMajorRevId = $revId;
			} else {
				$this->revisionIdTree[$this->lastMajorRevId][] = $revId;
			}

			$this->revisionList[$revId] = $revMinorEdit === 0
				? self::TYPE_MAJOR
				: self::TYPE_MINOR;
		}

		krsort( $this->revisionList );
	}
}
