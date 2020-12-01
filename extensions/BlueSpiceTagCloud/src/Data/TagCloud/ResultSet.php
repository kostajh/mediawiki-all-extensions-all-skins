<?php

namespace BlueSpice\TagCloud\Data\TagCloud;

class ResultSet extends \BlueSpice\Data\ResultSet {

	/**
	 *
	 * @param \BlueSpice\Data\ResultSet[] $result
	 */
	public function __construct( \BlueSpice\Data\ResultSet $result ) {
		parent::__construct( $result->getRecords(), $result->getTotal() );
	}

	/**
	 *
	 * @return int
	 */
	public function getHighestCount() {
		$numRecords = count( $this->records );
		if ( $numRecords < 1 ) {
			return 0;
		}
		usort( $this->records, function ( $a, $b ) {
			if ( $a->get( Record::COUNT, 0 ) === $b->get( Record::COUNT, 0 ) ) {
				return 0;
			}
			return $a->get( Record::COUNT, 0 ) < $b->get( Record::COUNT, 0 )
				? -1
				: 1;
		} );
		return $this->records[ $numRecords - 1 ]->get( Record::COUNT, 0 );
	}

	/**
	 *
	 * @return int
	 */
	public function getLowestCount() {
		if ( count( $this->records ) < 1 ) {
			return 0;
		}
		usort( $this->records, function ( $a, $b ) {
			if ( $a->get( Record::COUNT, 0 ) === $b->get( Record::COUNT, 0 ) ) {
				return 0;
			}
			return $a->get( Record::COUNT, 0 ) < $b->get( Record::COUNT, 0 )
				? -1
				: 1;
		} );
		return $this->records[0]->get( Record::COUNT, 0 );
	}
}
