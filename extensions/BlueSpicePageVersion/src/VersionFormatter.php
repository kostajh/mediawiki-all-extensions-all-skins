<?php

namespace BS\PageVersion;

class VersionFormatter {

	/**
	 *
	 * @var \Title
	 */
	protected $title = null;

	/**
	 * @var RevisionCounter
	 */
	protected $revisionCounter = null;

	/**
	 * VersionFormatter constructor.
	 * @param \Title $title
	 * @param RevisionCounter|null $revisionCounter
	 */
	function __construct( $title, $revisionCounter = null ) {
		$this->title = $title;

		if ( $revisionCounter instanceof RevisionCounter ) {
			$this->revisionCounter = $revisionCounter;
		} else {
			$this->revisionCounter = RevisionCounter::instance( $this->title );
		}
	}

	/**
	 * @param string $format
	 * @return string
	 */
	public function formatLatest( $format = '%s.%s' ) {
		return $this->format(
			$this->title->getLatestRevID(),
			$format
		);
	}

	/**
	 * @param int $revId
	 * @param string $format
	 * @return string
	 */
	public function format( $revId, $format = '%s.%s' ) {
		$minorRevisionSince = $this->revisionCounter->getMinorRevisionCountFrom( $revId );
		$majorRevisionsFrom = $this->revisionCounter->getMajorRevisionCountFrom( $revId );

		return sprintf( $format, $majorRevisionsFrom, $minorRevisionSince );
	}
}
